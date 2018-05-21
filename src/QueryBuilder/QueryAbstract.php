<?php

namespace DVE\EntityORM\QueryBuilder;

class QueryAbstract
{
    /**
     * @var \PDOStatement
     */
    protected $PDOStatement;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * Query constructor.
     * @param \PDOStatement $PDOStatement
     * @param $parameters
     */
    public function __construct(\PDOStatement $PDOStatement, $parameters)
    {
        $this->PDOStatement = $PDOStatement;
        $this->parameters = $parameters;
    }
}