<?php
namespace Enginewerk\ApplicationBundle\Response;

class ApplicationResponse extends AbstractApplicationResponse
{
    /**
     * @deprecated Please use SuccessResponse or ErrorResponse
     */
    public function __construct()
    {
        parent::__construct('', '', '');
    }

    /**
     * Sets status on Error and response message.
     *
     * @param string|null $message
     */
    public function error($message = null)
    {
        $this->message = $message;
        $this->status = parent::STATUS_ERROR;
    }

    /**
     * Sets status on Success and response message.
     *
     * @param string|null $message
     */
    public function success($message = null)
    {
        $this->message = $message;
        $this->status = parent::STATUS_SUCCESS;
    }

    /**
     * Sets response data.
     *
     * @param string|string[]|null $data
     */
    public function data($data)
    {
        $this->data = $data;
    }
}
