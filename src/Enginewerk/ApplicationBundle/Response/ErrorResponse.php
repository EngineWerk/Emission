<?php
namespace Enginewerk\ApplicationBundle\Response;

class ErrorResponse extends AbstractApplicationResponse
{
    const DEFAULT_MESSAGE = 'N_OK';

    /**
     * @param string $message
     * @param null $data
     */
    public function __construct($message = self::DEFAULT_MESSAGE, $data = null)
    {
        parent::__construct(parent::STATUS_ERROR, $message, $data);
    }
}
