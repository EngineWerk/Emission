<?php
namespace Enginewerk\ResumableBundle\Tests\Service;

use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\EmissionBundle\Service\FileViewServiceInterface;
use Enginewerk\FileManagementBundle\Entity\File;
use Enginewerk\FileManagementBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\FileManagementBundle\Repository\FileRepositoryInterface;
use Enginewerk\FileManagementBundle\Service\FileReadServiceInterface;
use Enginewerk\FileManagementBundle\Service\FileWriteServiceInterface;
use Enginewerk\FSBundle\Service\BinaryStorageServiceInterface;
use Enginewerk\ResumableBundle\Service\ResumableFileUploadService;

class ResumableFileUploadServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  FileRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileRepository;

    /** @var  FileBlockRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileBlockRepository;

    /** @var  FileWriteServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileWriteService;

    /** @var  FileReadServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileReadService;

    /** @var  BinaryStorageServiceInterface */
    private $binaryStorageService;

    /** @var  FileViewServiceInterface */
    private $fileViewService;

    public function setUp()
    {
        $this->fileRepository = $this->createMock(FileRepositoryInterface::class);
        $this->fileBlockRepository = $this->createMock(FileBlockRepositoryInterface::class);
        $this->fileWriteService = $this->createMock(FileWriteServiceInterface::class);
        $this->fileReadService = $this->createMock(FileReadServiceInterface::class);
        $this->binaryStorageService = $this->createMock(BinaryStorageServiceInterface::class);
        $this->fileViewService = $this->createMock(FileViewServiceInterface::class);
    }

    /**
     * @test
     */
    public function findFileChunkWithExistingFileAndFileBlock()
    {
        $fileUploadService = new ResumableFileUploadService(
            $this->binaryStorageService,
            $this->fileViewService,
            $this->fileWriteService,
            $this->fileReadService
        );

        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 1024;
        $chunkRangeStart = 0;
        $chunkRangeEnd = 512;

        $fileEntityMock = $this->createMock(File::class);
        $fileEntityMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(888));

        $this->fileReadService
            ->expects($this->once())
            ->method('findOneByNameAndChecksumAndSize')
            ->with($fileName, $fileChecksum, $fileSize)
            ->will($this->returnValue($fileEntityMock));

        $this->fileReadService
            ->expects($this->once())
            ->method('hasFileFileBlock')
            ->with(888, $chunkRangeStart, $chunkRangeEnd)
            ->will($this->returnValue(true));

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
        $fileUploadService = new ResumableFileUploadService(
            $this->binaryStorageService,
            $this->fileViewService,
            $this->fileWriteService,
            $this->fileReadService
        );

        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 1024;
        $chunkRangeStart = 0;
        $chunkRangeEnd = 512;

        $fileEntityMock = $this->createMock(File::class);
        $fileEntityMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(888));

        $this->fileReadService
            ->expects($this->once())
            ->method('findOneByNameAndChecksumAndSize')
            ->with($fileName, $fileChecksum, $fileSize)
            ->will($this->returnValue($fileEntityMock));

        $this->fileReadService
            ->expects($this->once())
            ->method('hasFileFileBlock')
            ->with(888, $chunkRangeStart, $chunkRangeEnd)
            ->will($this->returnValue(false));

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
        $fileUploadService = new ResumableFileUploadService(
            $this->binaryStorageService,
            $this->fileViewService,
            $this->fileWriteService,
            $this->fileReadService
        );

        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 1024;
        $chunkRangeStart = 0;
        $chunkRangeEnd = 512;

        $this->fileReadService
            ->expects($this->once())
            ->method('findOneByNameAndChecksumAndSize')
            ->with($fileName, $fileChecksum, $fileSize)
            ->will($this->returnValue(null));

        $this->fileReadService
            ->expects($this->never())
            ->method('hasFileFileBlock');

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
