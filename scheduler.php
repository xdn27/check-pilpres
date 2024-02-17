<?php require_once __DIR__.'/vendor/autoload.php';

use GO\Scheduler;

$scheduler = new Scheduler();

$scheduler->raw('./minicli kpu getdatatps p="JAWA BARAT"')->onlyOne();

$scheduler->run();
