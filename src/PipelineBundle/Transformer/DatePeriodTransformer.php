<?php

namespace PipelineBundle\Transformer;

use Salesinteract\Pipeline\Entity\DealInterface;
use Symfony\Component\HttpFoundation\Request;

class DatePeriodTransformer
{
    public function __construct()
    {
    }

    /**
     * @param string $period
     * @return __anonymous@1302
     */
    public static function transform(string $period)
    {

        switch ($period) {
            case 'first':
                $from = (new \DateTime())->modify('first day of january');
                $to = (new \DateTime())->modify('last day of march');
                break;
            case 'second':
                $from = (new \DateTime())->modify('first day of april');
                $to = (new \DateTime())->modify('last day of june');
                break;
            case 'third':
                $from = (new \DateTime())->modify('first day of juli');
                $to = (new \DateTime())->modify('last day of september');
                break;
            case 'fourth':
                $from = (new \DateTime())->modify('first day of october');
                $to = (new \DateTime())->modify('last day of december');
                break;
            default:
                $from = (new \DateTime())->modify('first day of january');
                $to = (new \DateTime())->modify('last day of december');
        }

        return new Class($from, $to) {
            public $from;

            public $to;

            public function __construct($from, $to)
            {
                $this->from = $from;
                $this->to = $to;
            }
        };
    }
}
