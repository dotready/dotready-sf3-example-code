<?php

namespace PipelineBundle\Repository;

use Doctrine\ORM\EntityManager;
use PipelineBundle\Entity\Deal;
use Salesinteract\Pipeline\Entity\DealInterface;
use Salesinteract\Pipeline\Repository\DealRepositoryInterface;

class DealRepository implements DealRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * FieldRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param DealInterface $deal
     * @return DealInterface
     */
    public function add(DealInterface $deal): DealInterface
    {
        $this->entityManager->persist($deal);
        $this->entityManager->flush();
        return $deal;
    }

    /**
     * @param DealInterface $deal
     * @return DealInterface
     */
    public function save(DealInterface $deal): DealInterface
    {
        $this->entityManager->merge($deal);
        $this->entityManager->flush();
        return $deal;
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function find(array $criteria, array $sort = [], int $limit = null, int $offset = null): array
    {
        $repository = $this->entityManager->getRepository(Deal::class);
        $deals = $repository->findBy($criteria, $sort, $limit, $offset);

        return $deals;
    }

    /**
     * @param array $criteria
     * @param array $sort
     *
     * @return null|DealInterface
     */
    public function findOne(array $criteria, array $sort = []): ?DealInterface
    {
        $repository = $this->entityManager->getRepository(Deal::class);
        return $repository->findOneBy($criteria, $sort);
    }

    /**
     * @param string $userId
     * @return int
     */
    public function getStatDeals(string $userId): int
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('COUNT(id) as count')
            ->from('pipeline_deal')
            ->where('owner_id = ?')
            ->andWhere('closed_status IS NULL');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $row['count'] !== null ? $row['count'] : 0;
    }

    /**
     * @param string $userId
     * @return int
     */
    public function getStatValue(string $userId): int
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('SUM(value) as total')
            ->from('pipeline_deal')
            ->where('owner_id = ?')
            ->andWhere('closed_status IS NULL');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $row['total'] !== null ? $row['total'] : 0;
    }

    /**
     * @param string $userId
     * @return int
     */
    public function getStatPipelines(string $userId): int
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('count(distinct(pipeline_id)) as count')
            ->from('pipeline_deal')
            ->where('owner_id = ?')
            ->andWhere('closed_status IS NULL');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $row['count'] !== null ? $row['count'] : 0;
    }

    /**
     * @param string $organisationId
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public function getDealValues(string $organisationId, \DateTime $from, \DateTime $to): array
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('SUM(value) as value, owner_id,pipeline_id, pd.created, closed_status')
            ->from('pipeline_deal', 'pd')
            ->innerJoin('pd', 'organisation', 'o', 'pd.organisation_id = o.id AND o.parent_id = ?')
            ->groupBy('pipeline_id, owner_id, closed_status, pd.created')
            ->where('stage_id is not null')
            ->andWhere('DATE(pd.created) BETWEEN ? AND ?');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$organisationId, $from->format('Y-m-d'), $to->format('Y-m-d')]);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * @param string $organisationId
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public function getDealCounts(string $organisationId, \DateTime $from, \DateTime $to): array
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('COUNT(pd.id) as count, owner_id, pipeline_id, closed_status')
            ->from('pipeline_deal', 'pd')
            ->innerJoin('pd', 'organisation', 'o', 'pd.organisation_id = o.id AND o.parent_id = ?')
            ->groupBy('pipeline_id, owner_id, closed_status')
            ->where('stage_id is not null')
            ->andWhere('pd.created BETWEEN ? AND ?');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$organisationId, $from->format('Y-m-d'), $to->format('Y-m-d')]);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
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
        $params = [$userId, $from->format('Y-m-d'), $to->format('Y-m-d')];
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('COUNT(pd.id) as count, MONTH(created) AS month')
            ->from('pipeline_deal', 'pd')
            ->groupBy('MONTH(created)')
            ->where('owner_id = ?')
            ->andWhere('pd.created BETWEEN ? AND ?');

        if (!empty($closedStatus)) {
            if ($closedStatus === 'pending') {
                $qb->andWhere('closed_status IS NULL');
            } else {
                $qb->andWhere('closed_status = ?');
                $params[] = $closedStatus;
            }
        }

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * @param string $organisationId
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public function getStageValues(string $organisationId, \DateTime $from, \DateTime $to): array
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('SUM(pd.value) AS value, pipeline_id, stage_id, owner_id, closed_status')
            ->from('pipeline_deal', 'pd')
            ->innerJoin('pd', 'organisation', 'o', 'pd.organisation_id = o.id AND o.parent_id = ?')
            ->groupBy('pipeline_id, stage_id , owner_id, closed_status')
            ->where('stage_id is not null')
            ->andWhere('pd.created BETWEEN ? AND ?');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$organisationId, $from->format('Y-m-d'), $to->format('Y-m-d')]);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * @param string $organisationId
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public function getStageCounts(string $organisationId, \DateTime $from, \DateTime $to): array
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('COUNT(pd.id) AS count, pipeline_id, stage_id, owner_id, closed_status')
            ->from('pipeline_deal', 'pd')
            ->innerJoin('pd', 'organisation', 'o', 'pd.organisation_id = o.id AND o.parent_id = ?')
            ->groupBy('pipeline_id, stage_id , owner_id, closed_status')
            ->where('stage_id is not null')
            ->andWhere('pd.created BETWEEN ? AND ?');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$organisationId, $from->format('Y-m-d'), $to->format('Y-m-d')]);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * @param DealInterface $deal
     * @param int $position
     */
    public function updateSortIndices(DealInterface $deal, int $position): void
    {
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare("
            SET @tempVariable := ?;
            UPDATE pipeline_deal SET sort_index = (@tempVariable:=@tempVariable+1)
            WHERE stage_id = ?
            ORDER BY sort_index ASC
        ");

        $stmt->execute([0, $deal->getStage()->getId()]);
        $stmt->closeCursor();


        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare("
            SET @tempVariable := ?;
            UPDATE pipeline_deal SET sort_index = (@tempVariable:=@tempVariable+1)
            WHERE stage_id = ? AND sort_index >= ?
        ");

        $stmt->execute([$position , $deal->getStage()->getId(), $position]);
        $stmt->closeCursor();
    }

    /**
     * @param string $profileId
     * @return array
     */
    public function getUserProfileStats(string $profileId): array
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('SUM(value) AS value, currency, closed_status')
            ->from('pipeline_deal', 'pd')
            ->innerJoin('pd', 'pipeline_stage', 'ps', 'ps.id = pd.stage_id and ps.archived = 0')
            ->where('user_profile_id = ?')
            ->groupBy('closed_status, currency');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$profileId]);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * @param string $organisationId
     * @return array
     */
    public function getDealTotalsByOrganisation(string $organisationId): array
    {
        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('SUM(value) AS total, IF (closed_status is null , "pending", closed_status) AS closed_status')
            ->from('pipeline_deal', 'pd')
            ->innerJoin('pd', 'organisation', 'o', 'pd.organisation_id = o.id AND o.parent_id = ? AND o.archived = 0')
            ->innerJoin('pd', 'pipeline_stage', 'ps', 'ps.id = pd.stage_id and ps.archived = 0')
            ->groupBy('closed_status');

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute([$organisationId]);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * @param array $criteria
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string $closedStatus
     *
     * @return array
     */
    public function getDealRecords(array $criteria, \DateTime $from, \DateTime $to, string $closedStatus): array
    {
        $params = [
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        ];

        $conn = $this->entityManager->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb->select('pd.id')
            ->from('pipeline_deal', 'pd')
            ->innerJoin('pd', 'pipeline_stage', 'ps', 'ps.id = pd.stage_id and ps.archived = 0')
            ->innerJoin('pd', 'organisation', 'o', 'o.id = pd.organisation_id AND o.archived = 0')
            ->innerJoin('pd', 'user_profile', 'up', 'up.id = pd.user_profile_id AND up.archived = 0')
            ->where('DATE(pd.created) >= ?')
            ->andWhere('DATE(pd.created) <= ?');

        if (!empty($closedStatus)) {
            if ($closedStatus === 'pending') {
                $qb->andWhere('closed_status IS NULL');
            } else {
                $qb->andWhere('closed_status = ?');
                $params[] = $closedStatus;
            }
        }

        foreach ($criteria as $key => $value) {
            $qb->andWhere('pd.'.$key . ' = ?');
            $params[] = $value;
        }

        $stmt = $conn->prepare($qb->getSQL());
        $stmt->execute($params);
        $result = $stmt->fetchAll(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $stmt->closeCursor();

        return $result;
    }

    /**
     * @param DealInterface $deal
     */
    public function remove(DealInterface $deal)
    {
        $this->entityManager->remove($deal);
        $this->entityManager->flush();
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
