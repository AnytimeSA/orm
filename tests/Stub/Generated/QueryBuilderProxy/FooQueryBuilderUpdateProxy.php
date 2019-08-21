<?php

namespace Anytime\ORM\Tests\Stub\Generated\QueryBuilderProxy;

use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;
use Anytime\ORM\QueryBuilder\UpdateQuery;
use Anytime\ORM\QueryBuilder\QueryBuilderProxyInterface;

class FooQueryBuilderUpdateProxy implements QueryBuilderProxyInterface
{
    /**
     * @var QueryBuilderAbstract
     */
    private $queryBuilder;

    public function __construct(QueryBuilderAbstract $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getQueryBuilder(): QueryBuilderAbstract
    {
        return $this->queryBuilder;
    }

    /**
     * @return UpdateQuery
     */
    public function getQuery()
    {
        return $this->queryBuilder->getUpdateQuery();
    }

    public function execute(): int
    {
        return $this->getQuery()->execute();
    }


}
