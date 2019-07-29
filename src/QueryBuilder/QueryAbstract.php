<?php

namespace Anytime\ORM\QueryBuilder;

use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\FilterCollection;

class QueryAbstract
{
    /**
     * @var Connection
     */
    protected $connection;

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
     * @var callable[][]
     */
    protected $filterCollection;

    /**
     * Query constructor.
     * @param Connection $connection
     * @param \PDOStatement $PDOStatement
     * @param $parameters
     */
    public function __construct(Connection $connection, \PDOStatement $PDOStatement, FilterCollection $filterCollection, $parameters)
    {
        $this->connection = $connection;
        $this->PDOStatement = $PDOStatement;
        $this->parameters = $parameters;
        $this->filterCollection = $filterCollection;
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

    /**
     * @param \PDOStatement $PDOStatement
     */
    protected function throwPdoError(\PDOStatement $PDOStatement)
    {
        $errInfo = $PDOStatement->errorInfo();
        if(array_key_exists(1, $errInfo) && $errInfo[1]) {
            $msg = array_key_exists(2, $errInfo) ? $errInfo[2] : 'Unknown error';
            throw new \RuntimeException($msg, (int)$errInfo[1]);
        }
    }
}
