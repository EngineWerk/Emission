<?php
namespace Enginewerk\Common\Uuid;

use Enginewerk\Common\Uuid\Adapter\UuidVersion4AdapterInterface;

class UuidVersion4Generator implements UuidGeneratorInterface
{
    /** @var UuidVersion4AdapterInterface */
    private $generator;

    /**
     * @param UuidVersion4AdapterInterface $generator
     */
    public function __construct(UuidVersion4AdapterInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        return $this->generator->generateV4();
    }
}
