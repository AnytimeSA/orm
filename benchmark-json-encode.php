<?php

echo hash('fnv164',  print_r([1000, 50], true));exit;

$sql = "SELECT * FROM orders WHERE date_created > ? AND user_id = ?";
$array = ['2016-01-01 00:00:00', 25];

$t1 = microtime(true);

for($i=0; $i<100000; $i++) {
    //$key = hash('fnv164', $sql) . hash('fnv164', var_export($array, true)); // 0.15
    //$key = hash('fnv164', $sql) . hash('fnv164', serialize($array)); // 0.9
    $key = hash('fnv164', $sql) . hash('fnv164', print_r($array, true)); // 0.75
    //$key = hash('fnv164', $sql) . hash('fnv164', json_encode($array)); // 0.99

}

$t2 = microtime(true);

echo $i . ' keys generated like "'.$key.'" Done in '.($t2 - $t1) . "\n";