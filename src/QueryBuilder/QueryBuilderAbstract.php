<?php

namespace DVE\EntityORM\QueryBuilder;

abstract class QueryBuilderAbstract implements QueryBuilderInterface
{
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