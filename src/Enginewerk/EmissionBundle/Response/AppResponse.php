<?php

namespace Enginewerk\EmissionBundle\Response;

/**
 * Description of AppResponse.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class AppResponse
{
    private $message = null;
    private $status = null;
    private $data = null;

    public $response = array();

    /**
     * Sets status on Error and response message.
     *
     * @param string|null $message
     */
    public function error($message = null)
    {
        $this->message = $message;
        $this->status = 'Error';
    }

    /**
     * Sets status on Success and response message.
     *
     * @param string|null $message
     */
    public function success($message = null)
    {
        $this->message = $message;
        $this->status = 'Success';
    }

    /**
     * Sets response data.
     *
     * @param string|null $data
     */
    public function data($data)
    {
        $this->data = $data;
    }

    private function prepareResponseStatus()
    {
        $this->response['status'] = $this->status;
    }

    private function prepareResponseMessage()
    {
        $this->response['message'] = $this->message;
    }

    private function prepareResponseData()
    {
        $this->response['data'] = $this->data;
    }

    /**
     * Sets response data and returns $this.
     *
     * @return \Enginewerk\EmissionBundle\Response\AppResponse
     */
    public function response()
    {
        ($this->message) ? $this->prepareResponseMessage() : '';
        ($this->data) ? $this->prepareResponseData() : '';
        ($this->status) ? $this->prepareResponseStatus() : '';

        return $this;
    }
}
