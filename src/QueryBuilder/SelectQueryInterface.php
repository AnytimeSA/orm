<?php

namespace DVE\EntityORM\QueryBuilder;

interface SelectQueryInterface
{
    /**
     * @param string $fetchDataFormat
     * @return SelectQuery
     */
    public function setFetchDataFormat(string $fetchDataFormat): SelectQuery;

    /**
     * @return mixed
     */
    public function fetchOne();

    /**
     * @return mixed
     */
    public function fetch();

    /**
     * @return array
     */
    public function fetchAll(): array;

    /**
     * @return mixed
     */
    public function fetchSingleScalarResult();
}