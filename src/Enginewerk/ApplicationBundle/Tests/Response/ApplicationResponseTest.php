<?php
namespace Enginewerk\ApplicationBundle\Tests\Response;

use Enginewerk\ApplicationBundle\Response\ApplicationResponse;

class ApplicationResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function toArray()
    {
        $response = new ApplicationResponse();
        $response->success('SuccessMessage');
        $response->data('SomeDataString');

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
}
