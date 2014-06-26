<?php

namespace Enginewerk\EmissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Enginewerk\EmissionBundle\Response\AppResponse;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileType;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileBlockType;

/**
 * ResumableController
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class ResumableController extends Controller
{
    /**
     * @Route("/uploadChunkTest", name="upload_file_chunk_test")
     * @Method({"GET"})
     */
    public function uploadChunkTestAction(Request $request)
    {
        $appResponse = new AppResponse();

        // Find out if we have this File already
        $file = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:File')
                ->findOneBy(array(
                    'name' => $request->get('resumableFilename'),
                    'checksum' => $request->get('resumableIdentifier'),
                    'size' => $request->get('resumableTotalSize')));

        if (!$file) {
            $appResponse->error('File "' . $request->get('resumableFilename') . '" not found');

            return new JsonResponse($appResponse->response(), 306);
        }

        // Find out if we have this FileBlock already
        $fileBlock = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlock')
                ->findOneBy(array(
                    'fileId' => $file->getId(),
                    'rangeStart' => $request->get('resumableCurrentStartByte'),
                    'rangeEnd' => $request->get('resumableCurrentEndByte')));

        if (!$fileBlock) {

            $appResponse->success('Block not found');

            return new JsonResponse($appResponse->response(), 306);
        } else {

            $appResponse->success('Block found');

            return new JsonResponse($appResponse->response(), 200);
        }
    }

    /**
     * @Route("/upload", name="upload_file")
     * @Method({"POST"})
     */
    public function uploadAction(Request $request)
    {
        $appResponse = new AppResponse();

        $formRequest = $request->request->get('form');

        $fileFormRequest = array();
        $fileFormRequest['name'] = $formRequest['resumableFilename'];
        $fileFormRequest['size'] = $formRequest['resumableTotalSize'];
        $fileFormRequest['checksum'] = $formRequest['resumableIdentifier'];
        $fileFormRequest['_token'] = $formRequest['_tokenFile'];

        // File
        $em = $this->getDoctrine()->getManager();
        // Find out if we have this File already
        $file = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:File')
                ->findOneBy(array(
                    'name' => $fileFormRequest['name'],
                    'checksum' => $fileFormRequest['checksum'],
                    'size' => $fileFormRequest['size'],
                    )
                );

        // No? Lets create one
        if (null === $file) {
            $request->files->set('file', $request->files->get('form'));
            $request->request->set('file', $fileFormRequest);
            $fileForm = $this->createForm(new ResumableFileType());
            $fileForm->handleRequest($request);

            if ($fileForm->isValid()) {
                $file = $fileForm->getData();
                $file->setType($fileForm->get('uploadedFile')->getData()->getMimeType());
                $file->setComplete(false);
                $file->setUser($this->getUser());

                $em->persist($file);
           } else {

                $responseCode = 415;
                $appResponse->error(var_export($fileForm->getErrorsAsString(), true));

                return new JsonResponse($appResponse->response(), $responseCode);
            }
        }

        $fileBlockFormRequest = array();
        $fileBlockFormRequest['rangeStart'] = $formRequest['resumableCurrentStartByte'];
        $fileBlockFormRequest['size'] = $formRequest['resumableCurrentChunkSize'];
        $fileBlockFormRequest['rangeEnd'] = $formRequest['resumableCurrentEndByte'];
        $fileBlockFormRequest['_token'] = $formRequest['_tokenFileBlock'];

        // Find out if we have this FileBlock
        $FileBlockInStorage = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlock')
                ->findOneBy(array(
                    'fileId' => $file->getId(),
                    'rangeStart' => $fileBlockFormRequest['rangeStart'],
                    'rangeEnd' => $fileBlockFormRequest['rangeEnd']));

        // No ? Lets create one
        if (null === $FileBlockInStorage) {

            $request->files->set('fileBlock', $request->files->get('form'));
            $request->request->set('fileBlock', $fileBlockFormRequest);
            $fileBlockForm = $this->createForm(new ResumableFileBlockType());
            $fileBlockForm->handleRequest($request);

            $uploadedFile = $fileBlockForm->get('uploadedFile')->getData();
            /* @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile  */
            $key = sha1(microtime() . $uploadedFile->getPathname());

            $size = $this->get('enginewerk_bbs')->put($key, $uploadedFile);

            $fileBlock = $fileBlockForm->getData();
            $fileBlock->setFile($file);
            $fileBlock->setFileHash($key);
            $fileBlock->setSize($size);

            $em->persist($fileBlock);
            $em->flush();
        }

        // Do we have whole file?
        // Lets make sum for all renageEnd and check against declared File size
        $query = $this->getDoctrine()->getManager()
                ->createQuery('SELECT SUM(f.size) as totalSize FROM EnginewerkEmissionBundle:FileBlock f WHERE f.fileId = :fileId')
                ->setParameter('fileId', $file->getId());

        $totalSize = $query->getSingleScalarResult();

        if ($totalSize == $file->getSize()) {

            // Set complete property to true
            $file->setComplete(true);
            $em->persist($file);
            $em->flush();

            // Return whole data for accessing of file
            $responseData = array(
                      'id' => $file->getId(),
                      'file_id' => $file->getFileId(),
                      'name' => $file->getName(),
                      'type' => $file->getType(),
                      'size' => $file->getSize(),
                      'expiration_date' => $file->getExpirationDate()->format('Y-m-d H:i:s'),
                      'updated_at' => $file->getUpdatedAt()->format('Y-m-d H:i:s'),
                      'created_at' => $file->getCreatedAt()->format('Y-m-d H:i:s'),
                      'uploaded_by' => $file->getUser()->getUsername(),
                      'show_url' => $this->generateUrl('show_file', array('file' => $file->getFileId()), true),
                      'download_url' => $this->generateUrl('download_file', array('file' => $file->getFileId()), true),
                      'open_url' => $this->generateUrl('open_file', array('file' => $file->getFileId()), true),
                      'delete_url' => $this->generateUrl('delete_file', array('file' => $file->getFileId()), true)
                );
        } else {

            $responseData = array(
                      'id' => $file->getId(),
                      'file_id' => $file->getFileId(),
                      'name' => $file->getName(),
                      'type' => $file->getType(),
                      'size' => $file->getSize()
                    );
        }

        $responseCode = 200;
        $appResponse->success();
        $appResponse->data($responseData);

        return new JsonResponse($appResponse->response(), $responseCode);
    }
}
