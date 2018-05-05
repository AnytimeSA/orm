<?php

namespace DVE\EntityORM\Hydrator;

use DVE\EntityORM\EntityManager\Entity;

interface EntityHydratorInterface
{
    /**
     * @param Entity $entity
     * @param array $data
     * @return EntityHydratorInterface
     */
    public function hydrate(Entity $entity, array $data): EntityHydratorInterface;
}