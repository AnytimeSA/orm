<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\Connection;
use Anytime\ORM\EntityManager\EntityManager;
use Anytime\ORM\EntityManager\FilterCollection;
use Anytime\ORM\QueryBuilder\DeleteQueryInterface;
use Anytime\ORM\QueryBuilder\InsertQueryInterface;
use Anytime\ORM\QueryBuilder\SelectQuery;
use Anytime\ORM\QueryBuilder\UpdateQueryInterface;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Filter\NullFilter;
use Anytime\ORM\Tests\Stub\Generated\Entity\Foo;
use Anytime\ORM\Tests\Stub\Generated\Entity\FooComposite;

class EntityManagerTest extends ORMTestCase
{
    /**
     * @group EntityManager
     */
    public function testGetters()
    {
        $entityManager = $this->getDynamicEntityManager();
        $this->assertInstanceOf(Connection::class, $entityManager->getConnection());
        $this->assertInstanceOf(FilterCollection::class, $entityManager->getFilterCollection());
    }

    /**
     * @group EntityManager
     */
    public function testCloseConnection()
    {
        $entityManager = $this->getDynamicEntityManager();
        $this->assertTrue($entityManager->closeConnection());
        $this->assertNull($entityManager->getConnection()->getPDO());
        $this->assertFalse($entityManager->closeConnection());
    }

    /**
     * @group EntityManager
     */
    public function testSelectQueryWithNoEntityClass()
    {
        $entityManager = $this->getDynamicEntityManager();
        $query = $entityManager->selectQuery('SELECT 1;');
        $this->assertInstanceOf(SelectQuery::class, $query);
        $this->assertNull($query->getEntityClass());
    }

    /**
     * @group EntityManager
     */
    public function testSelectQueryWithEntityClass()
    {
        $entityManager = $this->getDynamicEntityManager();
        $query = $entityManager->selectQuery('SELECT 1;', [], FooComposite::class);
        $this->assertInstanceOf(SelectQuery::class, $query);
        $this->assertSame(FooComposite::class, $query->getEntityClass());
    }

