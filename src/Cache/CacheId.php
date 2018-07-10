<?php

namespace Anytime\ORM\Cache;

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

    /**
     * @param string $sqlQuery This is the SQL string passed to the PDOStatement
     * @param array $sqlParams This is the parameters passed to the PDOStatement
     * @param int $index This is the index in the results. When you make the first "fetch" the index is 1, and the next one 2,..., 3...
     * @return string
     */
    public function getSelectQueryResultCacheId(string $sqlQuery, array $sqlParams, int $index = 1)
    {
        $paramsString = '';
        $sqlHash = hash('fnv132', $sqlQuery);

        foreach($sqlParams as $sqlParamName => $sqlParamValue) {
            $paramsString .= ($paramsString ? '|' : '').$sqlParamName . ':' . $sqlParamValue;
        }

        $paramsHash = hash('fnv132', $paramsString);

        return $sqlHash . '-' . $paramsHash . '-' . $index;
    }
}