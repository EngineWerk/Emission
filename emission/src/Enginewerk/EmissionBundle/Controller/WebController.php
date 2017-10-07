<?php
namespace Enginewerk\EmissionBundle\Controller;

use Enginewerk\ApplicationBundle\Response\WebApplicationResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebController extends Controller
{
    /**
     * @param string $created_after
     *
     * @return JsonResponse
     */
    public function filesAction($created_after = null)
    {
        $appResponse = new WebApplicationResponse();

        $appResponse->success();
        $appResponse->data(
            $this->get('enginewerk_emission.service.file_read_service')
                ->getFilesForJsonApi(new \DateTime($created_after ?: 'now'))
        );

        return new JsonResponse($appResponse->toArray(), Response::HTTP_OK);
    }

    /**
     * @param Request $request
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

        return new Response($this->renderView('EnginewerkEmissionBundle:Default:showFile.html.twig', ['File' => $file]));
    }

    /**
     * @param Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function showFileContentAction(Request $request)
    {
        $fileReadService = $this->get('enginewerk_emission.service.file_read_service');

        if (null === ($file = $fileReadService->findByShortIdentifier($request->get('fileShortIdentifier')))) {
            throw $this->createNotFoundException(sprintf(
                'File #%s not found.',
                $request->get('fileShortIdentifier')
            ));
        }

        $appResponse = new WebApplicationResponse();
        $appResponse->success();
        $appResponse->data(
            $this->renderView(
                'EnginewerkEmissionBundle:Default:showFileContent.html.twig',
                ['File' => $file]
            )
        );

        return new JsonResponse($appResponse->toArray(), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $appResponse = new WebApplicationResponse();

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

        return new JsonResponse($appResponse->toArray(), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fileExpirationDateAction(Request $request)
    {
        $appResponse = new WebApplicationResponse();

        if (null === $this->get('enginewerk_emission.service.file_read_service')->findByShortIdentifier($request->get('file'))) {
            $appResponse->error(sprintf('File #%s not found.', $request->get('file')));
        } else {
            if ('never' === $request->get('date')) {
                $expirationDate = null;
            } else {
                $expirationDate = new \DateTimeImmutable($request->get('date'));
            }

            try {
                $this->get('enginewerk_emission.storage.file_storage')->alterExpirationDate($request->get('file'), $expirationDate);
                $appResponse->success();
            } catch (\Exception $ex) {
                $appResponse->error('Can`t change expiration date');
                $this->get('logger')->error(sprintf('Can`t change expiration date of File #%s. %s', $request->get('file'), $ex->getMessage()));
            }
        }

        return new JsonResponse($appResponse->toArray(), Response::HTTP_OK);
    }

    /**
     * @param string $replace
     * @param string $replacement
     *
     * @return JsonResponse
     */
    public function replaceFileAction($replace, $replacement)
    {
        $appResponse = new WebApplicationResponse();

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
}
