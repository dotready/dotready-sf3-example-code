<?php

namespace PipelineBundle\Service;

use PipelineBundle\Entity\Pipeline;
use Salesinteract\Exception\ValidationException;
use Salesinteract\Pipeline\Entity\PipelineInterface;
use Salesinteract\Pipeline\Repository\PipelineRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializerInterface;

class PipelineService
{
    /**
     * @var PipelineRepositoryInterface
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
     * PipelineService constructor.
     * @param PipelineRepositoryInterface $repository
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        PipelineRepositoryInterface $repository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    )
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->validator  = $validator;
    }

    /**
     * @param array $data
     *
     * @return PipelineInterface
     *
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(array $data): PipelineInterface
    {
        $pipeline  = $this->serializer->deserialize(json_encode($data), Pipeline::class, 'json');
        $errors = $this->validator->validate($pipeline);

        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        return $pipeline;
    }

    /**
     * @param PipelineInterface $pipeline
     *
     * @return PipelineInterface
     */
    public function add(PipelineInterface $pipeline) : PipelineInterface
    {
        $stages = $pipeline->getStages();
        $pipeline->clearStages();

        $this->repository->add($pipeline);

        foreach ($stages as $stage) {
            $stage->setPipeline($pipeline);
            $pipeline->addStage($stage);
            $this->repository->getEntityManager()->persist($stage);

        }

        $this->repository->getEntityManager()->flush();
        return $pipeline;
    }

    /**
     * @param PipelineInterface $pipeline
     *
     * @return PipelineInterface
     */
    public function save(PipelineInterface $pipeline) : PipelineInterface
    {
        $stages = $pipeline->getStages();
        $pipeline->clearStages();

        $this->repository->save($pipeline);

        foreach ($stages as $stage) {
            $stage->setPipeline($pipeline);
            $pipeline->addStage($stage);
            $this->repository->getEntityManager()->merge($stage);
        }

        $this->repository->getEntityManager()->flush();
        return $pipeline;
    }

    /**
     * @param array $criteria
     *
     * @throws NotFoundHttpException
     *
     * @return PipelineInterface
     */
    public function findOne(array $criteria) : PipelineInterface
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
}
