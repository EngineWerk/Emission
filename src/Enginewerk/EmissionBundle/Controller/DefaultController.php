<?php

namespace Enginewerk\EmissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Enginewerk\EmissionBundle\Response\AppResponse;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileType;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileBlockType;

/**
 * DefaultController
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
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
                ->get('emission_file_storage')
                ->findAll();

        $fileBlockForm = $this->createForm(new ResumableFileBlockType());
        $fileForm = $this->createForm(new ResumableFileType());

        $capabilities = array(
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'browser_file_memory_limit' => $this->container->getParameter('app.uploader_max_chunk_size')
            );

        $uploaderCapabilities = $capabilities;
        sort($uploaderCapabilities, SORT_NUMERIC);
        $maxUploadFileSize = (int) $uploaderCapabilities[0];

        return array(
            'Files' => $files,
            'FileBlockForm' => $fileBlockForm->createView(),
            'FileForm' => $fileForm->createView(),
            'Capabilities' => $capabilities,
            'MaxUploadFileSize' => $maxUploadFileSize
                );
    }

    /**
     * @Route("/f/{file}", requirements={"file"}, name="show_file")
     * @Method({"GET"})
     * @Template()
     *
     * @param  Request                                                       $request
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showFileAction(Request $request)
    {
        $efs = $this->get('emission_file_storage');
        /* @var $efs \Enginewerk\EmissionBundle\Storage\FileStorage */

        if (null === ($file = $efs->find($request->get('file')))) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('file')));
        }

        return array('File' => $file);
    }

    /**
     * @Route("/fc/{file}", requirements={"file"}, name="show_file_content")
     * @Method({"GET"})
     *
     * @param  Request                                                       $request
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showFileContentAction(Request $request)
    {
        $efs = $this->get('emission_file_storage');
        /* @var $efs \Enginewerk\EmissionBundle\Storage\FileStorage */

        if (null === ($file = $efs->find($request->get('file')))) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('file')));
        }

        $appResponse = new AppResponse();
        $appResponse->success();
        $appResponse->data($this->renderView('EnginewerkEmissionBundle:Default:showFileContent.html.twig', array('File' => $file)));

        return new JsonResponse($appResponse->response(), 200);
    }

    /**
     * @Route("/d/{file}", requirements={"file"}, name="download_file", defaults={"dl" = 1})
     * @Route("/o/{file}", requirements={"file"}, name="open_file")
     * @Method({"GET"})
     *
     * @param  Request                                                       $request
     * @return StreamedResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function downloadFileAction(Request $request)
    {
        $efs = $this->get('emission_file_storage');
        /* @var $efs \Enginewerk\EmissionBundle\Storage\FileStorage */

        if (null === ($file = $efs->find($request->get('file')))) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $request->get('file')));
        }

        $responseFile = $efs->getFileForDownload($request->get('file'));

        set_time_limit(0);

        $response = new StreamedResponse();

        $response->headers->set('Content-Type', $file->getType());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if ($request->get('dl')) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getName().'"');
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
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $appResponse = new AppResponse();

        $efs = $this->get('emission_file_storage');
        /* @var $efs \Enginewerk\EmissionBundle\Storage\FileStorage */

        try {
            $efs->delete($request->get('file'));
            $appResponse->success();

        } catch (\Exception $ex) {
            $appResponse->error('Can`t delete File');
            $this->get('logger')->error(sprintf('Can`t delete File #%s. %s', $request->get('file'), $ex->getMessage()));
        }

        return new JsonResponse($appResponse->response(), 200);
    }

    /**
     * @Route("/{file}/expiration/{date}", requirements={"file"}, defaults={"date" = "never"}, name="file_expiration_date")
     * @Method({"GET"})
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return JsonResponse
     */
    public function fileExpirationDateAction(Request $request)
    {
        $appResponse = new AppResponse();

        $efs = $this->get('emission_file_storage');
        /* @var $efs \Enginewerk\EmissionBundle\Storage\FileStorage */

        if (null === $efs->find($request->get('file'))) {
            $appResponse->error(sprintf('File #%s not found.', $request->get('file')));
        } else {
            if ('never' == $request->get('date')) {
                $expirationDate = null;
            } else {
                $expirationDate = new \DateTime($request->get('date'));
            }

            try {
                $efs->alterExpirationDate($request->get('file'), $expirationDate);

                $appResponse->success();

            } catch (\Exception $ex) {
                $appResponse->error('Can`t change expiration date');
                $this->get('logger')->error(sprintf('Can`t change expiration date of File #%s. %s', $request->get('file'), $ex->getMessage()));
            }
        }

        return new JsonResponse($appResponse->response(), 200);
    }

    /**
     * @Route("/replace/{replace}/with/{replacement}", name="replace_file")
     * @Method({"GET"})
     */
    public function replaceFileAction($replace, $replacement)
    {
        $appResponse = new AppResponse();

        $efs = $this->get('emission_file_storage');
        /* @var $efs \Enginewerk\EmissionBundle\Storage\FileStorage */

        try {
            $efs->replace($replace, $replacement);
            $appResponse->success('File replaced.');

        } catch (\Exception $ex) {
            $this->get('logger')->error(sprintf('Can`t replace file. [%s] %s', get_class($ex), $ex->getMessage()));
            $appResponse->error(sprintf('Can`t replace file.'));
        }

        return new JsonResponse($appResponse->response());
    }

    /**
     * @Route("/files/{created_after}", defaults={"created_after" = null})
     * @Method({"GET"})
     */
    public function filesAction($created_after)
    {
        $createdAfter = ($created_after) ? new \DateTime($created_after) : null;

        $files = $this
                ->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:File')
                ->getFilesForJsonApi($createdAfter);

        $appResponse = new AppResponse();
        $appResponse->success();
        $appResponse->data($files);

        return new JsonResponse($appResponse->response(), 200);
    }
}
