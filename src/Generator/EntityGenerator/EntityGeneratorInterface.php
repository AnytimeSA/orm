<?php

namespace DVE\EntityORM\Generator\EntityGenerator;

/**
 * Interface EntityGeneratorInterface
 * @package DVE\EntityORM\Generator\EntityGenerator
 */
interface EntityGeneratorInterface
{
    /**
     * @param array $tableList
     * @return mixed
     */
    public function generate(array $tableList = []);

    /**
     * @return string
     */
    public function generateEntityClassString(): string;
}