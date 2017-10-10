<?php
namespace Enginewerk\EmissionBundle\Storage\Model;

use Enginewerk\Common\Collection\AbstractRestrictedArrayCollection;

final class FilePartCollection extends AbstractRestrictedArrayCollection
{
    const ELEMENT_TYPE = FilePart::class;
}
