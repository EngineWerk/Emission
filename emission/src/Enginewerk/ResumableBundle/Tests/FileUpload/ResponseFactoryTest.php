<?php
namespace Enginewerk\ResumableBundle\Tests\FileUpload;

use Enginewerk\ResumableBundle\FileUpload\ResponseFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlGenerator;

    protected function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testIncompleteFileResponse()
    {
        $responseFactory = new ResponseFactory($this->urlGenerator);
    }
}
