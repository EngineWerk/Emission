<?php

namespace Enginewerk\EmissionBundle\Tests\Generator;

use Enginewerk\EmissionBundle\Generator\Hash;

class HashTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $hash = Hash::generate();
        $this->assertEquals('a', $hash);
    }

    public function testGenerateSequence()
    {
        $hash = Hash::generateSequencedHash();
        $this->assertEquals('a', $hash);

        $hash = Hash::generateSequencedHash('a');
        $this->assertEquals('b', $hash);

        $hash = Hash::generateSequencedHash('z');
        $this->assertEquals('A', $hash);

        $hash = Hash::generateSequencedHash('Z');
        $this->assertEquals('1', $hash);

        $hash = Hash::generateSequencedHash('abc');
        $this->assertEquals('abd', $hash);

        $hash = Hash::generateSequencedHash('abc0');
        $this->assertEquals('abda', $hash);
    }

    public function testGenerateRandomHash()
    {
        $hash = Hash::genereateRandomHash();
        $this->assertEquals(4, strlen($hash));

        $hash = Hash::genereateRandomHash(4);
        $this->assertEquals(4, strlen($hash));

        $hash = Hash::genereateRandomHash(10);
        $this->assertEquals(10, strlen($hash));
    }
}
