<?php
namespace Enginewerk\ResumableBundle\FileUpload;

use Enginewerk\EmissionBundle\Presentation\Model\FileView;
use Enginewerk\ResumableBundle\FileUpload\Response\CompleteFileResponse;
use Enginewerk\ResumableBundle\FileUpload\Response\IncompleteFileResponse;

interface ResponseFactoryInterface
{
    /**
     * @param FileView $fileView
     *
     * @return IncompleteFileResponse
     */
    public function createIncompleteFileResponse(FileView $fileView);

    /**
     * @param FileView $fileView
     *
     * @return CompleteFileResponse
     */
    public function createCompleteFileResponse(FileView $fileView);
}
