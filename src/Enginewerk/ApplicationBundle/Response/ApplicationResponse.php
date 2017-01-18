<?php
namespace Enginewerk\ApplicationBundle\Response;

class ApplicationResponse
{
    const STATUS_SUCCESS = 'Success';
    const STATUS_ERROR = 'Error';

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
        $this->status = static::STATUS_ERROR;
    }

    /**
     * Sets status on Success and response message.
     *
     * @param string|null $message
     */
    public function success($message = null)
    {
        $this->message = $message;
        $this->status = static::STATUS_SUCCESS;
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
