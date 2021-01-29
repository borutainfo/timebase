<?php

use Boruta\Timebase\Common\Constant\SearchStrategyConstant;
use Boruta\Timebase\Timebase;

include 'vendor/autoload.php';

$timebase = new Timebase(__DIR__ . '/database/');
//$timebase->insert()
//    ->storage(['test'])
//    ->set(['asd'])
//    ->execute();

var_dump($timebase->search()->storage(['test'])->all()->execute());

//var_dump($random);