<?php
namespace Enginewerk\FileManagementBundle\Tests\Generator;

use Enginewerk\FileManagementBundle\Generator\HashGenerator;
use Enginewerk\FileManagementBundle\Generator\InvalidLengthException;

class HashGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function generate()
    {
        $hash = HashGenerator::generate();
        $this->assertEquals('a', $hash);
    }

    /**
     * @test
     */
    public function invalidLength()
    {
        $this->setExpectedException(InvalidLengthException::class);

        HashGenerator::generateRandomHash(0);
    }

    /**
     * @test
     */
    public function generateSequence()
    {
        $hash = HashGenerator::generateSequencedHash();
        $this->assertEquals('a', $hash);

        $hash = HashGenerator::generateSequencedHash('a');
        $this->assertEquals('b', $hash);

        $hash = HashGenerator::generateSequencedHash('z');
        $this->assertEquals('A', $hash);

        $hash = HashGenerator::generateSequencedHash('Z');
        $this->assertEquals('1', $hash);

        $hash = HashGenerator::generateSequencedHash('abc');
        $this->assertEquals('abd', $hash);

        $hash = HashGenerator::generateSequencedHash('abc0');
        $this->assertEquals('abda', $hash);
    }

    /**
     * @test
     */
    public function generateRandomHash()
    {
        $hash = HashGenerator::generateRandomHash();
        $this->assertEquals(4, strlen($hash));

        $hash = HashGenerator::generateRandomHash(4);
        $this->assertEquals(4, strlen($hash));

        $hash = HashGenerator::generateRandomHash(10);
        $this->assertEquals(10, strlen($hash));
    }
}
