<?php

namespace Anytime\ORM\Tests\Stub\Generated\EntityManager;

use Anytime\ORM\EntityManager\EntityManager;
use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\FilterCollection;

class DynamicEntityManager extends EntityManager
{
    /**
     * @var DynamicRepositories
     */
    public $repositories;

    /**
     * @var DynamicManagers
     */
    public $managers;

    public function __construct(Connection $connection, SnakeToCamelCaseStringConverter $snakeToCamelCaseStringConverter, DynamicRepositories $dynamicRepositories, DynamicManagers $dynamicManagers, QueryBuilderFactory $queryBuilderFactory, FilterCollection $filterCollection, string $databaseType)
    {
        $this->repositories = $dynamicRepositories;
        $this->managers = $dynamicManagers;
        $this->managers = $dynamicManagers;
        parent::__construct($connection, $snakeToCamelCaseStringConverter, $queryBuilderFactory, $filterCollection, $databaseType);
    }
}

