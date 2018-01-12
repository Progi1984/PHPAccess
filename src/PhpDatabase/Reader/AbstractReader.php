<?php

namespace Progi1984\PhpDatabase\Reader;

use Progi1984\PhpDatabase\PhpDatabase;

abstract class AbstractReader
{
    /**
     * @param string $filename
     * @return PhpDatabase
     */
    abstract public function readFile($filename);
}