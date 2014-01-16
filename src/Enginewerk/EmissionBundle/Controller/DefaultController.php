<?php

namespace Enginewerk\EmissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Entity\FileBlob;
use Enginewerk\EmissionBundle\Response\AppResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $Repository = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File');
        $Query = $Repository->createQueryBuilder('f')
                ->orderBy('f.id', 'DESC')
                ->getQuery();

        $Files = $Query->getResult();

        $FileBlob = new FileBlob();
        $Form = $this->createFormBuilder($FileBlob)
                ->add('fileBlob')
                ->add('save', 'submit')
                ->getForm();

        return array('Files' => $Files, 'Form' => $Form->createView());
    }

    /**
     * TODO Validating minimum chunk size
     * @Route("/uploadChunkTest", name="upload_file_chunk_test")
     */
    public function uploadChunkTestAction(Request $request)
    {
        $appResponse = new AppResponse();
        
        // Find out if we have this File already
        $File = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:File')
                ->findOneBy(array(
                    'name' => $request->get('resumableFilename'),
                    'checksum' => $request->get('resumableIdentifier'),
                    'size' => $request->get('resumableTotalSize')));

        if (!$File) {
            $appResponse->error('File "' . $request->get('resumableFilename') . '" , not found');
            return new JsonResponse($appResponse->response(), 306);
        } else {

            // Check if uploaded chunks are same size as currently delcared
            if ($File->getFileBlobs()->first()->getSize() != $request->get('resumableCurrentChunkSize')) {
                $appResponse->error('Chunk size differ from previously uploaded');
                return new JsonResponse($appResponse->response(), 415);
            }
        }

        // Find out if we have this FileBlob already
        $FileBlob = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlob')
                ->findOneBy(array(
                    'fileId' => $File->getId(),
                    'rangeStart' => $request->get('resumableCurrentStartByte'),
                    'rangeEnd' => $request->get('resumableCurrentEndByte')));

        if (!$FileBlob) {

            $appResponse->error('Blob not found');

            return new JsonResponse($appResponse->response(), 306);
        } else {

            $appResponse->info('Blob found');
            
            return new JsonResponse($appResponse->response(), 200);
        }
    }

    /**
     * TODO Validating minimum chunk size
     *
     * @Route("/upload", name="upload_file")
     */
    public function uploadAction(Request $request)
    {
        $appResponse = new AppResponse();
        $formRequest = $request->request->get('form');

        $formRequest['rangeStart'] = $request->request->get('resumableCurrentStartByte');
        $formRequest['rangeEnd'] = $request->request->get('resumableCurrentEndByte');

        $request->request->set('form', $formRequest);

        $FileBlob = new FileBlob();

        $Form = $this->createFormBuilder($FileBlob)
                ->add('fileBlob', 'file')
                ->add('rangeStart', 'text')
                ->add('rangeEnd', 'text')
                ->getForm();

        $Form->handleRequest($request);

        if ($Form->isValid()) {

            // File
            $em = $this->getDoctrine()->getManager();
            // Find out if we have this File already
            $File = $this->getDoctrine()
                    ->getRepository('EnginewerkEmissionBundle:File')
                    ->findOneBy(array(
                        'name' => $request->request->get('resumableFilename'),
                        'checksum' => $request->request->get('resumableIdentifier'),
                        'size' => $request->request->get('resumableTotalSize')));

            // No? Lets create one
            if (null === $File) {
                $File = new File();

                $File->setName($request->request->get('resumableFilename'));
                $File->setExtensionName($Form->get('fileBlob')->getData()->guessExtension());
                $File->setType($Form->get('fileBlob')->getData()->getMimeType());
                $File->setSize($request->request->get('resumableTotalSize'));
                $File->setChecksum($request->request->get('resumableIdentifier'));
                $File->setIsComplete(false);
                $File->setUploadedBy($this->getUser()->getUserName());

                $Validator = $this->get('validator');

                $errors = $Validator->validate($File);
                if (count($errors)) {
                    $appResponse->error((string) $errors);
                    
                    return new JsonResponse($appResponse->response(), 415);
                }

                $em->persist($File);
            }

            // Find out if we have this FileBlob
            $FileBlobInStorage = $this->getDoctrine()
                    ->getRepository('EnginewerkEmissionBundle:FileBlob')
                    ->findOneBy(array(
                        'fileId' => $File->getId(),
                        'rangeStart' => $formRequest['rangeStart'],
                        'rangeEnd' => $formRequest['rangeEnd']));

            // No ? Lets create one
            if (null === $FileBlobInStorage) {
                $FileBlob->setFile($File);
                $em->persist($FileBlob);
                $em->flush();
            }

            // Do we have whole file?
            // Lets make sum for all renageEnd and check against declared File size
            $query = $this->getDoctrine()->getManager()
                    ->createQuery('SELECT SUM(f.size) as totalSize FROM EnginewerkEmissionBundle:FileBlob f WHERE f.fileId = :fileId')
                    ->setParameter('fileId', $File->getId());

            $totalSize = $query->getSingleScalarResult();

            if ($totalSize == $File->getSize()) {

                // Set isComplete property to true
                $File->setIsComplete(true);
                $em->persist($File);
                $em->flush();

                // Return whole data for accessing of file
                $responseData = array(
                          'id' => $File->getId(),
                          'file_id' => $File->getFileId(),
                          'name' => $File->getName(),
                          'type' => $File->getType(),
                          'size' => $File->getSize(),
                          'expiration_date' => $File->getExpirationDate()->format('Y-m-d H:i:s'),
                          'updated_at' => $File->getUpdatedAt()->format('Y-m-d H:i:s'),
                          'created_at' => $File->getCreatedAt()->format('Y-m-d H:i:s'),
                          'uploaded_by' => $File->getUploadedBy(),
                          'show_url' => $this->generateUrl('show_file', array('file' => $File->getFileId()), true),
                          'download_url' => $this->generateUrl('download_file', array('file' => $File->getFileId()), true),
                          'open_url' => $this->generateUrl('open_file', array('file' => $File->getFileId()), true),
                          'delete_url' => $this->generateUrl('delete_file', array('file' => $File->getFileId()), true)
                    );
            } else {
                
                $responseData = array(
                          'id' => $File->getId(),
                          'file_id' => $File->getFileId(),
                          'name' => $File->getName(),
                          'type' => $File->getType(),
                          'size' => $File->getSize()
                        );
            }

            $responseCode = 200;
            $appResponse->success();
            $appResponse->data($responseData);

        } else {

            $responseCode = 415;
            $appResponse->error(var_export($Form->getErrorsAsString(), true));
        }
        
        return new JsonResponse($appResponse->response(), $responseCode);
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
        $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if (!$File) {
            throw $this->createNotFoundException('File not found');
        }

        return array('File' => $File);
    }

    /**
     * @Route("/d/{file}", requirements={"file"}, name="download_file", defaults={"dl" = 1})
     * @Route("/o/{file}", requirements={"file"}, name="open_file")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function downloadFileAction(Request $request)
    {
        $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if (null === $File) {
            throw $this->createNotFoundException('File not found');
        }

        $FileBlobs = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlob')
                ->findBy(array('fileId' => $File->getId()), array('rangeStart' => 'ASC'));

        foreach ($FileBlobs as $FileBlob) {
            $filePath = $FileBlob->getAbsolutePath();

            if (!file_exists($filePath) || is_dir($filePath)) {
                throw $this->createNotFoundException('File doesn`t exists');
            }
        }

        // TODO Download set_time_limit
        set_time_limit(0);
        $response = new Response();

        $response->headers->set('Content-Type', $File->getType());
        $response->headers->set('Content-Length', $File->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if ($request->get('dl')) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $File->getName().'"');
        }

        $response->sendHeaders();

        foreach ($FileBlobs as $FileBlob) {
            $filePath = $FileBlob->getAbsolutePath();
            readfile($filePath);
        }

        ob_end_flush();
    }

    /**
     * @Route("/delete/{file}", requirements={"file"}, name="delete_file")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function deleteAction(Request $request)
    {
        $appResponse = new AppResponse();

        $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if (!$File) {
            $appResponse->error('File not found');
        } else {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($File);
                $em->flush();

                $appResponse->success();

            } catch (Exception $e) {
                $appResponse->error('Can`t remove File');
            }
        }

        return new JsonResponse($appResponse->response(), 200);
    }
}