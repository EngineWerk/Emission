<?php
namespace Enginewerk\EmissionBundle\Controller;

use Enginewerk\ApplicationBundle\Response\ApplicationResponse;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileBlockType;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $fileBlockForm = $this->createForm(new ResumableFileBlockType());
        $fileForm = $this->createForm(new ResumableFileType());

        $responseContent = $this->renderView(
            'EnginewerkEmissionBundle:Default:index.html.twig',
            [
                'Files' => $this->get('enginewerk_emission.service.file_read_service')->findAllFiles(),
                'FileBlockForm' => $fileBlockForm->createView(),
                'FileForm' => $fileForm->createView(),
                'Capabilities' => $this->get('enginewerk_emission.service.capabilities_service')->getCapabilities(),
                'MaxUploadFileSize' => $this->get('enginewerk_emission.service.capabilities_service')->getMaxUploadFileSize(),
            ]
        );

        return new Response($responseContent);
    }

    /**
     * @param  Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function showFileAction(Request $request)
    {
        $fileReadService = $this->get('enginewerk_emission.service.file_read_service');

        if (null === ($file = $fileReadService->findByShortIdentifier($request->get('file')))) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('file')));
        }

        $responseContent = $this->renderView(
            'EnginewerkEmissionBundle:Default:showFile.html.twig',
            [
                'File' => $file,
            ]
        );

        return new Response($responseContent);
    }

    /**
     * @param  Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function showFileContentAction(Request $request)
    {
        $fileReadService = $this->get('enginewerk_emission.service.file_read_service');

        if (null === ($file = $fileReadService->findByShortIdentifier($request->get('fileShortIdentifier')))) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('fileShortIdentifier')));
        }

        $appResponse = new ApplicationResponse();
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
     * @param  Request $request
     *
     * @return StreamedResponse
     */
    public function downloadFileAction(Request $request)
    {
        set_time_limit(0);

        return $this->get('enginewerk_emission.service.file_stream_service')
            ->getFileForDownload(
                $request->get('fileShortIdentifier'),
                $request->get('dl') === '1'
            );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $serviceResponse = $this->get('enginewerk_emission.storage.file_storage')
            ->deleteFile($request->get('file'));

        return new JsonResponse(
            $serviceResponse->getResponseContent(),
            $serviceResponse->getResponseCode()
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fileExpirationDateAction(Request $request)
    {
        $serviceResponse = $this->get('enginewerk_emission.storage.file_storage')
            ->setFileExpirationDate(
                $request->get('file'),
                $request->get('date') ? new \DateTimeImmutable($request->get('date')) : null
            );

        return new JsonResponse(
            $serviceResponse->getResponseContent(),
            $serviceResponse->getResponseCode()
        );
    }

    /**
     * @param string $replace
     * @param string $replacement
     *
     * @return JsonResponse
     */
    public function replaceFileAction($replace, $replacement)
    {
        $serviceResponse = $this->get('enginewerk_emission.storage.file_storage')
            ->replace($replace, $replacement);

        return new JsonResponse(
            $serviceResponse->getResponseContent(),
            $serviceResponse->getResponseCode()
        );
    }

    /**
     * @param string|null $createdAfter
     *
     * @return JsonResponse
     */
    public function filesAction($createdAfter = null)
    {
        $serviceResponse = $this->get('enginewerk_emission.storage.file_storage')
            ->getFilesForJsonApi($createdAfter);

        return new JsonResponse(
            $serviceResponse->getResponseContent(),
            $serviceResponse->getResponseCode()
        );
    }
}
