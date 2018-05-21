<?php

namespace DVE\EntityORM\QueryBuilder;

class QueryAbstract
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var \PDOStatement
     */
    protected $PDOStatement;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * Query constructor.
     * @param \PDO $pdo
     * @param \PDOStatement $PDOStatement
     * @param $parameters
     */
    public function __construct(\PDO $pdo, \PDOStatement $PDOStatement, $parameters)
    {
        $this->pdo = $pdo;
        $this->PDOStatement = $PDOStatement;
        $this->parameters = $parameters;
    }

    /**
     * @param string $entityClass
     * @return QueryAbstract
     */
    public function setEntityClass(string $entityClass): QueryAbstract
    {
        $this->entityClass = $entityClass;
        return $this;
    }
}