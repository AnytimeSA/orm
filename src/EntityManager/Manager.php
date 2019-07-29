<?php

namespace Anytime\ORM\EntityManager;

use Anytime\ORM\QueryBuilder\QueryAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderInterface;
use Anytime\ORM\QueryBuilder\SelectQuery;

abstract class Manager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var FilterCollection
     */
    protected $filterCollection;

    /**
     * EntityRepository constructor.
     * @param Connection $connection
     * @param EntityRepository $entityRepository
     * @param EntityManager $entityManager
     */
    public function __construct(Connection $connection, EntityRepository $entityRepository, EntityManager $entityManager, FilterCollection $filterCollection)
    {
        $this->connection = $connection;
        $this->entityRepository = $entityRepository;
        $this->entityManager = $entityManager;
        $this->filterCollection = $filterCollection;
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
        $statement = $this->connection->prepare($sql);
        return (new SelectQuery($this->connection, $statement, $this->filterCollection, $parameters))->setEntityClass($this->getRepository()->getClassName());
    }
}
