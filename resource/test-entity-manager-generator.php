<?php

namespace Resource\Test;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;
use DVE\EntityORM\Generator\EntityGenerator\MySqlTableStructureRetriever;
use DVE\EntityORM\Generator\EntityManagerGenerator\EntityManagerGenerator;

require_once __DIR__ . '/../vendor/autoload.php';

$host = 'localhost';
$port = '3306';
$db = 'dbname';
$user = 'user';
$pass = 'pass';

$pdo = new \PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);

$entityManagerGenerator = new EntityManagerGenerator(
    new SnakeToCamelCaseStringConverter(),
    new MySqlTableStructureRetriever($pdo),
    __DIR__ . '/dynamic-entity-manager/',
    'My\\EntityManager\\Name\\Space',
    __DIR__ . '/user-repositories/',
    'My\\Repository\\Name\\Space',
    'My\\Name\\Space'
);

$entityManagerGenerator->generate();