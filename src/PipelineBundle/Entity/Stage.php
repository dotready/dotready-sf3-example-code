<?php

namespace PipelineBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Salesinteract\Pipeline\Entity\DealInterface;
use Salesinteract\Pipeline\Entity\PipelineInterface;
use Salesinteract\Pipeline\Entity\StageInterface;

class Stage implements StageInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $probability;

    /**
     * @var integer
     */
    private $index;

    /**
     * @var PipelineInterface
     */
    private $pipeline;

    /**
     * @var Collection
     */
    private $deals;

    /**
     * @var boolean
     */
    private $archived;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * Stage constructor.
     */
    public function __construct()
    {
        $this->deals = new ArrayCollection();
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
     * @return int
     */
    public function getProbability(): int
    {
        return $this->probability ?? 0;
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index ?? 0;
    }

    /**
     * @param int $index
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    /**
     * @return PipelineInterface
     */
    public function getPipeline(): PipelineInterface
    {
        return $this->pipeline;
    }

    /**
     * @param DealInterface $deal
     */
    public function addDeal(DealInterface $deal)
    {
        if (!$this->deals->contains($deal)) {
            $this->deals->add($deal);
        }
    }

    /**
     * @param DealInterface $deal
     */
    public function removeDeal(DealInterface $deal)
    {
        $exists = $this->deals->exists(function($key, $element) use ($deal) {
            return ($element->getId() === $deal->getId());
        });

        if ($exists) {
            $this->deals->removeElement($deal);
        }
    }

    /**
     * @return Collection
     */
    public function getDeals(): Collection
    {
        return $this->deals ?? new ArrayCollection();
    }

    /**
     * @param PipelineInterface $pipeline
     */
    public function setPipeline($pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived ?? false;
    }

    /**
     * @param bool $archived
     */
    public function setArchived(bool $archived): void
    {
        $this->archived = $archived;
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
