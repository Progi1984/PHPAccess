<?php

include_once __DIR__.'../../vendor/autoload.php';

function hex_dump($data, $newline="<br />") {
    static $from = '';
    static $to = '';
    static $width = 16; # number of bytes per line
    static $pad = '.'; # padding for non-visible characters

    if ($from === '') {
        for ($i=0; $i<=0xFF; $i++) {
            $from .= chr($i);
            $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
        }
    }

    $hex = str_split(bin2hex($data), $width*2);
    $chars = str_split(strtr($data, $from, $to), $width);

    $offset = 0;
    foreach ($hex as $i => $line) {
        echo str_pad(dechex($offset), 6, '0', STR_PAD_LEFT).' : '
            . implode(' ', str_split($line,2))
            . ' [' . $chars[$i] . ']'
            . $newline;
        $offset += $width;
    }
}


use \Progi1984\PhpDatabase\PhpDatabase;

try {
    $oReader = PhpDatabase::createReader('Sqlite');
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}

$oReader->readFile(__DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'sampleNorthwind.sqlite');