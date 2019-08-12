<?php

namespace Anytime\ORM\Tests\EntityManager;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\EntityManager\DefaultEntityRepository;
use Anytime\ORM\QueryBuilder\QueryBuilderFactory;
use Anytime\ORM\Tests\ORMTestCase;

class DefaultEntityRepositoryTest extends ORMTestCase
{
    /**
     * @group EntityRepository
     */
    public function testSettersAndGetters()
    {
        $entityRepository = $this->getDefaultEntityRepository();
        $this->assertNull($entityRepository->getTableName());
        $this->assertNull($entityRepository->getClassName());
        $this->assertSame($entityRepository, $entityRepository->setTableName('user'));
        $this->assertSame($entityRepository, $entityRepository->setClassName('Entity/User'));
        $this->assertSame('user', $entityRepository->getTableName());
        $this->assertSame('Entity/User', $entityRepository->getClassName());
    }


    private function getDefaultEntityRepository()
    {
        return new DefaultEntityRepository(
            $this->getConnection(true),
            $this->prophesize(SnakeToCamelCaseStringConverter::class)->reveal(),
            $this->prophesize(QueryBuilderFactory::class)->reveal()
        );
    }
}
