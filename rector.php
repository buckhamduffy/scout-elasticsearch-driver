<?php

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
	$containerConfigurator->import(__DIR__ . '/vendor/buckhamduffy/coding-standards/rector.php');

	// get parameters
	$parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [getcwd() . '/src']);
};
