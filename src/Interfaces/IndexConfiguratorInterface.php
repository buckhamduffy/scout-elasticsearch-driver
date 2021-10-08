<?php

namespace ScoutElastic\Interfaces;

interface IndexConfiguratorInterface
{

	/**
	 * @return mixed[]
	 * @deprecated
	 */
	public function getDefaultMapping(): array;

	/**
	 * Get the settings.
	 *
	 * @return mixed[]
	 */
	public function getSettings(): array;


	/**
	 * Get the name.
	 */
	public function getName(): string;

}
