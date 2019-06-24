<?php

namespace Anytime\ORM\EntityManager;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\QueryBuilder\DeleteQuery;
use Anytime\ORM\QueryBuilder\InsertQuery;
use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\QueryBuilder\SelectQuery;
use Anytime\ORM\QueryBuilder\UpdateQuery;

abstract class EntityManager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    protected $snakeToCamelCaseStringConverter;

    /**
     * @var string
     */
    protected $databaseType;

    /**
     * EntityManager constructor.
     * @param Connection $connection
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param string $databaseType
     */
    public function __construct(Connection $connection, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, QueryBuilderFactory $queryBuilderFactory, string $databaseType)
    {
        $this->connection = $connection;
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->databaseType = $databaseType;
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * @return Connection|null
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return bool
     */
    public function closeConnection(): bool
    {
        return $this->connection->closeConnection();
    }

    /**
     * @param Entity|Entity[] $entities
     */
    public function insert($entities)
    {
        if(!is_array($entities)) {
            $entities = [$entities];
        }

        foreach($entities as $entity) {

            if(!is_object($entity) || !is_subclass_of($entity, Entity::class)) {
                throw new \InvalidArgumentException('Entities to insert should be an instance of ' . Entity::class);
            }

            $pkeyValues = $entity->extractPrimaryKeyValues();
            $entityClass = get_class($entity);
            $isComposite = count($pkeyValues) > 1;

            // Composite primary keys should not be null for insert
            if($isComposite && in_array(null, $pkeyValues)) {
                throw new \RuntimeException('Composite primary key values should\'nt be null.');
            }

            $queryBuilder = $this->queryBuilderFactory->create($this->databaseType);
            $query = $queryBuilder
                ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_INSERT)
                ->setEntityClass($entityClass)
                ->getInsertQuery($entity)
            ;

            $insertId = $query->execute();

            // If not a composite pkey we update the pkey value with the last insert ID
            if(!$isComposite) {
                if(count($entityClass::PRIMARY_KEYS) > 0) {
                    $pkeyName = $entityClass::PRIMARY_KEYS[0];
                    $setter = 'set'.$this->snakeToCamelCaseStringConverter->convert($pkeyName);
                    $entity->$setter($insertId);
                    $entity->resetDataSetterUsed($pkeyName);
                }
            }
        }
    }

    /**
     * @param Entity|Entity[] $entities
     */
    public function update($entities)
    {
        if(!is_array($entities)) {
            $entities = [$entities];
        }

        foreach($entities as $entity) {

            if(!is_object($entity) || !is_subclass_of($entity, Entity::class)) {
                throw new \InvalidArgumentException('Entities to update should be an instance of ' . Entity::class);
            }

            // No update if no changes
            if(!$entity->updateNeeded()) {
                continue;
            }

            $pkeyValues = $entity->extractPrimaryKeyValues();
            $entityClass = get_class($entity);

            if(in_array(null, $pkeyValues)) {
                throw new \RuntimeException('Null values for primary keys are not allowed in an update context.');
            }

            $queryBuilder = $this->queryBuilderFactory->create($this->databaseType);
            $query = $queryBuilder
                ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_UPDATE)
                ->setEntityClass($entityClass)
                ->getUpdateQuery($entity)
            ;

            $query->execute();
        }
    }

    /**
     * @param Entity|Entity[] $entities
     */
    public function delete($entities)
    {
        if(!is_array($entities)) {
            $entities = [$entities];
        }

        foreach($entities as $entity) {

            if(!is_object($entity) || !is_subclass_of($entity, Entity::class)) {
                throw new \InvalidArgumentException('Entities to delete should be an instance of ' . Entity::class);
            }

            $pkeyValues = $entity->extractPrimaryKeyValues();
            $entityClass = get_class($entity);

            if(in_array(null, $pkeyValues)) {
                throw new \RuntimeException('Null values for primary keys are not allowed in a delete context.');
            }

            $queryBuilder = $this->queryBuilderFactory->create($this->databaseType);
            $query = $queryBuilder
                ->setQueryType(QueryBuilderAbstract::QUERY_TYPE_DELETE)
                ->setEntityClass($entityClass)
                ->getDeleteQuery($entity)
            ;

            $query->execute();
        }
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @param string|null $entityClass
     * @return SelectQuery
     */
    public function selectQuery(string $sql, array $parameters = [], string $entityClass = null)
    {
        $statement = $this->connection->prepare($sql);
        $query = new SelectQuery($this->connection, $statement, $parameters);

        if($entityClass && class_exists($entityClass) && is_subclass_of($entityClass, Entity::class)) {
            $query->setEntityClass($entityClass);
        }

        return $query;
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @return DeleteQuery
     */
    public function deleteQuery(string $sql, array $parameters = [])
    {
        $statement = $this->connection->prepare($sql);
        $query = new DeleteQuery($this->connection, $statement, $parameters);
        return $query;
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @return UpdateQuery
     */
    public function updateQuery(string $sql, array $parameters = [])
    {
        $statement = $this->connection->prepare($sql);
        $query = new UpdateQuery($this->connection, $statement, $parameters);
        return $query;
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @return InsertQuery
     */
    public function insertQuery(string $sql, array $parameters = [])
    {
        $statement = $this->connection->prepare($sql);
        $query = new InsertQuery($this->connection, $statement, $parameters);
        return $query;
    }
}
