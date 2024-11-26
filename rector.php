<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;


return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->phpVersion(\Rector\ValueObject\PhpVersion::PHP_84);
    $rectorConfig->disableParallel();

    $rectorConfig->paths([
        __DIR__ . '/src' ,
        __DIR__ . '/tests' ,
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84,
        SymfonySetList::SYMFONY_64,
        DoctrineSetList::DOCTRINE_BUNDLE_210,
        DoctrineSetList::DOCTRINE_ORM_213,
        DoctrineSetList::DOCTRINE_ORM_214,
        DoctrineSetList::DOCTRINE_ORM_25,
        DoctrineSetList::DOCTRINE_ORM_29,
    ]);
    $rectorConfig->skip([
        ClosureToArrowFunctionRector::class,
    ]);
};
