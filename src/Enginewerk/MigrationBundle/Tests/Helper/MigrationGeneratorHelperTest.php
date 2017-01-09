<?php
namespace Enginewerk\MigrationBundle\Tests\Helper;

use Enginewerk\MigrationBundle\Helper\MigrationGeneratorHelper;

final class MigrationGeneratorHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldGenerateMigrationVersionWithTicketNumber()
    {
        static::assertEquals(
            '2.0_XXX',
            MigrationGeneratorHelper::generateMigrationVersionWithTicketNumber('2.0', 'XXX')
        );
        static::assertEquals(
            '_',
            MigrationGeneratorHelper::generateMigrationVersionWithTicketNumber('', '')
        );
        static::assertEquals(
            '~_',
            MigrationGeneratorHelper::generateMigrationVersionWithTicketNumber('~', '')
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Only alpha-numeric and _ are allowed.
     */
    public function shouldThrowException()
    {
        static::assertEquals(
            '_',
            MigrationGeneratorHelper::generateMigrationVersionWithTicketNumber('', '~')
        );
    }
}
