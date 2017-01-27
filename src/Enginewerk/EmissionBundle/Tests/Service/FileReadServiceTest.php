<?php
namespace Enginewerk\EmissionBundle\Tests\Service;

use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\EmissionBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;
use Enginewerk\EmissionBundle\Service\FileReadService;
use Enginewerk\FileManagementBundle\Entity\File;
use Enginewerk\FileManagementBundle\Entity\FileBlock;

class FileReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  FileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileRepository;

    /** @var  FileBlockRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileBlockRepository;

    public function setUp()
    {
        $this->fileRepository = $this->getMock(FileRepositoryInterface::class);
        $this->fileBlockRepository = $this->getMock(FileBlockRepositoryInterface::class);
    }

    /**
     * @test
     */
    public function findFileChunkWithExistingFileAndFileBlock()
    {
        $fileUploadService = new FileReadService($this->fileRepository, $this->fileBlockRepository);

        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 1024;
        $chunkRangeStart = 0;
        $chunkRangeEnd = 512;

        $fileEntityMock = $this->getMock(File::class);
        $fileEntityMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(888));

        $this->fileRepository
            ->expects($this->once())
            ->method('findOneByNameAndChecksumAndSize')
            ->with($fileName, $fileChecksum, $fileSize)
            ->will($this->returnValue($fileEntityMock));

        $fileBlockEntity = new FileBlock();
        $this->fileBlockRepository
            ->expects($this->once())
            ->method('findByFileIdAndRangeStartAndRangeEnd')
            ->with(888, $chunkRangeStart, $chunkRangeEnd)
            ->will($this->returnValue($fileBlockEntity));

        $this->assertEquals(
            new ServiceResponse(
                200,
                [
                    'response' => [
                        'status' => 'Success',
                        'message' => 'Block found',
                        'data' => '',
                    ],
                ]
            ),
            $fileUploadService->findFileChunk($fileName, $fileChecksum, $fileSize, $chunkRangeStart, $chunkRangeEnd)
        );
    }

    /**
     * @test
     */
    public function findFileChunkWithExistingFileAndNotExistingFileBlock()
    {
        $fileUploadService = new FileReadService($this->fileRepository, $this->fileBlockRepository);

        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 1024;
        $chunkRangeStart = 0;
        $chunkRangeEnd = 512;

        $fileEntityMock = $this->getMock(File::class);
        $fileEntityMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(888));

        $this->fileRepository
            ->expects($this->once())
            ->method('findOneByNameAndChecksumAndSize')
            ->with($fileName, $fileChecksum, $fileSize)
            ->will($this->returnValue($fileEntityMock));

        $this->fileBlockRepository
            ->expects($this->once())
            ->method('findByFileIdAndRangeStartAndRangeEnd')
            ->with(888, $chunkRangeStart, $chunkRangeEnd)
            ->will($this->returnValue(null));

        $this->assertEquals(
            new ServiceResponse(
                306,
                [
                    'response' => [
                        'status' => 'Success',
                        'message' => 'Block not found',
                        'data' => '',
                    ],
                ]
            ),
            $fileUploadService->findFileChunk($fileName, $fileChecksum, $fileSize, $chunkRangeStart, $chunkRangeEnd)
        );
    }

    /**
     * @test
     */
    public function findFileChunkWithNotExistingFileAndNotExistingFileBlock()
    {
        $fileUploadService = new FileReadService($this->fileRepository, $this->fileBlockRepository);

        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 1024;
        $chunkRangeStart = 0;
        $chunkRangeEnd = 512;

        $this->fileRepository
            ->expects($this->once())
            ->method('findOneByNameAndChecksumAndSize')
            ->with($fileName, $fileChecksum, $fileSize)
            ->will($this->returnValue(null));

        $this->fileBlockRepository
            ->expects($this->never())
            ->method('findByFileIdAndRangeStartAndRangeEnd');

        $this->assertEquals(
            new ServiceResponse(
                306,
                [
                    'response' => [
                        'status' => 'Error',
                        'message' => 'File "fileName-01" not found',
                        'data' => '',
                    ],
                ]
            ),
            $fileUploadService->findFileChunk($fileName, $fileChecksum, $fileSize, $chunkRangeStart, $chunkRangeEnd)
        );
    }
}
