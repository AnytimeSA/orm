<?php

namespace DVE\EntityORM\Cache;

use DVE\EntityORM\QueryBuilder\QueryBuilderAbstract;
use DVE\EntityORM\QueryBuilder\QueryBuilderInterface;

class CacheId
{
    /**
     * Generate the cache ID by entity
     * @param string $entityClass
     * @param array $primaryValues
     * @return string
     */
    public function getEntityCacheByPrimaryKeyValues(string $entityClass, array $primaryValues)
    {
        $cacheID = 'ENTITY-' . hash('fnv132', $entityClass);
        foreach($primaryValues as $value) {
            $cacheID .= '-' . $value;
        }
        return $cacheID;
    }
//
//    public function getEntityCacheByQueryBuilder(QueryBuilderAbstract $queryBuilder)
//    {
////        $queryType = $queryBuilder->getQueryType();
////        $queryBuilder->
//    }
}