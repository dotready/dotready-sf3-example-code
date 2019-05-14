<?php

namespace Salesinteract\Pipeline\Entity;

use Salesinteract\Organisation\Entity\OrganisationInterface;
use Salesinteract\User\Entity\UserInterface;

interface DealInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * Name of the deal
     * @return string
     */
    public function getName(): string;

    /**
     * Amount in 0.00 format
     * @return float
     */
    public function getValue(): float;

    /**
     * Currency EUR, USD, etc
     * @return string
     */
    public function getCurrency(): string;

    /**
     * (Child) Organisation the deal is made with
     * @return string
     */
    public function getOrganisationId(): string;

    /**
     * Contact user id
     * @return string
     */
    public function getUserProfileId(): string;

    /**
     * @return PipelineInterface
     */
    public function getPipeline(): PipelineInterface;

    /**
     * @return StageInterface
     */
    public function getStage(): StageInterface;

    /**
     * When does the sales expect this to close the deal
     * @return \DateTime
     */
    public function getExpectedCloseDate(): \DateTime;

    /**
     * Actual closed date
     * @return \DateTime
     */
    public function getClosedDate(): \DateTime;

    /**
     * @return string
     */
    public function getClosedStatus(): string;

    /**
     * @return string
     */
    public function getClosedReason(): string;

    /**
     * Get sorted index within stage
     * @return int
     */
    public function getSortIndex(): int;

    /**
     * Sales user id
     * @return string
     */
    public function getOwnerId(): string;

    /**
     * @return bool
     */
    public function isArchived(): bool;
}
