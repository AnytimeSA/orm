<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Entity;

abstract class QueryBuilderAbstract implements QueryBuilderInterface
{
    const QUERY_TYPE_SELECT = 'SELECT';
    const QUERY_TYPE_INSERT = 'INSERT';
    const QUERY_TYPE_UPDATE = 'UPDATE';
    const QUERY_TYPE_DELETE = 'DELETE';

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    protected $snakeToCamelCaseStringConverter;


    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $queryType = self::QUERY_TYPE_SELECT;

    /**
     * @var string
     */
    protected $select;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $groupBy;

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var int
     */
    protected $limitNumber;

    /**
     * @var int
     */
    protected $limitOffset = 0;

    /**
     * @var string
     */
    protected $orderBy;

    /**
     * @var array
     */
    protected $join = [];

    /**
     * @var array
     */
    protected $fieldsToUpdate = [];

    /**
     * QueryBuilderAbstract constructor.
     * @param \PDO $pdo
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     */
    public function __construct(\PDO $pdo, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->pdo = $pdo;
    }

    /**
     * @return string
     */
    public function getQueryType(): string
    {
        return $this->queryType;
    }

    /**
     * @param string $queryType
     * @return QueryBuilderAbstract
     */
    public function setQueryType(string $queryType): QueryBuilderAbstract
    {
        $this->queryType = $queryType;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setEntityClass(string $entityClass): QueryBuilderInterface
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParameters(array $parameters): QueryBuilderInterface
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param array $fieldsToUpdate
     * @return QueryBuilderAbstract
     */
    public function setFieldsToUpdate(array $fieldsToUpdate): QueryBuilderAbstract
    {
        $this->fieldsToUpdate = $fieldsToUpdate;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function select(string $select): QueryBuilderInterface
    {
        $this->select = $select;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function where(string $where): QueryBuilderInterface
    {
        if($where != '') {
            $this->where = [$where];
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andWhere(string $where): QueryBuilderInterface
    {
        if($where != '') {
            $this->where[] = $where;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orWhere(string $where): QueryBuilderInterface
    {
        if($where != '') {
            $countWhere = count($this->where);

            if ($countWhere < 1) {
                $this->where[] = $where;
            } else {
                $this->where[$countWhere - 1] .= ') OR (' . $where;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function limit(int $number, int $offset): QueryBuilderInterface
    {
        $this->limitNumber = $number < 1 ? 1 : $number;
        $this->limitOffset = $offset < 0 ? 0 : $offset;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orderBy(string $orderBy): QueryBuilderInterface
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function join(string $join): QueryBuilderInterface
    {
        $this->join[] = $join;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function groupBy(string $groupBy): QueryBuilderInterface
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSelectQuery(): QueryAbstract
    {
        if($this->queryType !== QueryBuilderAbstract::QUERY_TYPE_SELECT) {
            throw new \RuntimeException('Not in an ' . QueryBuilderAbstract::QUERY_TYPE_SELECT . ' context');
        }

        $statement = $this->pdo->prepare($this->getSelectSQL());
        return (new SelectQuery($this->pdo, $statement, $this->parameters))->setEntityClass($this->entityClass);
    }

    /**
     * @inheritDoc
     */
    public function getInsertQuery(Entity $entity): QueryAbstract
    {
        if($this->queryType !== QueryBuilderAbstract::QUERY_TYPE_INSERT) {
            throw new \RuntimeException('Not in an ' . QueryBuilderAbstract::QUERY_TYPE_INSERT . ' context');
        }

        $data = $entity->extractSetterUsedData();
        $statement = $this->pdo->prepare($this->getInsertSQL($data));
        return (new InsertQuery($this->pdo, $statement, $data))->setEntityClass($this->entityClass);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateQuery(Entity $entity = null): QueryAbstract
    {
        if($this->queryType !== QueryBuilderAbstract::QUERY_TYPE_UPDATE) {
            throw new \RuntimeException('Not in an ' . QueryBuilderAbstract::QUERY_TYPE_UPDATE . ' context');
        }

        if($entity) {
            $primaryKeys = $this->entityClass::PRIMARY_KEYS;
            $primaryKeysData = $entity->extractPrimaryKeyValues();

            $fieldsToUpdate = $entity->extractSetterUsedData();
            $data = [];

            foreach($primaryKeys as $pkeyName) {
                $data[$pkeyName] = $primaryKeysData[$pkeyName];
            }

            $statement = $this->pdo->prepare($this->getUpdateByPrimaryKeySQL($fieldsToUpdate));
            return (new UpdateQuery($this->pdo, $statement, $data, $fieldsToUpdate))->setEntityClass($this->entityClass);

        } else {
            $statement = $this->pdo->prepare($this->getUpdateByCriteriaSQL($this->fieldsToUpdate));
            return (new UpdateQuery($this->pdo, $statement, $this->parameters, $this->fieldsToUpdate))->setEntityClass($this->entityClass);
        }
    }

    /**
     * @inheritDoc
     */
    public function getDeleteQuery(Entity $entity = null): QueryAbstract
    {
        if($this->queryType !== QueryBuilderAbstract::QUERY_TYPE_DELETE) {
            throw new \RuntimeException('Not in an ' . QueryBuilderAbstract::QUERY_TYPE_DELETE . ' context');
        }

        if($entity) {
            $parameters = [];
            foreach($this->entityClass::PRIMARY_KEYS as $pkeyName) {
                $getter = 'get' . $this->snakeToCamelCaseStringConverter->convert($pkeyName);
                $parameters[$pkeyName] = $entity->$getter();
            }
            $statement = $this->pdo->prepare($this->getDeleteByPrimaryKeySQL());
        } else {
            $statement = $this->pdo->prepare($this->getDeleteByCriteriaSQL());
            $parameters = $this->parameters;
        }

        return (new DeleteQuery($this->pdo, $statement, $parameters))->setEntityClass($this->entityClass);
    }
}