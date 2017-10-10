<?php
namespace Enginewerk\EmissionBundle\Storage\Model;

use Enginewerk\Common\Collection\AbstractRestrictedArrayCollection;
use Symfony\Component\HttpFoundation\File\File;

final class SystemFileCollection extends AbstractRestrictedArrayCollection
{
    const ELEMENT_TYPE = File::class;
}
