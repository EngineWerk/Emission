<?php
namespace Enginewerk\ResumableBundle\Controller;

use Enginewerk\ApplicationBundle\Controller\BaseController;
use Enginewerk\ResumableBundle\Request\FileRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ResumableController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadChunkTestAction(Request $request)
    {
        $fileUploadService = $this->get('enginewerk_emission.service.file_read_service');
        $serviceResponse = $fileUploadService->findFileChunk(
            $request->get('resumableFilename'),
            $request->get('resumableIdentifier'),
            $request->get('resumableTotalSize'),
            $request->get('resumableCurrentStartByte'),
            $request->get('resumableCurrentEndByte')
        );

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
    public function uploadAction(Request $request)
    {
        $fileService = $this->get('enginewerk_resumable.service.resumable_file_upload_service');
        $serviceResponse = $fileService->uploadFromRequest(
            $request->files->get('form')['uploadedFile'],
            new FileRequest($request->request->get('form')),
            $this->getUser()
        );

        return new JsonResponse(
            $serviceResponse->getResponseContent(),
            $serviceResponse->getResponseCode()
        );
    }
}
