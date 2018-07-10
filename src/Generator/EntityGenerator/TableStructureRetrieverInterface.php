<?php

namespace Anytime\ORM\Generator\EntityGenerator;

interface TableStructureRetrieverInterface
{
    /**
     * @param array|null $tableList
     * @return array
     */
    public function retrieve(array $tableList = []): array;
}
