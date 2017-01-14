<?php
namespace Enginewerk\EmissionBundle\Controller;

use Enginewerk\EmissionBundle\Form\Type\ResumableFileBlockType;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileType;
use Enginewerk\EmissionBundle\Response\AppResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @Template()
     */
    public function indexAction()
    {
        $files = $this
                ->get('enginewerk_emission.service.file_read_service')
                ->findAllFiles();

        $fileBlockForm = $this->createForm(new ResumableFileBlockType());
        $fileForm = $this->createForm(new ResumableFileType());

        $capabilities = [
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'browser_file_memory_limit' => $this->container->getParameter('app.uploader_max_chunk_size'),
            ];

        $uploaderCapabilities = $capabilities;
        sort($uploaderCapabilities, SORT_NUMERIC);
        $maxUploadFileSize = (int) $uploaderCapabilities[0];

        return [
            'Files' => $files,
            'FileBlockForm' => $fileBlockForm->createView(),
            'FileForm' => $fileForm->createView(),
            'Capabilities' => $capabilities,
            'MaxUploadFileSize' => $maxUploadFileSize,
        ];
    }

    /**
     * @Route("/f/{file}", requirements={"file"}, name="show_file")
     * @Method({"GET"})
     * @Template()
     *
     * @param  Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return array
     */
    public function showFileAction(Request $request)
    {
        $efs = $this->get('enginewerk_emission.storage.file_storage');

        if (null === ($file = $efs->findByShortIdentifier($request->get('file')))) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('file')));
        }

        return ['File' => $file];
    }

    /**
     * @Route("/fc/{fileShortIdentifier}", requirements={"fileShortIdentifier"}, name="show_file_content")
     * @Method({"GET"})
     *
     * @param  Request                                                       $request
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function showFileContentAction(Request $request)
    {
        $efs = $this->get('enginewerk_emission.storage.file_storage');

        if (null === ($file = $efs->findByShortIdentifier($request->get('fileShortIdentifier')))) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('fileShortIdentifier')));
        }

        $appResponse = new AppResponse();
        $appResponse->success();
        $appResponse->data(
            $this->renderView(
                'EnginewerkEmissionBundle:Default:showFileContent.html.twig',
                ['File' => $file]
            )
        );

        return new JsonResponse($appResponse->toArray(), 200);
    }

    /**
     * @Route("/d/{fileShortIdentifier}", requirements={"fileShortIdentifier"}, name="download_file", defaults={"dl" = 1})
     * @Route("/o/{fileShortIdentifier}", requirements={"fileShortIdentifier"}, name="open_file")
     * @Method({"GET"})
     *
     * @param  Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return StreamedResponse
     */
    public function downloadFileAction(Request $request)
    {
        $fileStorage = $this->get('enginewerk_emission.storage.file_storage');
        $fileShortIdentifier = $request->get('fileShortIdentifier');

        if (null === ($file = $fileStorage->findByShortIdentifier($fileShortIdentifier))) {
            throw $this->createNotFoundException(
                sprintf(
                    'File identified by "%s" not found.',
                    $request->get('fileShortIdentifier')
                )
            );
        }

        $responseFile = $fileStorage->getFileForDownload($fileShortIdentifier);

        set_time_limit(0);

        $response = new StreamedResponse();

        $response->headers->set('Content-Type', $file->getType());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if ($request->get('dl')) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getName() . '"');
        }

        $response->setCallback(function () use ($responseFile) {
            $responseFile->read();
        });

        return $response;
    }

    /**
     * @Route("/delete/{file}", requirements={"file"}, name="delete_file")
     * @Method({"DELETE"})
     *
     * @param  Request      $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $appResponse = new AppResponse();

        $fileStorage = $this->get('enginewerk_emission.storage.file_storage');

        try {
            $fileStorage->delete($request->get('file'));
            $appResponse->success();
        } catch (\Exception $ex) {
            $appResponse->error(sprintf(
                'Can`t delete File identified by shortFileIdentifier "%s"',
                $request->get('file')
            ));
            $this->get('logger')->error(
                sprintf(
                    'Can`t delete File identified by shortFileIdentifier "%s". %s',
                    $request->get('file'),
                    $ex->getMessage())
            );
        }

        return new JsonResponse($appResponse->toArray(), 200);
    }

    /**
     * @Route("/{file}/expiration/{date}", requirements={"file"}, defaults={"date" = "never"}, name="file_expiration_date")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fileExpirationDateAction(Request $request)
    {
        $appResponse = new AppResponse();

        $efs = $this->get('enginewerk_emission.storage.file_storage');

        if (null === $efs->findByShortIdentifier($request->get('file'))) {
            $appResponse->error(sprintf('File #%s not found.', $request->get('file')));
        } else {
            if ('never' === $request->get('date')) {
                $expirationDate = null;
            } else {
                $expirationDate = new \DateTimeImmutable($request->get('date'));
            }

            try {
                $efs->alterExpirationDate($request->get('file'), $expirationDate);

                $appResponse->success();
            } catch (\Exception $ex) {
                $appResponse->error('Can`t change expiration date');
                $this->get('logger')->error(sprintf('Can`t change expiration date of File #%s. %s', $request->get('file'), $ex->getMessage()));
            }
        }

        return new JsonResponse($appResponse->toArray(), 200);
    }

    /**
     * @Route("/replace/{replace}/with/{replacement}", name="replace_file")
     * @Method({"GET"})
     *
     * @param string $replace
     * @param string $replacement
     *
     * @return JsonResponse
     */
    public function replaceFileAction($replace, $replacement)
    {
        $appResponse = new AppResponse();

        $efs = $this->get('enginewerk_emission.storage.file_storage');

        try {
            $efs->replace($replace, $replacement);
            $appResponse->success('File replaced.');
        } catch (\Exception $ex) {
            $this->get('logger')->error(sprintf('Can`t replace file. [%s] %s', get_class($ex), $ex->getMessage()));
            $appResponse->error(sprintf('Can`t replace file.'));
        }

        return new JsonResponse($appResponse->toArray());
    }

    /**
     * @Route("/files/{created_after}", defaults={"created_after" = null})
     * @Method({"GET"})
     *
     * @param string $created_after
     *
     * @return JsonResponse
     */
    public function filesAction($created_after = null)
    {
        $appResponse = new AppResponse();

        $appResponse->success();
        $appResponse->data(
            $this->get('enginewerk_emission.service.file_read_service')
                ->getFilesForJsonApi(new \DateTime($created_after ?: 'now'))
        );

        return new JsonResponse($appResponse->toArray(), 200);
    }
}
