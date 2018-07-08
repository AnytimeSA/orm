<?php

namespace Resource\Test;

use DVE\EntityORM\Converter\SnakeToCamelCaseStringConverter;
use DVE\EntityORM\Generator\EntityGenerator\MySqlEntityGenerator;
use DVE\EntityORM\Generator\EntityGenerator\MySqlTableStructureRetriever;

require_once __DIR__ . '/../vendor/autoload.php';

$host = 'localhost';
$port = '3306';
$db = 'dbname';
$user = 'user';
$pass = 'pass';

$pdo = new \PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);

$entityGenerator = new MySqlEntityGenerator(
    new SnakeToCamelCaseStringConverter(),
    new MySqlTableStructureRetriever($pdo),
    __DIR__ . '/entities/',
    'My\\Name\\Space'
);

echo $entityGenerator->generate();