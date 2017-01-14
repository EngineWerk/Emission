<?php
namespace Enginewerk\EmissionBundle\Storage;

use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\FileResponse\ChunkedFile;
use Enginewerk\FSBundle\Storage\StorageService;
use RuntimeException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FileStorage
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var  StorageService */
    protected $binaryObjectStorage;

    /**
     * @param RegistryInterface $doctrine
     * @param StorageService $binaryObjectStorage
     */
    public function __construct(RegistryInterface $doctrine, StorageService $binaryObjectStorage)
    {
        $this->doctrine = $doctrine;
        $this->binaryObjectStorage = $binaryObjectStorage;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $file = $this->get($key);

        $fileBlockRepository = $this
                ->doctrine
                ->getRepository('EnginewerkEmissionBundle:FileBlock');

        $binaryBlocksToRemove = [];

        foreach ($file->getFileBlocks() as $fileBlock) {
            $usedBlocks = $fileBlockRepository->getUsedBlocksNumber($fileBlock->getFileHash());
            if (null === $usedBlocks || 1 == $usedBlocks) {
                $binaryBlocksToRemove[] = $fileBlock->getFileHash();
            }
        }

        $em = $this
                ->doctrine
                ->getManager();

        $em->getConnection()->beginTransaction();
        $em->remove($file);

        try {
            foreach ($binaryBlocksToRemove as $blockKey) {
                $this->binaryObjectStorage->delete($blockKey);
            }

            $em->flush();
            $em->getConnection()->commit();
        } catch (RuntimeException $e) {
            $em->getConnection()->rollback();
            $em->close();
            throw $e;
        }
    }

    /**
     * @param string $key
     *
     * @throws FileNotFoundException
     *
     * @return File|null
     *
     */
    protected function get($key)
    {
        $file = $this->find($key);

        if ($file) {
            return $file;
        } else {
            throw new FileNotFoundException(sprintf('File with key "%s" not found.', $key));
        }
    }

    /**
     * @param string $key
     *
     * @return ChunkedFile
     */
    public function getFileForDownload($key)
    {
        $file = $this->get($key);

        $fileBlocks = $this
                ->doctrine
                ->getRepository('EnginewerkEmissionBundle:FileBlock')
                ->findBy(['fileId' => $file->getId()], ['rangeStart' => 'ASC']);

        $blocks = [];
        foreach ($fileBlocks as $fileBlock) {
            $block = $this
                    ->binaryObjectStorage
                    ->get($fileBlock->getFileHash());
            $blocks[] = $block;
        }

        $responseFile = new ChunkedFile();
        $responseFile->setChunks($blocks);

        return $responseFile;
    }

    /**
     * @param string $key
     *
     * @return \Enginewerk\EmissionBundle\Entity\File|null|object
     */
    public function find($key)
    {
        return $this
                ->doctrine
                ->getRepository('EnginewerkEmissionBundle:File')
                ->findOneBy(['fileId' => $key]);
    }

    /**
     * @return File[]
     */
    public function findAll()
    {
        return $this
                ->doctrine
                ->getRepository('EnginewerkEmissionBundle:File')
                ->getFiles();
    }

    public function findCreatedAfter($dateTime)
    {
    }

    public function replace($replace, $replacement)
    {
        $replaceFile = $this->get($replace);
        /* @var $replaceFile \Enginewerk\EmissionBundle\Entity\File */

        $replacementFile = $this->get($replacement);
        /* @var $replacementFile \Enginewerk\EmissionBundle\Entity\File */

        if ($replaceFile->getUploadedBy() == $replacementFile->getUploadedBy()) {
            $em = $this
                    ->doctrine
                    ->getManager();

            $replacementFile->setFileId($replaceFile->getFileId());

            $em->remove($replaceFile);
            $this->binaryObjectStorage->delete($replace);
            $em->flush();
        } else {
            throw new \Exception(sprintf('Only owner can replace file.'));
        }
    }

    public function alterExpirationDate($key, $expirationDate)
    {
        $file = $this->get($key);
        $file->setExpirationDate($expirationDate);

        $this
            ->doctrine
            ->getManager()
            ->flush();
    }
}
