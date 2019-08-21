<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\Entity;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\Entity\FooComposite;

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
                'id' => 1,
                'id2' => 2
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
                'id'            => 1,
                'id2'           => 2,
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
        $entity = new FooComposite([
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
     * @group EntityManager
     * @group Entity
     */
    public function testUpdateNeeded()
    {
        $foo = $this->getEntity();
        $this->assertFalse($foo->updateNeeded(), 'If a setter of an initialized entity has never been called, the method should returns false');

        $foo->setSomeField('test 2');
        $this->assertTrue($foo->updateNeeded(), 'If a setter of an initialized entity has been called at least one time, the method should returns true');
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function testResetDataSetterUsedGlobally()
    {
        $this->assertSame([], $this->getEntity()->setSomeField('new string')->resetDataSetterUsed()->extractSetterUsedData());
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function testResetDataSetterUsedWithOneFieldInParam()
    {
        $this->assertSame(['some_field' => 'new string'], $this->getEntity()->setSomeField('new string')->resetDataSetterUsed('id2')->extractSetterUsedData());
        $this->assertSame([], $this->getEntity()->setSomeField('new string')->resetDataSetterUsed('some_field')->extractSetterUsedData());
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function testGetEntityPropertyTypeOnExistingField()
    {
        $this->assertSame('string', FooComposite::getEntityPropertyType('some_field'));
        $this->assertSame('int', FooComposite::getEntityPropertyType('id'));
        $this->assertSame('int', FooComposite::getEntityPropertyType('id2'));
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function testGetEntityPropertyTypeOnNonExistingField()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity property "non_existing_field" not found for entity ' . FooComposite::class);
        FooComposite::getEntityPropertyType('non_existing_field');
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function testIsPropertyNullableOnExistingField()
    {
        $this->assertTrue(FooComposite::isPropertyNullable('some_field'));
        $this->assertFalse(FooComposite::isPropertyNullable('id'));
        $this->assertFalse(FooComposite::isPropertyNullable('id2'));
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function testIsPropertyNullableOnNonExistingField()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity property "non_existing_field" not found for entity ' . FooComposite::class);
        FooComposite::isPropertyNullable('non_existing_field');
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function getPropertyDefaultValueOnExistingField()
    {
        $this->assertSame('default value', FooComposite::getPropertyDefaultValue('some_field'));
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function getPropertyDefaultValueOnNonExistingField()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity property "non_existing_field" not found for entity ' . FooComposite::class);
        FooComposite::getPropertyDefaultValue('non_existing_field');
    }

    /**
     * @group EntityManager
     * @group Entity
     */
    public function testIsPropertyExists()
    {
        $this->assertTrue(FooComposite::isPropertyExists('some_field'));
        $this->assertFalse(FooComposite::isPropertyExists('non_existing_field'));
    }

    /**
     * @return Entity
     */
    private function getEntity(): FooComposite
    {
        return new FooComposite([
            'id' => 1,
            'id2' => 2,
            'some_field' => 'Some string'
        ]);
    }
}
