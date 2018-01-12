<?php

namespace Progi1984\PhpDatabase;

use Progi1984\PhpDatabase\Reader\AbstractReader;

class PhpDatabase
{
    /**
     * @param string $reader
     * @return AbstractReader
     * @throws \Exception
     */
    static function createReader($reader)
    {
        $class = __NAMESPACE__ . '\Reader\\' . ucfirst(strtolower($reader));
        if (!class_exists($class)) {
            throw new \Exception('Reader ' . $reader . ' doesn\'t exist');
        }
        return new $class();
    }
}