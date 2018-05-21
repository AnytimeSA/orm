<?php

namespace DVE\EntityORM\QueryBuilder;

abstract class QueryBuilderAbstract implements QueryBuilderInterface
{
    const QUERY_TYPE_SELECT = 'SELECT';
    const QUERY_TYPE_INSERT = 'INSERT';
    const QUERY_TYPE_UPDATE = 'UPDATE';
    const QUERY_TYPE_DELETE = 'DELETE';

    /**
     * @var \PDO
     */
    private $pdo;

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
     * QueryBuilderAbstract constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
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
        $this->where = [$where];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andWhere(string $where): QueryBuilderInterface
    {
        $this->where[] = $where;
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
    public function getQuery(): SelectQueryInterface
    {
        $statement = $this->pdo->prepare($this->getSQL());
        $query = new SelectQuery($statement, $this->parameters);
        $query->setEntityClass($this->entityClass);
        return $query;
    }
}