<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\Entity;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    /**
     * @group EntityManager
     * @group Entity
     */
    public function testExtractPrimaryKeyValues()
    {
        $entity = $this->getEntity();
        $this->assertSame(
            [
                'id' => 10,
                'id2' => 20
            ],
            $entity->extractPrimaryKeyValues()
        );
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function testExtractData()
    {
        $entity = $this->getEntity();
        $this->assertSame(
            [
                'id'            => 10,
                'id2'           => 20,
                'some_field'    => 'Some string'
            ],
            $entity->extractData()
        );
    }

    /**
     * @return Entity|__anonymous@501
     */
    private function getEntity()
    {
        return new class extends Entity {
            const TABLENAME = 'test_entity';
            const PRIMARY_KEYS = ['id', 'id2'];

            protected $data = [
                'id' => 10,
                'id2' => 20,
                'some_field' => 'Some string'
            ];
        };
    }
}