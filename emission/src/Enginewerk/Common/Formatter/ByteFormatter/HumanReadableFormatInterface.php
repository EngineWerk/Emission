<?php
namespace Enginewerk\Common\Formatter\ByteFormatter;

interface HumanReadableFormatInterface
{
    /**
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public function format($bytes, $precision = 2);
}