    /**
     * @group EntityManager
     */
    public function testInsertEntitiesWithNonObjectsValuesFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->insert(['not an entity object', 123]);
    }

    /**
     * @group EntityManager
     */
    public function testInsertEntitiesWithNonEntitiesValuesFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->insert([new \stdClass()]);
    }

    /**
     * @group EntityManager
     */
    public function testInsertCompositeEntitiesWithNullPrimaryKeysFail()
    {
        $foo = new FooComposite(['id' => 1, 'id2' => null, 'some_field' => 'abc']);
        $this->expectException(\RuntimeException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->insert([$foo]);
    }

    /**
     * @group EntityManager
     */
    public function testInsertCompositeEntitiesWithNonNullPrimaryKeysSucceed()
    {
        $foo = new FooComposite(['id' => 1, 'id2' => 2, 'some_field' => 'abc']);
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->insert([$foo]));
    }

    /**
     * @group EntityManager
     */
    public function testInsertSinglePrimaryKeyEntitiesWithSucceed()
    {
        $foo = new Foo(['some_field' => 'abc']);
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->insert([$foo]));
        $this->assertSame(1, $foo->getId(), 'Should return the last insert ID returned by the QueryBuilder::execute() method of the mock (defined to 1)');
    }

    /**
     * @group EntityManager
     */
    public function testInsertWithEntityArgInsteadOfArraySucceed()
    {
        $foo = new Foo(['some_field' => 'abc']);
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->insert($foo));
        $this->assertSame(1, $foo->getId(), 'Should return the last insert ID returned by the QueryBuilder::execute() method of the mock (defined to 1)');
    }

    /**
     * @group EntityManager
     */
    public function testUpdateEntitiesWithNonObjectsValuesFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->update(['not an entity object', 123]);
    }

    /**
     * @group EntityManager
     */
    public function testUpdateEntitiesWithNonEntitiesValuesFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->update([new \stdClass()]);
    }

    /**
     * @group EntityManager
     * @group testUpdateCompositeEntitiesWithNullPrimaryKeysFail
     */
    public function testUpdateCompositeEntitiesWithNullPrimaryKeysFail()
    {
        $foo = (new FooComposite(['id' => 1, 'id2' => null]))->setSomeField('def');
        $this->expectException(\RuntimeException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->update([$foo]);
    }

    /**
     * @group EntityManager
     */
    public function testUpdateCompositeEntitiesWithNonNullPrimaryKeysSucceed()
    {
        $foo = (new FooComposite(['id' => 1, 'id2' => 2]))->setSomeField('def');
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->update([$foo]));
    }

    /**
     * @group EntityManager
     */
    public function testUpdateSinglePrimaryKeyEntitiesWithSucceed()
    {
        $foo = (new Foo(['id' => 1]))->setSomeField('def');
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->update([$foo]));
        $this->assertSame(1, $foo->getId(), 'Should return the number of updated rows by the QueryBuilder::execute() method of the mock (defined to 1)');
    }

    /**
     * @group EntityManager
     */
    public function testUpdatetWithEntityArgInsteadOfArraySucceed()
    {
        $foo = (new Foo(['id' => 1]))->setSomeField('def');
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->update($foo));
        $this->assertSame(1, $foo->getId(), 'Should return the number of updated rows by the QueryBuilder::execute() method of the mock (defined to 1)');
    }


    /**
     * @group EntityManager
     */
    public function testDeleteEntitiesWithNonObjectsValuesFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->delete(['not an entity object', 123]);
    }

    /**
     * @group EntityManager
     */
    public function testDeleteEntitiesWithNonEntitiesValuesFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->delete([new \stdClass()]);
    }

    /**
     * @group EntityManager
     * @group testUpdateCompositeEntitiesWithNullPrimaryKeysFail
     */
    public function testDeleteCompositeEntitiesWithNullPrimaryKeysFail()
    {
        $foo = (new FooComposite(['id' => 1, 'id2' => null, 'some_field' => 'abc']));
        $this->expectException(\RuntimeException::class);
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->delete([$foo]);
    }

    /**
     * @group EntityManager
     */
    public function testDeleteCompositeEntitiesWithNonNullPrimaryKeysSucceed()
    {
        $foo = (new FooComposite(['id' => 1, 'id2' => 2, 'some_field' => 'abc']));
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->delete([$foo]));
    }

    /**
     * @group EntityManager
     */
    public function testDeleteSinglePrimaryKeyEntitiesWithSucceed()
    {
        $foo = (new Foo(['id' => 1, 'some_field' => 'abc']));
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->delete([$foo]));
        $this->assertSame(1, $foo->getId(), 'Should return the number of deleted rows by the QueryBuilder::execute() method of the mock (defined to 1)');
    }

    /**
     * @group EntityManager
     */
    public function testDeleteWithEntityArgInsteadOfArraySucceed()
    {
        $foo = (new Foo(['id' => 1, 'some_field' => 'abc']));
        $entityManager = $this->getDynamicEntityManager();
        $this->assertNull($entityManager->delete($foo));
        $this->assertSame(1, $foo->getId(), 'Should return the number of deleted rows by the QueryBuilder::execute() method of the mock (defined to 1)');
    }

    /**
     * @group EntityManager
     */
    public function testDeleteWithEntityWithNullValueAsPrimaryKey()
    {
        $this->expectException(\RuntimeException::class);
        $foo = (new Foo(['some_field' => 'abc']));
        $entityManager = $this->getDynamicEntityManager();
        $entityManager->delete($foo);
    }

    /**
     * @group EntityManager
     */
    public function testDeleteQuery()
    {
        $entityManager = $this->getDynamicEntityManager();
        $deleteQuery = $entityManager->deleteQuery('DELETE FROM foo WHERE 1;', []);
        $this->assertInstanceOf(DeleteQueryInterface::class, $deleteQuery);
        $this->assertNull($deleteQuery->getEntityClass());
    }

    /**
     * @group EntityManager
     */
    public function testUpdateQuery()
    {
        $entityManager = $this->getDynamicEntityManager();
        $updateQuery = $entityManager->updateQuery('UPDATE foo SET bar = 1 WHERE 1;', []);
        $this->assertInstanceOf(UpdateQueryInterface::class, $updateQuery);
        $this->assertNull($updateQuery->getEntityClass());
    }

    /**
     * @group EntityManager
     */
    public function testInsertQuery()
    {
        $entityManager = $this->getDynamicEntityManager();
        $insertQuery = $entityManager->insertQuery('INSERT INTO foo SET bar = 1;', []);
        $this->assertInstanceOf(InsertQueryInterface::class, $insertQuery);
        $this->assertNull($insertQuery->getEntityClass());
    }

    /**
     * @group EntityManager
     */
    public function testGetFilterCollection()
    {
        $entityManager = $this->getDynamicEntityManager();
        $this->assertInstanceOf(FilterCollection::class, $entityManager->getFilterCollection());
    }

    /**
     * @group EntityManager
     */
    public function testAddFilter()
    {
        $filter = new NullFilter('Test filter', null);
        $entityManager = $this->getDynamicEntityManager();
        $this->assertInstanceOf(EntityManager::class, $entityManager->addFilter($filter));
    }
}
