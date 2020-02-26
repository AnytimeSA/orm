<?php

namespace Anytime\ORM\Generator\EntityGenerator;

abstract class TableStructureRetrieverAbstract implements TableStructureRetrieverInterface
{
    /**
     * @param string $phpType
     * @return bool|float|int|null|string
     */
    public function getDefaultValueByPhpType(string $phpType)
    {
        switch($phpType) {
            case 'int': return 0;
            case 'float': return 0.0;
            case 'bool': return false;
            case 'date': return '1970-01-01 00:00:00.000000 +00:00';
            case 'string': return '';
            default: return null;
        }
    }
}
