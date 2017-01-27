<?php
namespace Enginewerk\ApplicationBundle\Response;

class SuccessResponse extends AbstractApplicationResponse
{
    const DEFAULT_MESSAGE = 'OK';

    /**
     * @param string $message
     * @param null $data
     */
    public function __construct($message = self::DEFAULT_MESSAGE, $data = null)
    {
        parent::__construct(parent::STATUS_SUCCESS, $message, $data);
    }
}
