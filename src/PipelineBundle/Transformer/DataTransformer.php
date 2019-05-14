<?php

namespace PipelineBundle\Transformer;

use Salesinteract\Pipeline\Entity\DealInterface;
use Symfony\Component\HttpFoundation\Request;

class DataTransformer
{
    /**
     * @param Request $request
     * @param DealInterface $deal
     * @return array
     */
    public static function transform(Request $request, DealInterface $deal): array
    {
        $data = $request->request->all();
        $data['ownerId'] = $deal->getOwnerId();
        $data['archived'] = $deal->isArchived();
        $data['created'] = $deal->getCreated()->format('Y-m-d H:i:s');
        $data['sortIndex'] = $deal->getSortIndex();

        return $data;
    }
}
