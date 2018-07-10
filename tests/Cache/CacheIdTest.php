<?php

namespace Anytime\ORM\Tests\Cache;

use Anytime\ORM\Cache\CacheId;
use PHPUnit\Framework\TestCase;

class CacheIdTest extends TestCase
{
    /**
     * @dataProvider getEntityCacheIdByPrimaryKeyValuesProvider
     * @param string $class
     * @param string|null $pk1
     * @param string|null $pk2
     * @param string $expected
     */
    public function testGetEntityCacheIdByPrimaryKeyValues(string $class, $pk1, $pk2, string $expected)
    {
        $pks = [];

        if(!is_null($pk1)) $pks[] = $pk1;
        if(!is_null($pk2)) $pks[] = $pk2;

        $cacheId = new CacheId();
        $this->assertSame($expected, $cacheId->getEntityCacheIdByPrimaryKeyValues($class, $pks));
    }

    /**
     * @return array
     */
    public function getEntityCacheIdByPrimaryKeyValuesProvider()
    {
        return [
            ['My\\Class', 1, 2, 'ENTITY-34e68847-1-2'],
            ['My\\Class', 1, null, 'ENTITY-34e68847-1']
        ];
    }
}