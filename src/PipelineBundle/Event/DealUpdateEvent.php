<?php

namespace PipelineBundle\Event;

use Salesinteract\Pipeline\Entity\DealInterface;
use Symfony\Component\EventDispatcher\Event;

class DealUpdateEvent extends Event
{
    /**
     * @var DealInterface
     */
    private $deal;

    /**
     * @var string
     */
    private $action;

    /**
     * DealCreateEvent constructor.
     * @param DealInterface $deal
     */
    public function __construct(DealInterface $deal, string $action)
    {
        $this->deal = $deal;
        $this->action = $action;
    }

    /**
     * @return DealInterface
     */
    public function getDeal(): DealInterface
    {
        return $this->deal;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }
}
