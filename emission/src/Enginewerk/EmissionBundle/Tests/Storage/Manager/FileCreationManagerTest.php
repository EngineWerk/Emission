<?php
namespace Enginewerk\EmissionBundle\Tests\Storage\Manager;

use Enginewerk\ApplicationBundle\DateTime\DateTimeReadInterface;
use Enginewerk\Common\Uuid\UuidGeneratorInterface;
use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Generator\PublicIdentifierGeneratorInterface;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;
use Enginewerk\EmissionBundle\Storage\Configuration\FileCreateConfigurationInterface;
use Enginewerk\EmissionBundle\Storage\Manager\FileCreationManager;
use Enginewerk\UserBundle\Entity\User;
use Enginewerk\UserBundle\Repository\UserFinderInterface;

class FileCreationManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var FileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileRepository;

    /** @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $uuidGenerator;

    /** @var PublicIdentifierGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $publicIdentifierGenerator;

    /** @var UserFinderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $userRepository;

    /** @var FileCreateConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $configuration;

    /** @var DateTimeReadInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $dateTimeReader;

    public function setUp()
    {
        $this->fileRepository = $this->createMock(FileRepositoryInterface::class);
        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->publicIdentifierGenerator = $this->createMock(PublicIdentifierGeneratorInterface::class);
        $this->userRepository = $this->createMock(UserFinderInterface::class);
        $this->configuration = $this->createMock(FileCreateConfigurationInterface::class);
        $this->dateTimeReader = $this->createMock(DateTimeReadInterface::class);
    }

    /**
     * @test
     */
    public function createFile()
    {
        $fileCreationManager = new FileCreationManager(
            $this->fileRepository,
            $this->uuidGenerator,
            $this->publicIdentifierGenerator,
            $this->userRepository,
            $this->configuration,
            $this->dateTimeReader
        );

        $createdFilePublicIdentifier = 'publicIdentifier';
        $createFileUuid = 'someUuid';

        $fileName = 'aFileName';
        $fileChecksum = 'aFileChecksum';
        $fileSize = 999;
        $userIdentifier = 'aUserIdentifier';
        $mimeType = 'someMimeType';

        $currentTime = 1;
        $currentDateTime = new \DateTime('@' . $currentTime);

        $user = $this->createMock(User::class);
        $this->userRepository
            ->expects(static::once())
            ->method('getByEmail')
            ->with($userIdentifier)
            ->willReturn($user);

        $this->configuration
            ->expects(static::once())
            ->method('getPublicIdentifierLength')
            ->willReturn(4);

        $timeToLive = 2;
        $this->configuration
            ->expects(static::once())
            ->method('getTimeToLive')
            ->willReturn($timeToLive);

        $this->publicIdentifierGenerator
            ->expects(static::once())
            ->method('generate')
            ->with(4)
            ->willReturn($createdFilePublicIdentifier);

        $this->uuidGenerator
            ->expects(static::once())
            ->method('generate')
            ->willReturn($createFileUuid);

        $this->dateTimeReader
            ->expects(static::once())
            ->method('getCurrentDateTime')
            ->willReturn($currentDateTime);

        $fileEntity = new File();
        $fileEntity->setPublicIdentifier($createdFilePublicIdentifier);
        $fileEntity->setFileHash($createFileUuid);
        $fileEntity->setExpirationDate($currentDateTime);
        $fileEntity->setName($fileName);
        $fileEntity->setChecksum($fileChecksum);
        $fileEntity->setSize($fileSize);
        $fileEntity->setType($mimeType);
        $fileEntity->setUser($user);
        $fileEntity->setComplete(false);

        $this->fileRepository
            ->expects(static::once())
            ->method('persist')
            ->with($fileEntity);

        $this->assertEquals($currentTime, $currentDateTime->getTimestamp());
        $this->assertEquals(
            $createdFilePublicIdentifier,
            $fileCreationManager->createFile($fileName, $fileChecksum, $fileSize, $userIdentifier, $mimeType)
        );
        $this->assertEquals($currentTime + $timeToLive, $currentDateTime->getTimestamp());
    }
}
