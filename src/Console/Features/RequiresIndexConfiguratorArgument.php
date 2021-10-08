<?php

namespace ScoutElastic\Console\Features;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use ScoutElastic\Interfaces\IndexConfiguratorInterface;

trait RequiresIndexConfiguratorArgument
{
	/**
	 * Get the index configurator.
	 */
	protected function getIndexConfigurator(): IndexConfiguratorInterface
	{
		$configuratorClass = trim($this->argument('index-configurator'));

		$configuratorInstance = new $configuratorClass();

		if (!($configuratorInstance instanceof IndexConfiguratorInterface)) {
			throw new InvalidArgumentException(sprintf(
				'The class %s must implement %s.',
				$configuratorClass,
				IndexConfiguratorInterface::class
			));
		}

		return new $configuratorClass();
	}

	/**
	 * Get the arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			[
				'index-configurator',
				InputArgument::REQUIRED,
				'The index configurator class',
			],
		];
	}
}
