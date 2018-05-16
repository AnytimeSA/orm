<?php

namespace DVE\EntityORM\QueryBuilder;

interface QueryInterface
{
    /**
     * @param string $fetchDataFormat
     * @return Query
     */
    public function setFetchDataFormat(string $fetchDataFormat): Query;

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