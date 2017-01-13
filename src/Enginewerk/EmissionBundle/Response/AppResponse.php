<?php
namespace Enginewerk\EmissionBundle\Response;

class AppResponse
{
    /** @var string */
    private $message;

    /** @var  string */
    private $status;

    /** @var  string|string[] */
    private $data;

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
     * @param string|string[]|null $data
     */
    public function data($data)
    {
        $this->data = $data;
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        return [
            'response' => [
                'message' => $this->message ?: '',
                'status' => $this->status ?: '',
                'data' => $this->data ?: '',
            ],
        ];
    }
}
