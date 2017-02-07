<?php
namespace Enginewerk\FileManagementBundle\Tests\Service;

use Enginewerk\FileManagementBundle\Entity\File as FileEntity;
use Enginewerk\FileManagementBundle\Model\File;
use Enginewerk\FileManagementBundle\Model\FileCollection;
use Enginewerk\FileManagementBundle\Model\FileFactoryInterface;
use Enginewerk\FileManagementBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\FileManagementBundle\Repository\FileRepositoryInterface;
use Enginewerk\FileManagementBundle\Service\FileReadService;

class FileReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  FileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileRepository;

    /** @var  FileBlockRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileBlockRepository;

    /** @var  FileFactoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileFactory;

    public function setUp()
    {
        $this->fileRepository = $this->createMock(FileRepositoryInterface::class);
        $this->fileBlockRepository = $this->createMock(FileBlockRepositoryInterface::class);
        $this->fileFactory = $this->createMock(FileFactoryInterface::class);
    }

    /**
     * @test
     */
    public function findAllFiles()
    {
        $fileReadService = new FileReadService($this->fileRepository, $this->fileBlockRepository, $this->fileFactory);

        $fileEntity = new FileEntity();
        $fileRepositoryResponse = [
            $fileEntity,
            $fileEntity,
        ];

        $this->fileRepository
            ->expects($this->once())
            ->method('getFiles')
            ->will($this->returnValue($fileRepositoryResponse));

        $file = new File(99, 'abc', 'checksum', 'FileName', 'mimeType', 314, '314MB', true, 'FileMaster');
        $this->fileFactory
            ->expects($this->exactly(2))
            ->method('createFromEntity')
            ->with($fileEntity)
            ->will($this->returnValue($file));

        $this->assertEquals(
            new FileCollection([
                $file,
                $file,
            ]),
            $fileReadService->findAllFiles()
        );
    }
}
