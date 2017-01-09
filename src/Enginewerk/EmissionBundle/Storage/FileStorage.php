<?php
namespace Enginewerk\EmissionBundle\Storage;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Enginewerk\EmissionBundle\FileResponse\ChunkedFile;
use RuntimeException;

/**
 * Description of FileStorage.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class FileStorage
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    protected $binaryObjectStorage;

    public function __construct(Registry $doctrine, $binaryObjectStorage)
    {
        $this->doctrine = $doctrine;
        $this->binaryObjectStorage = $binaryObjectStorage;
    }

    public function delete($key)
    {
        $file = $this->get($key);

        $fileBlockRepository = $this
                ->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlock');

        $binaryBlocksToRemove = [];

        foreach ($file->getFileBlocks() as $fileBlock) {
            $usedBlocks = $fileBlockRepository->getUsedBlocksNumber($fileBlock->getFileHash());
            if (null === $usedBlocks || 1 == $usedBlocks) {
                $binaryBlocksToRemove[] = $fileBlock->getFileHash();
            }
        }

        $em = $this
                ->getDoctrine()
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
     * @param $key
     *
     * @throws \Enginewerk\EmissionBundle\Storage\FileNotFoundException
     *
     * @return \Enginewerk\EmissionBundle\Entity\File
     */
    public function get($key)
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
                ->getDoctrine()
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

    public function find($key)
    {
        return $this
                ->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:File')
                ->findOneBy(['fileId' => $key]);
    }

    public function findAll()
    {
        return $this
                ->getDoctrine()
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
                    ->getDoctrine()
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
            ->getDoctrine()
            ->getManager()
            ->flush();
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }
}
