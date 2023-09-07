<?php declare(strict_types=1);

namespace App\Tests;

use ReflectionClass;
use ReflectionException;

trait PHPUnitUtils
{

    /**
     * @param $object
     * @param $name
     * @param array $args
     * @return mixed
     * @throws ReflectionException
     */
    public static function callPrivateMethod($object, $name, array $args = []): mixed
    {
        $class = new ReflectionClass($object);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

}
