<?php
namespace Enginewerk\EmissionBundle\Service;

use DateTimeInterface;
use Enginewerk\ApplicationBundle\Logger\HasLoggerTrait;
use Enginewerk\ApplicationBundle\Response\ErrorResponse;
use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\ApplicationBundle\Response\SuccessResponse;
use Enginewerk\FileManagementBundle\Model\File as FileModel;
use Enginewerk\FileManagementBundle\Model\FileCollection;
use Enginewerk\FileManagementBundle\Service\FileReadServiceInterface;
use Enginewerk\FileManagementBundle\Storage\FileNotFoundException;
use Enginewerk\FileManagementBundle\Storage\FileStorage;
use Enginewerk\FileManagementBundle\Storage\InvalidFileIdentifierException;
use Enginewerk\FileManagementBundle\Storage\UserPermissionException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

final class FileService
{
    use HasLoggerTrait;

    /** @var  EngineInterface */
    protected $viewTemplate;

    /** @var  FileStorage */
    private $fileStorage;

    /** @var  FileReadServiceInterface */
    private $fileReadService;

    /**
     * @param EngineInterface $viewTemplate
     * @param FileStorage $fileStorage
     * @param FileReadServiceInterface $fileReadService
     */
    public function __construct(
        EngineInterface $viewTemplate,
        FileStorage $fileStorage,
        FileReadServiceInterface $fileReadService
    ) {
        $this->viewTemplate = $viewTemplate;
        $this->fileStorage = $fileStorage;
        $this->fileReadService = $fileReadService;
    }

    /**
     * @return FileCollection
     */
    public function findAllFiles()
    {
        return $this->fileReadService->findAllFiles();
    }

    /**
     * @param string $shortIdentifier
     *
     * @throws InvalidFileIdentifierException
     *
     * @return FileModel|null
     *
     */
    public function findByShortIdentifier($shortIdentifier)
    {
        return $this->fileReadService->findFileByShortIdentifier($shortIdentifier);
    }

    /**
     * @param string $shortFileIdentifier
     *
     * @return ServiceResponse
     */
    public function deleteFile($shortFileIdentifier)
    {
        try {
            $this->fileStorage->deleteFile($shortFileIdentifier);

            return new ServiceResponse(
                Response::HTTP_OK,
                (new SuccessResponse())->toArray()
            );
        } catch (\Exception $exception) {
            $this->getLogger()
                ->error(
                sprintf(
                    'Can`t delete File identified by shortFileIdentifier "%s". %s',
                    $shortFileIdentifier,
                    $exception->getMessage())
            );

            return new ServiceResponse(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                (new ErrorResponse(
                    ErrorResponse::DEFAULT_MESSAGE,
                    sprintf(
                        'Can`t delete File identified by shortFileIdentifier "%s"',
                        $shortFileIdentifier
                    )
                ))->toArray()
            );
        }
    }

    /**
     * @param string $replaceShortFileIdentifier
     * @param string $replacementShortFileIdentifier
     *
     * @return ServiceResponse
     */
    public function replace($replaceShortFileIdentifier, $replacementShortFileIdentifier)
    {
        try {
            $this->fileStorage->replace($replaceShortFileIdentifier, $replacementShortFileIdentifier);

            return new ServiceResponse(
                Response::HTTP_OK,
                (new SuccessResponse('File replaced'))->toArray()
            );
        } catch (UserPermissionException $userPermissionException) {
            $this->getLogger()->error($userPermissionException->getMessage());

            return new ServiceResponse(
                Response::HTTP_FORBIDDEN,
                (new ErrorResponse($userPermissionException->getMessage()))->toArray()
            );
        } catch (\Exception $exception) {
            $this->getLogger()->error($exception->getMessage());

            return new ServiceResponse(
                Response::HTTP_FORBIDDEN,
                (new ErrorResponse('Can\'t replace file'))->toArray()
            );
        }
    }

    /**
     * @param string $fileShortIdentifier
     * @param DateTimeInterface|null $expirationDate
     *
     * @return ServiceResponse
     */
    public function setFileExpirationDate($fileShortIdentifier, DateTimeInterface $expirationDate = null)
    {
        try {
            $this->fileStorage->setFileExpirationDate($fileShortIdentifier, $expirationDate);

            return new ServiceResponse(
                Response::HTTP_OK,
                (new SuccessResponse('OK'))->toArray()
            );
        } catch (InvalidFileIdentifierException $invalidIdentifierException) {
            $this->getLogger()->error($invalidIdentifierException->getMessage());

            return new ServiceResponse(
                Response::HTTP_BAD_REQUEST,
                (new ErrorResponse(sprintf(
                    'File identified by "%s" was not found.',
                    $fileShortIdentifier
                )))->toArray()
            );
        } catch (FileNotFoundException $fileNotFoundException) {
            $this->getLogger()->error(sprintf(
                'Can`t change expiration date of File #%s. %s',
                $fileShortIdentifier,
                $fileNotFoundException->getMessage()
            ));

            return new ServiceResponse(
                Response::HTTP_NOT_FOUND,
                (new ErrorResponse('Can`t change expiration date'))->toArray()
            );
        } catch (\Exception $exception) {
            $this->getLogger()->error($exception->getMessage());

            return new ServiceResponse(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                (new ErrorResponse('Can`t change expiration date'))->toArray()
            );
        }
    }

    /**
     * @param string|null $createdAfter
     *
     * @return ServiceResponse
     */
    public function getFilesForJsonApi($createdAfter = null)
    {
        return new ServiceResponse(
            Response::HTTP_OK,
            (new SuccessResponse(
                SuccessResponse::DEFAULT_MESSAGE,
                $this->fileReadService->getFilesForJsonApi(new \DateTime($createdAfter ?: 'now'))
            ))->toArray()
        );
    }
    /**
     * @param string $shortFileIdentifier
     *
     * @return ServiceResponse
     */
    public function showFileContent($shortFileIdentifier)
    {
        if (null === ($file = $this->fileStorage->findByShortIdentifier($shortFileIdentifier))) {
            return new ServiceResponse(
                Response::HTTP_NOT_FOUND,
                new ErrorResponse(sprintf('File #%s not found.', $shortFileIdentifier))
            );
        } else {
            return new ServiceResponse(
                Response::HTTP_OK,
                (new SuccessResponse(
                    SuccessResponse::DEFAULT_MESSAGE,
                    $this->viewTemplate->render(
                        'EnginewerkEmissionBundle:Default:showFileContent.html.twig',
                        ['File' => $file]
                    )
                ))->toArray()
            );
        }
    }
}
