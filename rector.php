<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->disableParallel();

    $rectorConfig->paths([
        __DIR__ . '/src' ,
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);
    $rectorConfig->skip([
        \Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector::class,
    ]);
};
