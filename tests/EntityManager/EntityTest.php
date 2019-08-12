<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\Entity;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Foo;

class EntityTest extends ORMTestCase
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
     * @group EntityManager
     * @group Entity
     */
    public function testExtractSetterUsedDataWithExtraDbField()
    {
        $entity = new Foo([
            'id' => 123,
            'some_field' => 'abc',
            'some_extra_unknown_field' => 'xyz'
        ]);

        $this->assertSame([], $entity->extractSetterUsedData());

        $entity->setSomeField('new value');

        $this->assertSame(
            ['some_field' => 'new value'],
            $entity->extractSetterUsedData()
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
