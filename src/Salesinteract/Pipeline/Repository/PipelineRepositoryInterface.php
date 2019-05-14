<?php

namespace Salesinteract\Pipeline\Repository;

use Salesinteract\Pipeline\Entity\PipelineInterface;

interface PipelineRepositoryInterface
{
    /**
     * Add a pipeline
     *
     * @param PipelineInterface $pipeline
     * @return PipelineInterface
     */
    public function add(PipelineInterface $pipeline): PipelineInterface;

    /**
     * Find single pipeline based on criteria
     *
     * @param array $criteria
     * @return PipelineInterface
     */
    public function findOne(array $criteria);

    /**
     * Find all pipelines based on criteria
     *
     * @param array $criteria
     * @return array
     */
    public function find(array $criteria): array;
}
