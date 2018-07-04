<?php

namespace DVE\EntityORM\EntityManager;

use DVE\EntityORM\QueryBuilder\QueryBuilderAbstract;
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
     * @param string $queryType
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder($alias = null, string $queryType = QueryBuilderAbstract::QUERY_TYPE_SELECT): QueryBuilderInterface
    {
        return $this->entityRepository->createQueryBuilder($alias, $queryType)
    }

    /**
     * @param string|null $alias
     * @return QueryBuilderInterface
     */
    public function createDeleteQueryBuilder($alias = null)
    {
        return $this->entityRepository->createDeleteQueryBuilder($alias);
    }

    /**
     * @param mixed $primaryKeyValues,... If primary key is a composite you need to pass as many parameters as required
     * @return Entity|null
     */
    public function findByPrimaryKey(...$primaryKeyValues)
    {
        $entityClassName = $this->getRepository()->getClassName();
        $queryBuilder = $this->createQueryBuilder();
        $where = $queryBuilder->getFindByPrimaryKeySQLWhere($entityClassName::PRIMARY_KEYS);

        $queryBuilder
            ->setParameters($primaryKeyValues)
            ->where($where)
        ;

        return $queryBuilder->getSelectQuery()->fetchOne();
    }
}