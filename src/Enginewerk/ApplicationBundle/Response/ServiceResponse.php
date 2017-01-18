<?php
namespace Enginewerk\ApplicationBundle\Response;

class ServiceResponse
{
    /** @var  int */
    private $responseCode;

    /** @var  string[]|null */
    private $responseContent;

    /**
     * @param int $responseCode
     * @param string[]|null $responseContent
     */
    public function __construct($responseCode, array $responseContent = null)
    {
        $this->responseCode = $responseCode;
        $this->responseContent = $responseContent;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @return string[]|null
     */
    public function getResponseContent()
    {
        return $this->responseContent;
    }
}
