<?php

namespace Anytime\ORM\EntityManager;

use Anytime\ORM\QueryBuilder\QueryAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderInterface;
use Anytime\ORM\QueryBuilder\SelectQuery;

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
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * EntityRepository constructor.
     * @param \PDO $pdo
     * @param EntityRepository $entityRepository
     * @param EntityManager $entityManager
     */
    public function __construct(\PDO $pdo, EntityRepository $entityRepository, EntityManager $entityManager)
    {
        $this->pdo = $pdo;
        $this->entityRepository = $entityRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->entityRepository;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param string|null $alias
     * @param string $queryType
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder($alias = null, string $queryType = QueryBuilderAbstract::QUERY_TYPE_SELECT): QueryBuilderInterface
    {
        return $this->entityRepository->createQueryBuilder($alias, $queryType);
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
     * @param string|null $alias
     * @return QueryBuilderInterface
     */
    public function createUpdateQueryBuilder($alias = null)
    {
        return $this->entityRepository->createUpdateQueryBuilder($alias);
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

    /**
     * @param string $sql
     * @param array $parameters
     * @return QueryAbstract|SelectQuery
     */
    public function selectQuery(string $sql, array $parameters = [])
    {
        $statement = $this->pdo->prepare($sql);
        return (new SelectQuery($this->pdo, $statement, $parameters))->setEntityClass($this->getRepository()->getClassName());
    }
}
