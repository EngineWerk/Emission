<?php

namespace Enginewerk\FSBundle\Storage;

/**
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
interface StorageInterface
{
    /**
     * @param  type                                        $key
     * @param  \Symfony\Component\HttpFoundation\File\File $uploadedFile
     * @return integer                                     File size
     */
    public function put($key, $uploadedFile);

    /**
     * @param  string                                      $key
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function get($key);

    public function delete($key);
}
