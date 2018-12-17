<?php

namespace Anytime\ORM\Generator\EntityGenerator;

/**
 * Interface EntityGeneratorInterface
 * @package Anytime\ORM\Generator\EntityGenerator
 */
interface EntityGeneratorInterface
{
    /**
     * @param array $tableList
     * @param array $ignoredtables
     */
    public function generate(array $tableList = [], array $ignoredtables = []);

    /**
     * @param string $tableName
     * @param array $tableStruct
     * @return string
     */
    public function generateEntityClassString(string $tableName, array $tableStruct): string;
}
