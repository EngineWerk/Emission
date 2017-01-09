<?php
namespace Enginewerk\FSBundle\Storage;

/**
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
interface StorageInterface
{
    /**
     * @param  string                                        $key
     * @param  \Symfony\Component\HttpFoundation\File\File $uploadedFile
     *
     * @return int                                     File size
     */
    public function put($key, $uploadedFile);

    /**
     * @param  string                                      $key
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function get($key);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete($key);
}
