<?php

namespace Salesinteract\Pipeline\Entity;

use Doctrine\Common\Collections\Collection;

interface PipelineInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return Collection
     */
    public function getStages(): Collection;

    /**
     * @return bool
     */
    public function isArchived(): bool;

    /**
     * @return string
     */
    public function getName(): string;
}
