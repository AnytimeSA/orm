<?php

namespace DVE\EntityORM\EntityManager;

use DVE\EntityORM\QueryBuilder\QueryBuilderInterface;

abstract class Manager
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * EntityRepository constructor.
     * @param \PDO $pdo
     * @param EntityRepository $entityRepository
     */
    public function __construct(\PDO $pdo, EntityRepository $entityRepository)
    {
        $this->pdo = $pdo;
        $this->entityRepository = $entityRepository;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->entityRepository;
    }

    /**
     * @param string|null $alias
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder(string $alias = null): QueryBuilderInterface
    {
        return $this->entityRepository->createQueryBuilder($alias);
    }
}