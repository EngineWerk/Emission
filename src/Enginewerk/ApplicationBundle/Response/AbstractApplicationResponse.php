<?php
namespace Enginewerk\ApplicationBundle\Response;

abstract class AbstractApplicationResponse
{
    const STATUS_SUCCESS = 'Success';
    const STATUS_ERROR = 'Error';

    /** @var  string */
    protected $status;

    /** @var  string */
    protected $message;

    /** @var  string|string[]|null $data */
    protected $data;

    /**
     * @param string $status
     * @param string $message
     * @param null|string|\string[] $data
     */
    public function __construct($status, $message, $data)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * @return array
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
