<?php
namespace Enginewerk\StatBundle\Presentation\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Enginewerk\StatBundle\Presentation\FileCountInterface;
use Enginewerk\StatBundle\Presentation\FileSizeInterface;

class FileReader implements FileSizeInterface, FileCountInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function getFilesCount()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(f.id)')
            ->from('EnginewerkEmissionBundle:File', 'f');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getFilesSize()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('SUM(f.size)')
            ->from('EnginewerkEmissionBundle:File', 'f');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getFilesSizeReal()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('SUM(b.size)')
            ->from('EnginewerkFSBundle:BinaryBlock', 'b');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getFileTypesCount()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(f.id) AS number, f.type')
            ->from('EnginewerkEmissionBundle:File', 'f')
            ->groupBy('f.type')
            ->orderBy('number', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
}
