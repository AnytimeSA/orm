<?php

namespace Anytime\ORM\Cache;

use Anytime\ORM\QueryBuilder\QueryBuilderAbstract;

class CacheId
{
    /**
     * Generate the cache ID by entity
     * @param string $entityClass
     * @param array $primaryValues
     * @return string
     */
    public function getEntityCacheIdByPrimaryKeyValues(string $entityClass, array $primaryValues)
    {
        $cacheID = 'ENTITY-' . hash('fnv132', $entityClass);
        foreach($primaryValues as $value) {
            $cacheID .= '-' . $value;
        }
        return $cacheID;
    }
}