<?php

namespace DVE\EntityORM\Generator\EntityManagerGenerator;

interface EntityManagerGeneratorInterface
{
    public function generate(array $tableList = []);
}
