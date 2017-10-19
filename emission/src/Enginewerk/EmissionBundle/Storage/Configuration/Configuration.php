<?php
namespace Enginewerk\EmissionBundle\Storage\Configuration;

final class Configuration implements FileCreateConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getTimeToLive()
    {
        return 86600;
    }

    /**
     * @inheritdoc
     */
    public function getPublicIdentifierLength()
    {
        return 8;
    }
}
