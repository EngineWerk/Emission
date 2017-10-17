<?php
namespace Enginewerk\ResumableBundle\Tests\Service;

use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\Common\Uuid\UuidGeneratorInterface;
use Enginewerk\EmissionBundle\Presentation\Model\FileView;
use Enginewerk\EmissionBundle\Service\FileViewFinderInterface;
use Enginewerk\EmissionBundle\Storage\FileCreationInterface;
use Enginewerk\EmissionBundle\Storage\FileFinderInterface;
use Enginewerk\EmissionBundle\Storage\Model\File;
use Enginewerk\EmissionBundle\Storage\Model\FilePart;
use Enginewerk\EmissionBundle\Storage\Model\FilePartCollection;
use Enginewerk\FSBundle\Service\BinaryStorageInterface;
use Enginewerk\ResumableBundle\FileUpload\FileRequest;
use Enginewerk\ResumableBundle\FileUpload\Response\CompleteFileResponse;
use Enginewerk\ResumableBundle\FileUpload\ResponseFactoryInterface;
use Enginewerk\ResumableBundle\Service\ResumableFileUploadService;
use Enginewerk\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResumableFileUploadServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var FileFinderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileFinderMock;

    /** @var FileCreationInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileManagerMock;

    /** @var FileViewFinderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $fileViewFinderMock;

    /** @var ResponseFactoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $responseFactoryMock;

    /** @var BinaryStorageInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $binaryStorageServiceMock;

    /** @var UuidGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $identifierGeneratorMock;

    public function setUp()
    {
        $this->fileFinderMock = $this->createMock(FileFinderInterface::class);
        $this->fileManagerMock = $this->createMock(FileCreationInterface::class);
        $this->fileViewFinderMock = $this->createMock(FileViewFinderInterface::class);
        $this->responseFactoryMock = $this->createMock(ResponseFactoryInterface::class);
        $this->binaryStorageServiceMock = $this->createMock(BinaryStorageInterface::class);
        $this->identifierGeneratorMock = $this->createMock(UuidGeneratorInterface::class);
    }

    /**
     * @test
     */
    public function uploadFromRequestFinalPart()
    {
        $fileUploadService = new ResumableFileUploadService(
            $this->fileFinderMock,
            $this->fileManagerMock,
            $this->fileViewFinderMock,
            $this->responseFactoryMock,
            $this->binaryStorageServiceMock,
            $this->identifierGeneratorMock
        );

        $filePublicIdentifier = 'ayz';
        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 3072;
        $uploadChunkRangeStart = 2048;
        $uploadChunkRangeEnd = 3072;

        /** @var UploadedFile|\PHPUnit_Framework_MockObject_MockObject $uploadedFile */
        $uploadedFile = $this->createMock(UploadedFile::class);

        $resumableRequest = $this->createFileRequest(
            $fileName,
            $fileChecksum,
            $fileSize,
            $uploadChunkRangeStart,
            $uploadChunkRangeEnd);

        /** @var User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->createMock(User::class);

        $filePartCollection = new FilePartCollection();
        $file = new File($filePublicIdentifier, $fileName, $fileSize, 'pdf', $filePartCollection);

        $filePart1 = new FilePart('a', 0, 1024);
        $filePartCollection->add($filePart1);

        $filePart2 = new FilePart('b', 1024, 2048);
        $filePartCollection->add($filePart2);

        $this->fileFinderMock
            ->expects($this->once())
            ->method('findFile')
            ->with($fileName, $fileChecksum, $fileSize)
            ->willReturn($file);

        $this->fileManagerMock
            ->expects($this->never())
            ->method('createFile');

        $this->fileManagerMock
            ->expects($this->once())
            ->method('setFileAsComplete')
            ->with($filePublicIdentifier);

        $binaryIdentifier = 'uuid4';
        $this->identifierGeneratorMock
            ->expects($this->once())
            ->method('generate')
            ->willReturn($binaryIdentifier);

        $this->binaryStorageServiceMock
            ->expects($this->once())
            ->method('store')
            ->with($uploadedFile, $binaryIdentifier)
            ->willReturn($uploadChunkRangeEnd - $uploadChunkRangeStart);

        $this->fileManagerMock
            ->expects($this->once())
            ->method('createFilePart')
            ->with(
                $filePublicIdentifier,
                $binaryIdentifier,
                $uploadChunkRangeEnd - $uploadChunkRangeStart,
                $resumableRequest->getResumableCurrentStartByte(),
                $resumableRequest->getResumableCurrentEndByte()
            );

        $fileView = $this->createFileView($filePublicIdentifier, $fileChecksum, $fileName, $fileSize);

        $this->fileViewFinderMock
            ->expects($this->once())
            ->method('getByPublicIdentifier')
            ->with($filePublicIdentifier)
            ->willReturn($fileView);

        $completeFileMock = $this->createMock(CompleteFileResponse::class);
        $completeFileMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['foo' => 'bar']);

        $this->responseFactoryMock
            ->expects($this->once())
            ->method('createCompleteFileResponse')
            ->willReturn($completeFileMock);

        $this->responseFactoryMock
            ->expects($this->never())
            ->method('createIncompleteFileResponse');

        $this->assertEquals(
            new ServiceResponse(
                200,
                [
                    'response' => [
                        'status' => 'Success',
                        'message' => '',
                        'data' => [
                            'foo' => 'bar',
                        ],
                    ],
                ]
            ),
            $fileUploadService->uploadFromRequest($uploadedFile, $resumableRequest, $userMock)
        );
    }

    private function createFileView($filePublicIdentifier, $fileChecksum, $fileName, $fileSize)
    {
        return new FileView(
            1,
            $filePublicIdentifier,
            $fileChecksum,
            $fileName,
            'pdf',
            $fileSize,
            'size',
            $this->createMock(\DateTimeImmutable::class),
            $this->createMock(\DateTimeImmutable::class),
            $this->createMock(\DateTimeImmutable::class),
            true,
            'userName'
        );
    }

    private function createFileRequest($fileName, $fileChecksum, $fileSize, $uploadChunkRangeStart, $uploadChunkRangeEnd)
    {
        $resumableRequest = [];

        $resumableRequest['resumableChunkNumber'] = 3;
        $resumableRequest['resumableChunkSize'] = 1024;
        $resumableRequest['resumableCurrentChunkSize'] = 1024;
        $resumableRequest['resumableCurrentStartByte'] = $uploadChunkRangeStart;
        $resumableRequest['resumableCurrentEndByte'] = $uploadChunkRangeEnd;
        $resumableRequest['resumableTotalSize'] = $fileSize;
        $resumableRequest['resumableType'] = 'pdf';
        $resumableRequest['resumableIdentifier'] = $fileChecksum;
        $resumableRequest['resumableFilename'] = $fileName;
        $resumableRequest['resumableRelativePath'] = '/tmp';
        $resumableRequest['resumableTotalChunks'] = 3;

        return new FileRequest($resumableRequest);
    }

    /**
     * @test
     */
    public function findFileChunkWithExistingFileAndFileBlock()
    {
        $fileUploadService = new ResumableFileUploadService(
            $this->fileFinderMock,
            $this->fileManagerMock,
            $this->fileViewFinderMock,
            $this->responseFactoryMock,
            $this->binaryStorageServiceMock,
            $this->identifierGeneratorMock
        );

        $filePublicIdentifier = 'ayz';
        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 3074;
        $chunkRangeStart = 1025;
        $chunkRangeEnd = 2049;

        $filePartCollection = new FilePartCollection();
        $file = new File($filePublicIdentifier, $fileName, 3074, 'pdf', $filePartCollection);

        $filePart1 = new FilePart($filePublicIdentifier, 0, 1024);
        $filePartCollection->add($filePart1);

        $filePart2 = new FilePart($filePublicIdentifier, 1025, 2049);
        $filePartCollection->add($filePart2);

        $filePart3 = new FilePart($filePublicIdentifier, 2050, 3074);
        $filePartCollection->add($filePart3);

        $this->fileFinderMock->expects($this->once())
            ->method('findFile')
            ->with($fileName, $fileChecksum, $fileSize)
            ->willReturn($file);

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
            $this->fileFinderMock,
            $this->fileManagerMock,
            $this->fileViewFinderMock,
            $this->responseFactoryMock,
            $this->binaryStorageServiceMock,
            $this->identifierGeneratorMock
        );

        $filePublicIdentifier = 'ayz';
        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 4096;
        $chunkRangeStart = 3075;
        $chunkRangeEnd = 4096;

        $filePartCollection = new FilePartCollection();
        $file = new File($filePublicIdentifier, $fileName, 3074, 'pdf', $filePartCollection);

        $filePart1 = new FilePart($filePublicIdentifier, 0, 1024);
        $filePartCollection->add($filePart1);

        $filePart2 = new FilePart($filePublicIdentifier, 1025, 2049);
        $filePartCollection->add($filePart2);

        $filePart3 = new FilePart($filePublicIdentifier, 2050, 3074);
        $filePartCollection->add($filePart3);

        $this->fileFinderMock->expects($this->once())
            ->method('findFile')
            ->with($fileName, $fileChecksum, $fileSize)
            ->willReturn($file);

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
            $this->fileFinderMock,
            $this->fileManagerMock,
            $this->fileViewFinderMock,
            $this->responseFactoryMock,
            $this->binaryStorageServiceMock,
            $this->identifierGeneratorMock
        );

        $fileName = 'fileName-01';
        $fileChecksum = '08abc89a';
        $fileSize = 4096;
        $chunkRangeStart = 3075;
        $chunkRangeEnd = 4096;

        $this->fileFinderMock->expects($this->once())
            ->method('findFile')
            ->with($fileName, $fileChecksum, $fileSize)
            ->willReturn(null);

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
