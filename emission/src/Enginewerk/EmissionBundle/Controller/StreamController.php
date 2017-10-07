<?php
namespace Enginewerk\EmissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StreamController extends Controller
{
    /**
     * @param Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return StreamedResponse
     */
    public function downloadFileAction(Request $request)
    {
        $fileShortIdentifier = $request->get('fileShortIdentifier');

        if (null === ($file = $this->get('enginewerk_emission.service.file_read_service')->findByShortIdentifier($fileShortIdentifier))) {
            throw $this->createNotFoundException(
                sprintf(
                    'File identified by "%s" not found.',
                    $request->get('fileShortIdentifier')
                )
            );
        }

        $responseFile = $this->get('enginewerk_emission.storage.file_storage')->getFileForDownload($fileShortIdentifier);

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
}
