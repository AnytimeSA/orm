<?php

namespace Anytime\ORM\Tests\Stub\Generated\EntityManager;

use Anytime\ORM\EntityManager\Repositories;

class DynamicRepositories extends Repositories
{
    /**
     * @return FooEntityRepository|\Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooEntityRepository
     */
    public function getFooEntityRepository(): \Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooEntityRepository
    {
        return $this->loadAndGetRepository(
            'Anytime\ORM\Tests\Stub\User\EntityRepository\FooEntityRepository',
            'Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooEntityRepository',
            'foo',
            'Anytime\ORM\Tests\Stub\Generated\Entity\Foo'
        );
    }

    /**
     * @return FooCompositeEntityRepository|\Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooCompositeEntityRepository
     */
    public function getFooCompositeEntityRepository(): \Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooCompositeEntityRepository
    {
        return $this->loadAndGetRepository(
            'Anytime\ORM\Tests\Stub\User\EntityRepository\FooCompositeEntityRepository',
            'Anytime\ORM\Tests\Stub\Generated\EntityManager\DefaultRepository\FooCompositeEntityRepository',
            'foo',
            'Anytime\ORM\Tests\Stub\Generated\Entity\FooComposite'
        );
    }
}
