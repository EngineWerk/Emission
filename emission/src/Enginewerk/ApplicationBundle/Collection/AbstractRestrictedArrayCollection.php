<?php
namespace Enginewerk\ApplicationBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\InvalidArgumentException;

abstract class AbstractRestrictedArrayCollection extends ArrayCollection
{
    const ELEMENT_TYPE = null;

    public function __construct(array $elements = [])
    {
        foreach ($elements as $element) {
            $this->validateType($element);
        }

        parent::__construct($elements);
    }

    public function add($element)
    {
        $this->validateType($element);

        return parent::add($element);
    }

    public function set($key, $value)
    {
        $this->validateType($value);

        parent::set($key, $value);
    }

    public function removeElement($element)
    {
        $this->validateType($element);

        return parent::removeElement($element);
    }

    private function validateType($element)
    {
        if (null !== static::ELEMENT_TYPE && get_class($element) !== static::ELEMENT_TYPE) {
            throw new InvalidArgumentException(sprintf(
                'Expected element type of "%s", "%s" given',
                static::ELEMENT_TYPE,
                get_class($element)
            ));
        }
    }
}
