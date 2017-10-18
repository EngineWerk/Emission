<?php
namespace Enginewerk\EmissionBundle\Storage\Configuration;

interface FileCreateConfigurationInterface
{
    /**
     * @return int seconds
     */
    public function getTimeToLive();

    /**
     * @return int Identifier length
     */
    public function getPublicIdentifierLength();
}
