<?php

namespace Salesinteract\Pipeline\Repository;

use Salesinteract\Pipeline\Entity\DealInterface;

interface DealRepositoryInterface
{
    /**
     * Add a deal
     *
     * @param DealInterface $deal
     * @return DealInterface
     */
    public function add(DealInterface $deal): DealInterface;

    /**
     * Find single deal based on criteria
     *
     * @param array $criteria
     * @return DealInterface|null
     */
    public function findOne(array $criteria): ?DealInterface;

    /**
     * Find all deals based on criteria
     *
     * @param array $criteria
     * @return array
     */
    public function find(array $criteria): array;
}
