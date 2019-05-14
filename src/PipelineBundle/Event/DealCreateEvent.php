<?php

namespace PipelineBundle\Event;

use Salesinteract\Pipeline\Entity\DealInterface;
use Symfony\Component\EventDispatcher\Event;

class DealCreateEvent extends Event
{
    /**
     * @var DealInterface
     */
    private $deal;

    /**
     * DealCreateEvent constructor.
     * @param DealInterface $deal
     */
    public function __construct(DealInterface $deal)
    {
        $this->deal = $deal;
    }

    /**
     * @return DealInterface
     */
    public function getDeal(): DealInterface
    {
        return $this->deal;
    }
}
