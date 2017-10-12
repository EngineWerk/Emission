<?php
namespace Enginewerk\EmissionBundle\Controller;

use Enginewerk\EmissionBundle\Storage\FileNotFoundException;
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
        $fileShortIdentifier = (string) $request->get('fileShortIdentifier');

        try {
            return $this->get('enginewerk_emission.service.stream_service')->getStreamResponse(
                $fileShortIdentifier,
                (bool) $request->get('dl')
            );
        } catch (FileNotFoundException $fileNotFoundException) {
            throw $this->createNotFoundException(
                sprintf(
                    'File identified by "%s" not found.',
                    $fileShortIdentifier
                )
            );
        }
    }
}
