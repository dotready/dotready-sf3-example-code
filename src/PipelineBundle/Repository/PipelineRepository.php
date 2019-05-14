<?php

namespace PipelineBundle\Repository;

use Doctrine\ORM\EntityManager;
use PipelineBundle\Entity\Pipeline;
use Salesinteract\Pipeline\Entity\PipelineInterface;
use Salesinteract\Pipeline\Repository\PipelineRepositoryInterface;

class PipelineRepository implements PipelineRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * FieldRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param PipelineInterface $pipeline
     * @return PipelineInterface
     */
    public function add(PipelineInterface $pipeline): PipelineInterface
    {
        $this->entityManager->persist($pipeline);
        $this->entityManager->flush();
        return $pipeline;
    }

    /**
     * @param PipelineInterface $pipeline
     * @return PipelineInterface
     */
    public function save(PipelineInterface $pipeline): PipelineInterface
    {
        $this->entityManager->merge($pipeline);
        $this->entityManager->flush();
        return $pipeline;
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function find(array $criteria, array $sort = [], int $limit = null, int $offset = null): array
    {
        $repository = $this->entityManager->getRepository(Pipeline::class);
        $fields = $repository->findBy($criteria, $sort, $limit, $offset);

        return $fields;
    }

    /**
     * @param array $criteria
     * @param array $sort
     *
     * @return null|PipelineInterface
     */
    public function findOne(array $criteria, array $sort = []): ?PipelineInterface
    {
        $repository = $this->entityManager->getRepository(Pipeline::class);
        return $repository->findOneBy($criteria, $sort);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
