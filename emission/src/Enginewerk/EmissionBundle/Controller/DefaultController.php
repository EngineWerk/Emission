<?php
namespace Enginewerk\EmissionBundle\Controller;

use Enginewerk\EmissionBundle\Form\Type\ResumableFileBlockType;
use Enginewerk\EmissionBundle\Form\Type\ResumableFileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $files = $this->get('enginewerk_emission.service.file_presentation_service')->findAll();

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

        return new Response($this->renderView(
            'EnginewerkEmissionBundle:Default:index.html.twig',
            [
                'Files' => $files,
                'FileBlockForm' => $fileBlockForm->createView(),
                'FileForm' => $fileForm->createView(),
                'Capabilities' => $capabilities,
                'MaxUploadFileSize' => $maxUploadFileSize,
            ]
        ));
    }
}
