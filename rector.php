<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonyLevelSetList;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->disableParallel();

    $rectorConfig->paths([
        __DIR__ . '/src' ,
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SymfonyLevelSetList::UP_TO_SYMFONY_63,
        DoctrineSetList::DOCTRINE_BUNDLE_210,
        DoctrineSetList::DOCTRINE_ORM_25,
    ]);
    $rectorConfig->skip([
        ClosureToArrowFunctionRector::class,
    ]);
};
