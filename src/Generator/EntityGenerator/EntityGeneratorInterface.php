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
     * @return mixed
     */
    public function generate(array $tableList = []);

    /**
     * @param string $tableName
     * @param array $tableStruct
     * @return string
     */
    public function generateEntityClassString(string $tableName, array $tableStruct): string;
}
