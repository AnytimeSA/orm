<?php

namespace Anytime\ORM\QueryBuilder;

interface QueryBuilderProxyInterface
{
    /**
     * @return QueryAbstract
     */
    public function getQuery();

    /**
     * @return int
     */
    public function execute(): int;
}
