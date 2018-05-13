<?php

namespace DVE\EntityORM\QueryBuilder;

interface QueryInterface
{
    public function setFetchDataFormat(string $fetchDataFormat): Query;

    public function fetchOne();

    public function fetch();

    public function fetchAll(): array;
}