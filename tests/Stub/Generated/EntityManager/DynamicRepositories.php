<?php

namespace Anytime\ORM\Tests\Stub\Generated\EntityManager;

use Anytime\ORM\EntityManager\Repositories;

class DynamicRepositories extends Repositories
{
    /**
     * @return FooEntotyRepository|\Anytime\ORM\Tests\Stub\Generated\DefaultRepository\FooEntityRepository
     */
    public function getFooEntityRepository(): \Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooEntityRepository
    {
        return $this->loadAndGetRepository(
            'Anytime\ORM\Tests\Stub\Custom\EntityRepository\DefaultRepository\FooEntityRepository',
            'Anytime\ORM\Tests\Stub\Generated\EntityRepository\DefaultRepository\FooEntityRepository',
            'foo',
            'Anytime\ORM\Tests\Stub\Generated\Entity\Foo'
        );
    }
}
