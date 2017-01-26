<?php
namespace Enginewerk\EmissionBundle\Model;

use Enginewerk\EmissionBundle\Entity\File as FileEntity;

class FileFactory implements FileFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createFromEntity(FileEntity $file)
    {
        return new File(
            (int) $file->getId(),
            $file->getFileId(),
            $file->getChecksum(),
            $file->getName(),
            $file->getType(),
            (int) $file->getSize(),
            $this->bytesToHumanReadable((int) $file->getSize()),
            (bool) $file->getComplete(),
            $file->getUser()->getUsername()
        );
    }

    /**
     * @param int $bytes
     *
     * @return string
     */
    private function bytesToHumanReadable($bytes)
    {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;
        $petabyte = $terabyte * 1024;

        if ($bytes < $kilobyte) {
            $humanReadableSize = number_format($bytes, 2) . 'B';
        } elseif ($bytes < $megabyte) {
            $humanReadableSize = number_format($bytes / $kilobyte, 2) . 'KB';
        } elseif ($bytes < $gigabyte) {
            $humanReadableSize = number_format($bytes / $megabyte, 2) . 'MB';
        } elseif ($bytes < $terabyte) {
            $humanReadableSize = number_format($bytes / $gigabyte, 2) . 'GB';
        } elseif ($bytes < $petabyte) {
            $humanReadableSize = number_format($bytes / $terabyte, 2) . 'TB';
        } else {
            $humanReadableSize = number_format($bytes / $petabyte, 2) . 'PB';
        }

        return $humanReadableSize;
    }
}
