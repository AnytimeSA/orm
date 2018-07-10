<?php

namespace Anytime\ORM\Generator\EntityManagerGenerator;

interface EntityManagerGeneratorInterface
{
    public function generate(array $tableList = []);
}
