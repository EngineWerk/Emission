<?php
namespace Enginewerk\ResumableBundle\FileUpload\Response;

class CompleteFileResponse extends IncompleteFileResponse
{
    /** @var \DateTimeInterface */
    private $expirationDate;

    /** @var \DateTimeInterface */
    private $updatedAt;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var string */
    private $uploadedBy;

    /** @var string */
    private $showUrl;

    /** @var string */
    private $downloadUrl;

    /** @var string */
    private $openUrl;

    /** @var string */
    private $deleteUrl;

    /**
     * @param int $id
     * @param string $publicIdentifier
     * @param string $name
     * @param string $type
     * @param int $size
     * @param \DateTimeInterface $expirationDate
     * @param \DateTimeInterface $updatedAt
     * @param \DateTimeInterface $createdAt
     * @param string $uploadedBy
     * @param string $showUrl
     * @param string $downloadUrl
     * @param string $openUrl
     * @param string $deleteUrl
     */
    public function __construct(
        $id,
        $publicIdentifier,
        $name,
        $type,
        $size,
        \DateTimeInterface $expirationDate,
        \DateTimeInterface $updatedAt,
        \DateTimeInterface $createdAt,
        $uploadedBy,
        $showUrl,
        $downloadUrl,
        $openUrl,
        $deleteUrl
    ) {
        parent::__construct($id, $publicIdentifier, $name, $type, $size);

        $this->expirationDate = $expirationDate;
        $this->updatedAt = $updatedAt;
        $this->createdAt = $createdAt;
        $this->uploadedBy = $uploadedBy;
        $this->showUrl = $showUrl;
        $this->downloadUrl = $downloadUrl;
        $this->openUrl = $openUrl;
        $this->deleteUrl = $deleteUrl;
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'expiration_date' => $this->expirationDate->format('Y-m-d H:i:s'),
                'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
                'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
                'uploaded_by' => $this->uploadedBy,
                'show_url' => $this->showUrl,
                'download_url' => $this->downloadUrl,
                'open_url' => $this->openUrl,
                'delete_url' => $this->deleteUrl,
            ]
        );
    }
}
