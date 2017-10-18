<?php
namespace Enginewerk\EmissionBundle\Storage\Configuration;

final class Configuration implements FileCreateConfigurationInterface
{
    public function getTimeToLive()
    {
        return 86600;
    }

    public function getPublicIdentifierLength()
    {
        return 8;
    }
}
