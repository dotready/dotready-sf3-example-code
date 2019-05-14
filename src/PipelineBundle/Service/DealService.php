<?php

namespace PipelineBundle\Service;

use PipelineBundle\Entity\Deal;
use Salesinteract\Exception\ValidationException;
use Salesinteract\Pipeline\Entity\DealInterface;
use Salesinteract\Pipeline\Repository\DealRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializerInterface;

class DealService
{
    /**
     * @var DealRepositoryInterface
     */
    private $repository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var PipelineService
     */
    private $pipelineService;

    /**
     * DealService constructor.
     * @param DealRepositoryInterface $repository
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param PipelineService $pipelineService
     */
    public function __construct(
        DealRepositoryInterface $repository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        PipelineService $pipelineService
    )
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->validator  = $validator;
        $this->pipelineService = $pipelineService;
    }

    /**
     * @param array $data
     *
     * @return DealInterface
     *
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(array $data): DealInterface
    {
        if (!isset($data['pipelineId']) || empty($data['pipelineId'])) {
            throw new ValidationException((new ConstraintViolationList([
                new ConstraintViolation('Pipeline can not be empty', '', [], '', 'pipeline', '')
            ])));
        }

        if (!isset($data['stageId']) || empty($data['stageId'])) {
            throw new ValidationException((new ConstraintViolationList([
                new ConstraintViolation('Pipeline can not be empty', '', [], '', 'pipeline', '')
            ])));
        }

        $pipeline = $this->pipelineService->findOne(['id' => $data['pipelineId']]);
        $stageId = [$data['stageId']];

        $stage = $pipeline->getStages()->filter(function($entry) use ($stageId) {
            return in_array($entry->getId(), $stageId);
        });

        $stage = $stage->first();

        if (empty($data['sortIndex'])) {
            $data['sortIndex'] = count($this->find(['stage' => $stage, 'archived' => false])) + 1;
        }

        $deal = $this->serializer->deserialize(json_encode($data), Deal::class, 'json');
        $deal->setStage($stage);
        $deal->setPipeline($pipeline);

        $errors = $this->validator->validate($deal);

        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        return $deal;
    }

    /**
     * @param DealInterface $deal
     *
     * @return DealInterface
     */
    public function add(DealInterface $deal) : DealInterface
    {
        $this->repository->add($deal);

        $stage = $deal->getStage();
        $stage->addDeal($deal);

        $this->repository->getEntityManager()->merge($stage);
        $this->repository->getEntityManager()->flush();

        return $deal;
    }

    /**
     * @param DealInterface $deal
     *
     * @return DealInterface
     */
    public function save(DealInterface $deal) : DealInterface
    {
        $this->repository->save($deal);
        return $deal;
    }

    /**
     * @param array $criteria
     *
     * @return DealInterface
     */
    public function findOne(array $criteria) : DealInterface
    {
        return $this->repository->findOne($criteria);
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param null $limit
     *
     * @return array
     */
    public function find(array $criteria, array $sort = array(), $limit = null) : array
    {
        return $this->repository->find($criteria, $sort, $limit);
    }

    /**
     * @param DealInterface $deal
     *
     * @return void
     */
    public function remove(DealInterface $deal): void
    {
        $this->repository->remove($deal);
    }

    /**
     * @param string $userId
     * @return int
     */
    public function getStatDeals(string $userId): int
    {
        return $this->repository->getStatDeals($userId);
    }

    /**
     * @param string $userId
     * @return int
     */
    public function getStatValue(string $userId): int
    {
        return $this->repository->getStatValue($userId);
    }

    /**
     * @param string $userId
     * @return int
     */
    public function getStatPipelines(string $userId): int
    {
        return $this->repository->getStatPipelines($userId);
    }

    /**
     * @param string $userId
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string $closedStatus
     * @return array
     */
    public function getUserDealCounts(string $userId, \DateTime $from, \DateTime $to, string $closedStatus = ''): array
    {
        return $this->repository->getUserDealCounts($userId, $from, $to, $closedStatus);
    }

    /**
     * @param DealInterface $deal
     * @param int $position
     */
    public function updateSortIndices(DealInterface $deal, int $position): void
    {
        $this->repository->updateSortIndices($deal, $position);
    }

    /**
     * @param string $profileId
     * @return array
     */
    public function getUserProfileStats(string $profileId): array
    {
        $data = [
            'pending' => 0,
            'won' => 0,
            'lost' => 0
        ];

        $results = $this->repository->getUserProfileStats($profileId);

        foreach ($results as $row) {
            $status = $row['closed_status'] === null ? 'pending' : $row['closed_status'];
            $data[$status] = ['value' => (int) $row['value'], 'currency' => $row['currency']];
        }

        return $data;
    }

    /**
     * @param array $criteria
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string $status
     *
     * @return array
     */
    public function getDealRecords(array $criteria, \DateTime $from, \DateTime $to, string $status): array
    {
        $data = [];
        $records = $this->repository->getDealRecords($criteria, $from, $to, $status);

        foreach ($records as $record) {
            $data[]  = $this->findOne(['id' => $record['id']]);
        }

        return $data;
    }

    /**
     * @param string $organisationId
     *
     * @return array
     */
    public function getDealTotalsByOrganisation(string $organisationId): array
    {
        $stats = [
            'lost' => 0,
            'won' => 0,
            'pending' => 0
        ];

        $data = $this->repository->getDealTotalsByOrganisation($organisationId);

        foreach ($data as $row) {
            $stats[$row['closed_status']] = $row['total'];
        }

        return $stats;
    }
}
