<?php
namespace Enginewerk\ApplicationBundle\Tests\Response;

use Enginewerk\ApplicationBundle\Response\WebApplicationResponse;

class ApplicationResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function toArray()
    {
        $response = new WebApplicationResponse();
        $response->success('SuccessMessage');
        $response->data('SomeDataString');

        $this->assertEquals(
            [
                'response' => [
                    'message' => 'SuccessMessage',
                    'status' => WebApplicationResponse::STATUS_SUCCESS,
                    'data' => 'SomeDataString',
                ],
            ],
            $response->toArray()
        );
    }
}
