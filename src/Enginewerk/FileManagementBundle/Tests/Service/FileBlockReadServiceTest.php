<?php
namespace Enginewerk\FileManagementBundle\Tests\Service;

use Enginewerk\FileManagementBundle\Entity\File as FileEntity;
use Enginewerk\FileManagementBundle\Entity\FileBlock;
use Enginewerk\FileManagementBundle\FileResponse\BinaryBlockCollection;
use Enginewerk\FileManagementBundle\Service\FileBlockReadService;
use Enginewerk\FileManagementBundle\Service\FileReadServiceInterface;
use Enginewerk\FSBundle\Service\BinaryStorageServiceInterface;

class FileBlockReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  FileReadServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileReadService;

    /** @var  BinaryStorageServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $binaryStorageService;

    public function setUp()
    {
        $this->fileReadService = $this->createMock(FileReadServiceInterface::class);
        $this->binaryStorageService = $this->createMock(BinaryStorageServiceInterface::class);
    }

    /**
     * @test
     */
    public function getFileBlockCollection()
    {
        $fileShortIdentifier = 'AopQzs2';
        $fileBlockReadService = new FileBlockReadService($this->fileReadService, $this->binaryStorageService);

        $fileEntityMock = $this->createMock(FileEntity::class);
        $fileEntityMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(888));

        $this->fileReadService
            ->expects($this->once())
            ->method('getByShortFileIdentifier')
            ->with($fileShortIdentifier)
            ->will($this->returnValue($fileEntityMock));

        $fileBlockEntity01Mock = $this->createMock(FileBlock::class);
        $fileBlockEntity01Mock->expects($this->once())
            ->method('getFileHash')
            ->will($this->returnValue('someFileHash01'));

        $fileBlockEntity02Mock = $this->createMock(FileBlock::class);
        $fileBlockEntity02Mock->expects($this->once())
            ->method('getFileHash')
            ->will($this->returnValue('someFileHash02'));

        $this->fileReadService
            ->expects($this->once())
            ->method('findBlocksByFileId')
            ->with(888)
            ->will($this->returnValue([
                $fileBlockEntity01Mock,
                $fileBlockEntity02Mock,
            ]));

        $this->binaryStorageService
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['someFileHash01'],
                ['someFileHash02']
            )
            ->willReturnOnConsecutiveCalls(
                'Block01',
                'Block02'
            );

        $this->assertEquals(
            new BinaryBlockCollection(['Block01', 'Block02']),
            $fileBlockReadService->getFileBlockCollection($fileShortIdentifier)
        );
    }
}
