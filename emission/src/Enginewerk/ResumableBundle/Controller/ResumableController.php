<?php
namespace Enginewerk\ResumableBundle\Controller;

use Enginewerk\ResumableBundle\FileUpload\FileRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ResumableController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadChunkTestAction(Request $request)
    {
        $fileUploadService = $this->get('enginewerk_resumable.service.resumable_file_upload_service');
        $serviceResponse = $fileUploadService->findFileChunk(
            $request->get('resumableFilename'),
            $request->get('resumableIdentifier'),
            (int) $request->get('resumableTotalSize'),
            (int) $request->get('resumableCurrentStartByte'),
            (int) $request->get('resumableCurrentEndByte')
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
        $fileUploadService = $this->get('enginewerk_resumable.service.resumable_file_upload_service');
        $serviceResponse = $fileUploadService->uploadFromRequest(
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
