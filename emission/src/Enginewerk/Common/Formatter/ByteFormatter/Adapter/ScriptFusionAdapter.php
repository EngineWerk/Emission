<?php
namespace Enginewerk\Common\Formatter\ByteFormatter\Adapter;

use Enginewerk\Common\Formatter\ByteFormatter\HumanReadableFormatInterface;
use ScriptFUSION\Byte\Base;
use ScriptFUSION\Byte\ByteFormatter;

final class ScriptFusionAdapter implements HumanReadableFormatInterface
{
    /** @var ByteFormatter */
    private $formatter;

    /**
     * @param ByteFormatter $formatter
     */
    public function __construct(ByteFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public function format($bytes, $precision = 2)
    {
        return $this->formatter
            ->setBase(Base::DECIMAL)
            ->format($bytes, $precision);
    }
}
