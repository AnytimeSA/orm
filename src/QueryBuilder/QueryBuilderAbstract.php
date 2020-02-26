<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\Entity;
use Anytime\ORM\EntityManager\FilterCollection;

abstract class QueryBuilderAbstract implements QueryBuilderInterface
{
    const MAX_BIG_INT_VALUE = 9223372036854775807;

    const QUERY_TYPE_SELECT = 'SELECT';
    const QUERY_TYPE_INSERT = 'INSERT';
    const QUERY_TYPE_UPDATE = 'UPDATE';
    const QUERY_TYPE_DELETE = 'DELETE';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var SnakeToCamelCaseStringConverter
     */
    protected $snakeToCamelCaseStringConverter;

    /**
     * @var FilterCollection
     */
    protected $filterCollection;


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
     * @param Connection $connection
     * @param SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter
     */
    public function __construct(Connection $connection, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, FilterCollection $filterCollection)
    {
        $this->snakeToCamelCaseStringConverter = $snakeToCamelCaseStringConverter;
        $this->connection = $connection;
        $this->filterCollection = $filterCollection;
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
     * @inheritDoc
     */
    public function setParameter(string $paramName, $paramValue): QueryBuilderInterface
    {
        $this->parameters[$paramName] = $paramValue;
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
     * @param string $fieldName
     * @param $newValue
     * @return $this
     */
    public function addFieldToUpdate(string $fieldName, $newValue)
    {
        $this->fieldsToUpdate[$fieldName] = $newValue;
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
        $this->limitNumber = $number < 0 ? 0 : $number;
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

        $statement = $this->connection->prepare($this->getSelectSQL());
        return (new SelectQuery($this->connection, $statement, $this->filterCollection, $this->parameters))->setEntityClass($this->entityClass);
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
        $statement = $this->connection->prepare($this->getInsertSQL($data));
        return (new InsertQuery($this->connection, $statement, $this->filterCollection, $data))->setEntityClass($this->entityClass);
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

            $statement = $this->connection->prepare($this->getUpdateByPrimaryKeySQL($fieldsToUpdate));
            return (new UpdateQuery($this->connection, $statement, $this->filterCollection, $data, $fieldsToUpdate))->setEntityClass($this->entityClass);

        } else {
            $sql = $this->getUpdateByCriteriaSQL($this->fieldsToUpdate);
            $newFieldsToUpdate = [];

            foreach($this->fieldsToUpdate as $fieldToUpdate => $fieldToUpdateValue) {
                if(!is_object($fieldToUpdateValue)) {
                    $newFieldsToUpdate[$fieldToUpdate] = $fieldToUpdateValue;
                }
            }

            $statement = $this->connection->prepare($sql);
            return (new UpdateQuery($this->connection, $statement, $this->filterCollection, $this->parameters, $newFieldsToUpdate))->setEntityClass($this->entityClass);
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
            $statement = $this->connection->prepare($this->getDeleteByPrimaryKeySQL());
        } else {
            $statement = $this->connection->prepare($this->getDeleteByCriteriaSQL());
            $parameters = $this->parameters;
        }

        return (new DeleteQuery($this->connection, $statement, $this->filterCollection, $parameters))->setEntityClass($this->entityClass);
    }

    /**
     * @return int
     */
    public function execute()
    {
        $allowedQueryType = [self::QUERY_TYPE_DELETE, self::QUERY_TYPE_INSERT, self::QUERY_TYPE_UPDATE];

        if(in_array($this->queryType, $allowedQueryType)) {
            switch($this->queryType) {
                case self::QUERY_TYPE_UPDATE: return $this->getUpdateQuery()->execute();
                case self::QUERY_TYPE_INSERT: return $this->getInsertQuery()->execute();
                case self::QUERY_TYPE_DELETE: return $this->getDeleteQuery()->execute();
            }
        } else {
            throw new \RuntimeException('The execute method is allowed for ' . implode('/', $allowedQueryType) . " query type only.");
        }
    }

    /**
     * @param array $fields
     */
    protected function checkUpdateFieldsArray(array $fields)
    {
        if(count($fields) < 1) {
            throw new \InvalidArgumentException('Update and insert methods require an non-empty array containing the list of fields to update as first argument.');
        }
    }

    /**
     * @param string $fieldName
     */
    protected function checkFieldNameFormat($fieldName)
    {
        if(is_numeric($fieldName)) {
            throw new \InvalidArgumentException('Invalid field name "'.$fieldName.'".');
        }
    }
}
