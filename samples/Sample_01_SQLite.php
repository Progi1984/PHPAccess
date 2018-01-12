<?php

include_once __DIR__.'../../vendor/autoload.php';

use \Progi1984\PhpDatabase\PhpDatabase;

try {
    $oReader = PhpDatabase::createReader('Sqlite');
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}

$oReader->readFile(__DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'sampleNorthwind.sqlite');