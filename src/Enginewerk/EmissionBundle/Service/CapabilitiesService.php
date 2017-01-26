<?php
namespace Enginewerk\EmissionBundle\Service;

class CapabilitiesService
{
    /** @var  int */
    private $uploaderMaxChunkSize;

    /**
     * @param int $uploaderMaxChunkSize Megabytes
     */
    public function __construct($uploaderMaxChunkSize)
    {
        $this->uploaderMaxChunkSize = $uploaderMaxChunkSize;
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'browser_file_memory_limit' => $this->uploaderMaxChunkSize,
        ];
    }

    /**
     * @return int Megabytes
     */
    public function getMaxUploadFileSize()
    {
        $uploaderCapabilities = $this->getCapabilities();
        sort($uploaderCapabilities, SORT_NUMERIC);

        return (int) $uploaderCapabilities[0];
    }
}
