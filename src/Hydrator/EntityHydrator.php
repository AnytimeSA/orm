<?php

namespace DVE\EntityORM\Hydrator;

use DVE\EntityORM\EntityManager\Entity;

class EntityHydrator implements  EntityHydratorInterface
{
    /**
     * @inheritdoc
     */
    public function hydrate(Entity $entity, array $data): EntityHydratorInterface
    {
        $entity->setData($data);
        return $this;
    }
}