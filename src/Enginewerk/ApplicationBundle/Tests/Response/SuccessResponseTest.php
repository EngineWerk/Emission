<?php
namespace Enginewerk\ApplicationBundle\Tests\Response;

use Enginewerk\ApplicationBundle\Response\ApplicationResponse;
use Enginewerk\ApplicationBundle\Response\SuccessResponse;

class SuccessResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function toArray()
    {
        $response = new SuccessResponse(
            'SuccessMessage',
            'SomeDataString'
        );

        $this->assertEquals(
            [
                'response' => [
                    'message' => 'SuccessMessage',
                    'status' => ApplicationResponse::STATUS_SUCCESS,
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
        $response = new SuccessResponse();

        $this->assertEquals(
            [
                'response' => [
                    'message' => SuccessResponse::DEFAULT_MESSAGE,
                    'status' => ApplicationResponse::STATUS_SUCCESS,
                    'data' => null,
                ],
            ],
            $response->toArray()
        );
    }
}
