<?php

namespace DVE\EntityORM\Generator\EntityGenerator;

interface TableStructureRetrieverInterface
{
    /**
     * @param array|null $tableList
     * @return array
     */
    public function retrieve(array $tableList = []): array;
}
