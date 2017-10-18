<?php
namespace Enginewerk\EmissionBundle\Storage\Manager;

use Enginewerk\ApplicationBundle\DateTime\DateTimeReadInterface;
use Enginewerk\Common\Uuid\UuidGeneratorInterface;
use Enginewerk\EmissionBundle\Entity\File as FileEntity;
use Enginewerk\EmissionBundle\Generator\PublicIdentifierGeneratorInterface;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;
use Enginewerk\EmissionBundle\Storage\Configuration\FileCreateConfigurationInterface;
use Enginewerk\UserBundle\Repository\UserFinderInterface;

final class FileCreationManager implements CreateFileInterface
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    /** @var PublicIdentifierGeneratorInterface */
    private $publicIdentifierGenerator;

    /** @var UserFinderInterface */
    private $userRepository;

    /** @var FileCreateConfigurationInterface */
    private $configuration;

    /** @var DateTimeReadInterface */
    private $dateTimeReader;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param UuidGeneratorInterface $uuidGenerator
     * @param PublicIdentifierGeneratorInterface $publicIdentifierGenerator
     * @param UserFinderInterface $userRepository
     * @param FileCreateConfigurationInterface $configuration
     * @param DateTimeReadInterface $dateTimeReader
     */
    public function __construct(
        FileRepositoryInterface $fileRepository,
        UuidGeneratorInterface $uuidGenerator,
        PublicIdentifierGeneratorInterface $publicIdentifierGenerator,
        UserFinderInterface $userRepository,
        FileCreateConfigurationInterface $configuration,
        DateTimeReadInterface $dateTimeReader
    ) {
        $this->fileRepository = $fileRepository;
        $this->uuidGenerator = $uuidGenerator;
        $this->publicIdentifierGenerator = $publicIdentifierGenerator;
        $this->userRepository = $userRepository;
        $this->configuration = $configuration;
        $this->dateTimeReader = $dateTimeReader;
    }

    /**
     * @inheritdoc
     */
    public function createFile($fileName, $fileChecksum, $fileSize, $userIdentifier, $mimeType)
    {
        $user = $this->userRepository->getByEmail($userIdentifier);
        $file = new FileEntity();

        $file->setName($fileName);
        $file->setChecksum($fileChecksum);
        $file->setSize($fileSize);
        $file->setType($mimeType);
        $file->setUser($user);
        $file->setComplete(false);

        $publicIdentifier = $this->publicIdentifierGenerator
            ->generate($this->configuration->getPublicIdentifierLength());
        $file->setPublicIdentifier($publicIdentifier);

        $file->setFileHash($this->uuidGenerator->generate());

        $expirationTime = $this->dateTimeReader->getCurrentDateTime();
        $expirationTime->add(new \DateInterval(sprintf('PT%dS', $this->configuration->getTimeToLive())));

        $file->setExpirationDate($expirationTime);

        $this->fileRepository->persist($file);

        return $file->getPublicIdentifier();
    }
}
