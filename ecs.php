<?php

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
	$config->import(__DIR__ . '/vendor/buckhamduffy/coding-standards/ecs.php');
    $config->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/config',
    ]);
};
