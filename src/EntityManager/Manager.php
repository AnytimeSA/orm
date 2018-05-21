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

    /**
     * @param mixed $primaryKeyValues,... If primary key is a composite you need to pass as many parameters as required
     * @return Entity|null
     * TODO THis should ne moved in the query builder concrete class + interface
     */
    public function findByPrimaryKey(...$primaryKeyValues)
    {
        $entityClassName = $this->getRepository()->getClassName();
        $primaryKeys = $entityClassName::PRIMARY_KEYS;
        $tableName = $this->getRepository()->getTableName();
        $where = '';

        foreach($primaryKeys as $primaryKey) {
            $where .= ($where ? ' AND ' : ''). '`' . $tableName.'`.`'.$primaryKey . '` = ?';
        }

        $queryBuilder = $this->createQueryBuilder()
            ->setParameters($primaryKeyValues)
            ->where($where)
        ;

        return $queryBuilder->getSelectQuery()->fetchOne();
    }
}