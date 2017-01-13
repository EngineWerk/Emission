<?php
namespace Enginewerk\EmissionBundle\Controller;

use Enginewerk\EmissionBundle\Form\Type\ResumableFileBlockType;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileType;
use Enginewerk\EmissionBundle\Response\AppResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * ResumableController.
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
                ->findOneBy([
                    'name' => $request->get('resumableFilename'),
                    'checksum' => $request->get('resumableIdentifier'),
                    'size' => $request->get('resumableTotalSize'), ]);

        if (!$file) {
            $appResponse->error('File "' . $request->get('resumableFilename') . '" not found');

            return new JsonResponse($appResponse->toArray(), 306);
        }

        // Find out if we have this FileBlock already
        $fileBlock = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlock')
                ->findOneBy([
                    'fileId' => $file->getId(),
                    'rangeStart' => $request->get('resumableCurrentStartByte'),
                    'rangeEnd' => $request->get('resumableCurrentEndByte'), ]);

        if (!$fileBlock) {
            $appResponse->success('Block not found');

            return new JsonResponse($appResponse->toArray(), 306);
        } else {
            $appResponse->success('Block found');

            return new JsonResponse($appResponse->toArray(), 200);
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

        // File
        $em = $this->getDoctrine()->getManager();
        // Find out if we have this File already
        $file = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:File')
                ->findOneBy([
                    'name' => $formRequest['resumableFilename'],
                    'checksum' => $formRequest['resumableIdentifier'],
                    'size' => $formRequest['resumableTotalSize'],
                    ]
                );

        // No? Lets create one
        if (null === $file) {
            $fileForm = $this->createForm(new ResumableFileType());
            $fileForm->handleRequest($request);

            if ($fileForm->isValid()) {
                $file = $fileForm->getData();
                $file->setType($fileForm->get('uploadedFile')->getData()->getMimeType());
                $file->setUser($this->getUser());

                $em->persist($file);
            } else {
                $responseCode = 415;
                $appResponse->error(var_export($fileForm->getErrorsAsString(), true));

                return new JsonResponse($appResponse->toArray(), $responseCode);
            }
        }

        // Find out if we have this FileBlock
        $FileBlockInStorage = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlock')
                ->findOneBy([
                    'fileId' => $file->getId(),
                    'rangeStart' => $formRequest['resumableCurrentStartByte'],
                    'rangeEnd' => $formRequest['resumableCurrentEndByte'], ]);

        // No ? Lets create one
        if (null === $FileBlockInStorage) {
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
        // Lets make sum for all rangeEnd and check against declared File size
        $totalSize = $this->getDoctrine()
            ->getRepository('EnginewerkEmissionBundle:FileBlock')
            ->getTotalSize($file->getId());

        if ($totalSize == $file->getSize()) {

            // Set complete property to true
            $file->setComplete(true);
            $em->persist($file);
            $em->flush();

            // Return whole data for accessing of file
            $responseData = [
                      'id' => $file->getId(),
                      'file_id' => $file->getFileId(),
                      'name' => $file->getName(),
                      'type' => $file->getType(),
                      'size' => $file->getSize(),
                      'expiration_date' => $file->getExpirationDate()->format('Y-m-d H:i:s'),
                      'updated_at' => $file->getUpdatedAt()->format('Y-m-d H:i:s'),
                      'created_at' => $file->getCreatedAt()->format('Y-m-d H:i:s'),
                      'uploaded_by' => $file->getUser()->getUsername(),
                      'show_url' => $this->generateUrl('show_file', ['file' => $file->getFileId()], true),
                      'download_url' => $this->generateUrl('download_file', ['file' => $file->getFileId()], true),
                      'open_url' => $this->generateUrl('open_file', ['file' => $file->getFileId()], true),
                      'delete_url' => $this->generateUrl('delete_file', ['file' => $file->getFileId()], true),
                ];
        } else {
            $responseData = [
                      'id' => $file->getId(),
                      'file_id' => $file->getFileId(),
                      'name' => $file->getName(),
                      'type' => $file->getType(),
                      'size' => $file->getSize(),
                    ];
        }

        $responseCode = 200;
        $appResponse->success();
        $appResponse->data($responseData);

        return new JsonResponse($appResponse->toArray(), $responseCode);
    }
}
