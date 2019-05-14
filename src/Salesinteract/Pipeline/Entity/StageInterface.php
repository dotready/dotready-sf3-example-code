<?php

namespace Salesinteract\Pipeline\Entity;

interface StageInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getProbability(): int;

    /**
     * @return int
     */
    public function getIndex(): int;

    /**
     * @return PipelineInterface
     */
    public function getPipeline(): PipelineInterface;

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime;
}
