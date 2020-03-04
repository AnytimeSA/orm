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
     * @param FilterCollection $filterCollection
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
     * @return string|null
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @return void
     */
    protected function bindParams()
    {
        $pdoParamType = null;

        foreach($this->parameters as $param => $value) {
            $type = gettype($value);

            if(is_null($value)) {
                $pdoParamType = \PDO::PARAM_NULL;
            } else {
                switch($type) {
                    case 'integer': $pdoParamType = \PDO::PARAM_INT; break;
                    case 'boolean':
                        $pdoParamType = \PDO::PARAM_BOOL;
                        break;
                    default: $pdoParamType = \PDO::PARAM_STR;
                }
            }


            $this->PDOStatement->bindValue(
                gettype($param) === 'integer' ? ($param+1) : ':'.$param,
                $value,
                $pdoParamType
            );
        }
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
