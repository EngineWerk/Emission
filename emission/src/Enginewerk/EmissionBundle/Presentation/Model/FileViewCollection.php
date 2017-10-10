<?php
namespace Enginewerk\EmissionBundle\Presentation\Model;

use Enginewerk\Common\Collection\AbstractRestrictedArrayCollection;

final class FileViewCollection extends AbstractRestrictedArrayCollection
{
    const ELEMENT_TYPE = FileView::class;
}
