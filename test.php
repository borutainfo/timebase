<?php

use Boruta\Timebase\Timebase;

include 'vendor/autoload.php';

$timebase = new Timebase(__DIR__ . '/database/');
//$timebase->insert()
//    ->storage(['test','12fF-3'])
//    ->set(['jakiesdane'])
//    ->execute();

print_r($timebase->query()
    ->storage(['test','12fF-3'])
    ->timestamp($random = 1611256524)
    ->approximate()
    ->execute());

var_dump($random);