<?php

namespace Anytime\ORM\Generator\QueryBuilderProxyGenerator;


interface QueryBuilderProxyGeneratorInterface
{
    /**
     * @param array $tableList
     * @param array $ignoredTables
     */
    public function generate(array $tableList = [], array $ignoredTables = []);

    /**
     * @param string $tableName
     * @param array $tableStruct
     * @return string
     */
    public function generateQueryBuilderUpdateProxyClassString(string $tableName, array $tableStruct): string;

    /**
     * @param string $tableName
     * @param array $tableStruct
     * @return string
     */
    public function generateQueryBuilderDeleteProxyClassString(string $tableName, array $tableStruct): string;
}
