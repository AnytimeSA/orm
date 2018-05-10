<?php

namespace DVE\EntityORM\QueryBuilder;

abstract class QueryBuilderAbstract implements QueryBuilderInterface
{
    const RETURN_TYPE_ENTITY = 1;
    const RETURN_TYPE_ARRAY = 2;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * QueryBuilderAbstract constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}