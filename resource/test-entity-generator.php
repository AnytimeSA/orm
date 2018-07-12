<?php

namespace Resource\Test;

use Anytime\ORM\Converter\SnakeToCamelCaseStringConverter;
use Anytime\ORM\Generator\EntityGenerator\EntityGenerator;
use Anytime\ORM\Generator\EntityGenerator\MySqlTableStructureRetriever;

require_once __DIR__ . '/../vendor/autoload.php';

$host = 'localhost';
$port = '3306';
$db = 'dbname';
$user = 'user';
$pass = 'pass';

$pdo = new \PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);

$entityGenerator = new EntityGenerator(
    new SnakeToCamelCaseStringConverter(),
    new MySqlTableStructureRetriever($pdo),
    __DIR__ . '/entities/',
    'My\\Name\\Space'
);

echo $entityGenerator->generate();