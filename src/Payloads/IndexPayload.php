<?php

namespace ScoutElastic\Payloads;

use Exception;
use ScoutElastic\Payloads\Features\HasProtectedKeys;
use ScoutElastic\Interfaces\IndexConfiguratorInterface;

class IndexPayload extends RawPayload
{

	use HasProtectedKeys;

	/**
	 * The protected keys.
	 */
	protected array $protectedKeys = [
		'index',
	];

	/**
	 * The index configurator.
	 */
	protected IndexConfiguratorInterface $indexConfigurator;

	/**
	 * IndexPayload constructor.
	 *
	 * @return void
	 */
	public function __construct(IndexConfiguratorInterface $indexConfigurator)
	{
		$this->indexConfigurator = $indexConfigurator;

		$this->payload['index'] = $indexConfigurator->getName();
	}

	/**
	 * Use an alias.
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function useAlias(string $alias)
	{
		$aliasGetter = 'get' . ucfirst($alias) . 'Alias';

		if (!method_exists($this->indexConfigurator, $aliasGetter)) {
			throw new Exception(sprintf(
				"The index configurator %s doesn't have getter for the %s alias.",
				get_class($this->indexConfigurator),
				$alias
			));
		}

		$this->payload['index'] = call_user_func([$this->indexConfigurator, $aliasGetter]);

		return $this;
	}

}
