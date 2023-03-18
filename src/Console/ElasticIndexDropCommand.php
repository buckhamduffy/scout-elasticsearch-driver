<?php

namespace ScoutElastic\Console;

use ScoutElastic\Migratable;
use Illuminate\Console\Command;
use ScoutElastic\Payloads\RawPayload;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Interfaces\IndexConfiguratorInterface;
use ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;

class ElasticIndexDropCommand extends Command
{
	use RequiresIndexConfiguratorArgument;

	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $name = 'elastic:drop-index';

	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $description = 'Drop an Elasticsearch index';

	/**
	 * Handle the command.
	 */
	public function handle(): void
	{
		$configurator = $this->getIndexConfigurator();
		$indexName = $this->resolveIndexName($configurator);

		$payload = (new RawPayload())
			->set('index', $indexName)
			->get();

		ElasticClient::indices()
			->delete($payload);

		$this->info(sprintf(
			'The index %s was deleted!',
			$indexName
		));
	}

	/**
	 * @return mixed|string|void
	 */
	protected function resolveIndexName(IndexConfiguratorInterface $configurator)
	{
		if (in_array(Migratable::class, class_uses_recursive($configurator))) {
			$payload = (new RawPayload())
				->set('name', $configurator->getWriteAlias())
				->get();

			$aliases = ElasticClient::indices()
				->getAlias($payload);

			return key($aliases);
		}

		return $configurator->getName();
	}
}
