<?php

namespace Enginewerk\EmissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Enginewerk\EmissionBundle\Response\AppResponse;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileType;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileBlockType;

/**
 * DefaultController
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        /** @var $repository \Enginewerk\EmissionBundle\Entity\FileRepository **/
        $repository = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File');
        $query = $repository->createQueryBuilder('f')
                ->orderBy('f.id', 'DESC')
                ->getQuery();

        $files = $query->getResult();

        $fileBlockForm = $this->createForm(new ResumableFileBlockType());
        $fileForm = $this->createForm(new ResumableFileType());

        return array('Files' => $files, 'FileBlockForm' => $fileBlockForm->createView(), 'FileForm' => $fileForm->createView());
    }

    /**
     * @Route("/f/{file}", requirements={"file"}, name="show_file")
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @throws type
     */
    public function showFileAction(Request $request)
    {
        $file = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if (!$file) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('file')));
        }

        return array('File' => $file);
    }

    /**
     * @Route("/d/{file}", requirements={"file"}, name="download_file", defaults={"dl" = 1})
     * @Route("/o/{file}", requirements={"file"}, name="open_file")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function downloadFileAction(Request $request)
    {
        /** @var $file \Enginewerk\EmissionBundle\Entity\File **/
        $file = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if (null === $file) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('file')));
        }

        $fileBlocks = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlock')
                ->findBy(array('fileId' => $file->getId()), array('rangeStart' => 'ASC'));

        $blocks = array();
        $storage = $this->get('enginewerk_bbs');
        foreach ($fileBlocks as $fileBlock) {
            
            $block = $storage->get($fileBlock->getFileHash());
            $filePath = $block->getPathname();

            if (!file_exists($filePath) || is_dir($filePath)) {
                $this->get('logger')->error(sprintf('BinaryBlock #%s at path "%s" doesn`t exist.', $block->getId(), $block->getPathname()));
                throw $this->createNotFoundException('File doesn`t exists.');
            }

            $blocks[] = $filePath;
        }

        // TODO Download set_time_limit
        set_time_limit(0);

        $response = new StreamedResponse();

        $response->headers->set('Content-Type', $file->getType());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if ($request->get('dl')) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getName().'"');
        }

        $response->setCallback(function () use ($blocks) {
            foreach ($blocks as $blockFilePath) {
                readfile($blockFilePath);
            }
        });

        return $response;
    }

    /**
     * @Route("/delete/{file}", requirements={"file"}, name="delete_file")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function deleteAction(Request $request)
    {
        $appResponse = new AppResponse();

        /** @var $file \Enginewerk\EmissionBundle\Entity\File **/
        $file = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));
        $fileBlockRepository = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:FileBlock');
        
        $binaryBlocksToRemove = array();
        foreach ($file->getFileBlocks() as $fileBlock) {
            $usedBlocks = $fileBlockRepository->getUsedBlocksNumber($fileBlock->getFileHash());
            if(null === $usedBlocks || 1 == $usedBlocks) {
                $binaryBlocksToRemove[] = $fileBlock->getFileHash();
            }
        }

        if (!$file) {
            $appResponse->error(sprintf('File #%s not found.', $request->get('file')));
            return new JsonResponse($appResponse->response(), 200);
        }
        
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($file);
            $em->flush();
            foreach ($binaryBlocksToRemove as $blockKey) {
                $this->get('enginewerk_bbs')->delete($blockKey);
            }

            $appResponse->success();

        } catch (Exception $ex) {
            $appResponse->error('Can`t remove File');
            $this->get('logger')->error(sprintf('Can`t remove File #%s. %s', $file->getId(), $ex->getMessage()));
        }

        return new JsonResponse($appResponse->response(), 200);
    }

    /**
     * @Route("/{file}/expiration/{date}", requirements={"file"}, defaults={"date" = "never"}, name="file_expiration_date")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function fileExpirationDateAction(Request $request)
    {
        $appResponse = new AppResponse();

        /** @var $file \Enginewerk\EmissionBundle\Entity\File **/
        $file = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if (!$file) {
            $appResponse->error(sprintf('File #%s not found.', $request->get('file')));
        } else {
            if ('never' == $request->get('date')) {
                $expirationDate = null;
            } else {
                $expirationDate = new \DateTime($request->get('date'));
            }

            $em = $this->getDoctrine()->getManager();
            $file->setExpirationDate($expirationDate);

            try {
                $em->flush();
                $appResponse->success();

            } catch (Exception $ex) {
                $appResponse->error('Can`t change expiration date');
                $this->get('logger')->error(sprintf('Can`t change expiration date of File #%s. %s', $file->getId(), $ex->getMessage()));
            }
        }

        return new JsonResponse($appResponse->response(), 200);
    }

    /**
     * @Route("/replace/{replace}/with/{replacement}", name="replace_file")
     */
    public function replaceFileAction($replace, $replacement)
    {
        $appResponse = new AppResponse();

        /** @var $repository \Enginewerk\EmissionBundle\Entity\FileRepository **/
        $repository = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File');

        /** @var $replaceFile \Enginewerk\EmissionBundle\Entity\File **/
        $replaceFile = $repository->findOneByFileId($replace);
        if (!$replaceFile) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $replace));
        }

        /** @var $replacementFile \Enginewerk\EmissionBundle\Entity\File **/
        $replacementFile = $repository->findOneByFileId($replacement);
        if (!$replacementFile) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $replace));
        }

        if ($replaceFile->getUploadedBy() == $replacementFile->getUploadedBy()) {

            $em = $this->getDoctrine()->getManager();
            $replacementFile->setFileId($replaceFile->getFileId());
            // Nie działa z usługą BBS
            $em->remove($replaceFile);

            try {
                $em->flush();
                $appResponse->success('File replaced.');
            } catch (Exception $ex) {
                $this->get('logger')->error(sprintf('Can`t replace file. [%s] %s', get_class($ex), $ex->getMessage()));
                $appResponse->error(sprintf('Can`t replace file.'));
            }

        } else {
            $appResponse->error(sprintf('Only owner can replace file.'));
        }

        return new JsonResponse($appResponse->response());
    }
}
