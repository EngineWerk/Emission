<?php
namespace Enginewerk\ApplicationBundle\Tests\Response;

use Enginewerk\ApplicationBundle\Response\ApplicationResponse;
use Enginewerk\ApplicationBundle\Response\ErrorResponse;

class ErrorResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function toArray()
    {
        $response = new ErrorResponse(
            'SuccessMessage',
            'SomeDataString'
        );

        $this->assertEquals(
            [
                'response' => [
                    'message' => 'SuccessMessage',
                    'status' => ApplicationResponse::STATUS_ERROR,
                    'data' => 'SomeDataString',
                ],
            ],
            $response->toArray()
        );
    }

    /**
     * @test
     */
    public function toArrayWithDefault()
    {
        $response = new ErrorResponse();

        $this->assertEquals(
            [
                'response' => [
                    'message' => ErrorResponse::DEFAULT_MESSAGE,
                    'status' => ApplicationResponse::STATUS_ERROR,
                    'data' => null,
                ],
            ],
            $response->toArray()
        );
    }
}
