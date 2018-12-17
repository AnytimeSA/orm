<?php

namespace Anytime\ORM\Generator\EntityManagerGenerator;

interface EntityManagerGeneratorInterface
{
    /**
     * @param array $tableList
     * @param array $ignoredTables
     */
    public function generate(array $tableList = [], array $ignoredTables = []);
}
