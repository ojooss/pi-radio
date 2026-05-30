<?php declare(strict_types=1);

namespace App\Tests;

use ReflectionClass;
use ReflectionException;

trait PHPUnitUtils
{

    /**
     * @param array<mixed> $args
     * @throws ReflectionException
     */
    public static function callPrivateMethod(object $object, string $name, array $args = []): mixed
    {
        $class = new ReflectionClass($object);
        $method = $class->getMethod($name);
        return $method->invokeArgs($object, $args);
    }

}
