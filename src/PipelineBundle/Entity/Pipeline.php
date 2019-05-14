<?php

namespace PipelineBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Salesinteract\Pipeline\Entity\PipelineInterface;
use Salesinteract\Pipeline\Entity\StageInterface;

class Pipeline implements PipelineInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $organisationId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Collection
     */
    private $stages;

    /**
     * @var boolean
     */
    private $archived;

    /**
     * @var \DateTime
     */
    private $created;

    public function __construct()
    {
        $this->stages = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection
     */
    public function getStages(): Collection
    {
        return $this->stages ?? new ArrayCollection();
    }

    /**
     * @param StageInterface $stage
     */
    public function addStage(StageInterface $stage)
    {
        $this->stages->add($stage);
    }

    /**
     * @return void
     */
    public function clearStages()
    {
        $this->stages = new ArrayCollection();
    }

    /**
     * @param bool $archived
     */
    public function setArchived(bool $archived)
    {
        $this->archived = $archived;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived ?? false;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * Set created date on persist
     */
    public function lifecyclePreCreateDate()
    {
        $this->created = new \DateTime();
    }

    /**
     * Set created date on persist
     */
    public function lifecyclePreArchived()
    {
        $this->archived = false;
    }
}
