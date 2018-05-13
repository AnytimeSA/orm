<?php

namespace DVE\EntityORM\EntityManager;

abstract class EntityManager
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * EntityManager constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}