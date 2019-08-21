<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\EntityManager\EntityManager;
use Anytime\ORM\EntityManager\Factory;
use Anytime\ORM\Generator\EntityGenerator\EntityGeneratorInterface;
use Anytime\ORM\Generator\EntityManagerGenerator\EntityManagerGeneratorInterface;
use Anytime\ORM\Generator\QueryBuilderProxyGenerator\QueryBuilderProxyGeneratorInterface;
use Anytime\ORM\Tests\ORMTestCase;
use Anytime\ORM\Tests\Stub\Generated\EntityManager\DynamicEntityManager;

class FactoryTest extends ORMTestCase
{
    /**
     * @group EntityManager
     * @group EntityManagerFactory
     */
    public function testSetters()
    {
        $factory = new Factory();
        $this->assertSame($factory, $factory->setDatabaseType(Factory::DATABASE_TYPE_MYSQL));
        $this->assertSame($factory, $factory->setEntityNamespace('\\'));
        $this->assertSame($factory, $factory->setEntityManagerNamespace('\\'));
        $this->assertSame($factory, $factory->setUserEntityRepositoryNamespace('\\'));
        $this->assertSame($factory, $factory->setUserManagerNamespace('\\'));
        $this->assertSame($factory, $factory->setQueryBuilderProxyNamespace('\\'));
        $this->assertSame($factory, $factory->setEntityDirectory('/'));
        $this->assertSame($factory, $factory->setEntityManagerDirectory('/'));
        $this->assertSame($factory, $factory->setUserEntityRepositoryDirectory('/'));
        $this->assertSame($factory, $factory->setUserManagerDirectory('/'));
        $this->assertSame($factory, $factory->setQueryBuilderProxyDirectory('/'));
    }

    /**
     * @group EntityManager
     * @group EntityManagerFactory
     */
    public function testCreateEntityManagerSucceed()
    {
        $pdo = $this->getPdoMockBuilder()->reveal();
        $factory = $this->getEntityManagerFactory();
        $em = $factory->createEntityManager($pdo);
        $this->assertInstanceOf(EntityManager::class, $em);
        $this->assertInstanceOf(DynamicEntityManager::class, $em);
        $this->assertSame($pdo, $em->getConnection()->getPDO());
    }

    /**
     * @group EntityManager
     * @group EntityManagerFactory
     */
    public function testCreateEntityManagerFailDueToMissingPropertiesInitialization()
    {
        $this->expectException(\InvalidArgumentException::class);
        $pdo = $this->getPdoMockBuilder()->reveal();
        $factory =  (new Factory());
        $factory->createEntityManager($pdo);
    }

    /**
     * @group EntityManager
     * @group EntityManagerFactory
     */
    public function testCreateEntityGenerator()
    {
        $pdo = $this->getPdoMockBuilder()->reveal();
        $factory = $this->getEntityManagerFactory();
        $entityGenerator = $factory->createEntityGenerator($pdo);
        $this->assertInstanceOf(EntityGeneratorInterface::class, $entityGenerator);
    }

    /**
     * @group EntityManager
     * @group EntityManagerFactory
     */
    public function testCreateEntityManagerGenerator()
    {
        $pdo = $this->getPdoMockBuilder()->reveal();
        $factory = $this->getEntityManagerFactory();
        $entityManagerGenerator = $factory->createEntityManagerGenerator($pdo);
        $this->assertInstanceOf(EntityManagerGeneratorInterface::class, $entityManagerGenerator);
    }

    /**
     * @group EntityManager
     * @group EntityManagerFactory
     */
    public function testCreateQueryBuilderProxyGenerator()
    {
        $pdo = $this->getPdoMockBuilder()->reveal();
        $factory = $this->getEntityManagerFactory();
        $queryBuilderProxyGenerator = $factory->createQueryBuilderProxyGenerator($pdo);
        $this->assertInstanceOf(QueryBuilderProxyGeneratorInterface::class, $queryBuilderProxyGenerator);
    }


}
