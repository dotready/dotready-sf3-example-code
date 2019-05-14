<?php

namespace PipelineBundle\Entity;

use Salesinteract\Pipeline\Entity\DealInterface;
use Salesinteract\Pipeline\Entity\PipelineInterface;
use Salesinteract\Pipeline\Entity\StageInterface;
use Salesinteract\User\Entity\UserInterface;

class Deal implements DealInterface
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
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $organisationId;

    /**
     * @var string
     */
    private $userProfileId;

    /**
     * @var PipelineInterface
     */
    private $pipeline;

    /**
     * @var StageInterface
     */
    private $stage;

    /**
     * @var \DateTime
     */
    private $expectedCloseDate;

    /**
     * @var \DateTime
     */
    private $closedDate;

    /**
     * @var string
     */
    private $closedStatus;

    /**
     * @var string
     */
    private $closedReason;

    /**
     * @var string
     */
    private $ownerId;

    /**
     * @var int
     */
    private $sortIndex;

    /**
     * @var bool
     */
    private $archived;

    /**
     * @var \DateTime
     */
    private $created;

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
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getOrganisationId(): string
    {
        return $this->organisationId;
    }

    /**
     * @return string
     */
    public function getUserProfileId(): string
    {
        return $this->userProfileId;
    }

    /**
     * @return PipelineInterface
     */
    public function getPipeline(): PipelineInterface
    {
        return $this->pipeline;
    }

    /**
     * @param PipelineInterface $pipeline
     */
    public function setPipeline(PipelineInterface $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * @return StageInterface
     */
    public function getStage(): StageInterface
    {
        return $this->stage;
    }

    /**
     * @param StageInterface $stage
     */
    public function setStage(StageInterface $stage)
    {
        $this->stage = $stage;
    }

    /**
     * Detach Stage
     *
     * @return void
     */
    public function removeStage()
    {
        $this->stage = null;
    }

    /**
     * @return \DateTime
     */
    public function getClosedDate(): \DateTime
    {
        return $this->closedDate;
    }

    /**
     * @param \DateTime $date
     */
    public function setClosedDate(\DateTime $date)
    {
        $this->closedDate = $date;
    }

    /**
     * @return string
     */
    public function getClosedStatus(): string
    {
        return $this->closedStatus ?? '';
    }

    /**
     * @param string $status
     */
    public function setClosedStatus(string $status)
    {
        $this->closedStatus = $status;
    }

    /**
     * @return string
     */
    public function getClosedReason(): string
    {
        return $this->closedReason ?? '';
    }

    /**
     * @param string $reason
     */
    public function setClosedReason(string $reason)
    {
        $this->closedReason = $reason;
    }

    /**
     * @return \DateTime
     */
    public function getExpectedCloseDate(): \DateTime
    {
        return $this->expectedCloseDate;
    }

    /**
     * @return string
     */
    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getSortIndex(): int
    {
        return $this->sortIndex ?? 0;
    }

    /**
     * @param int $sortIndex
     */
    public function setSortIndex(int $sortIndex)
    {
        $this->sortIndex = $sortIndex;
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
    public function setArchived(bool $archived)
    {
        $this->archived = $archived;
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