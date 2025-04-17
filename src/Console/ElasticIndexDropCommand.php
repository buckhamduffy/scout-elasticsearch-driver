<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Facades\ElasticClient;
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

		if (method_exists($configurator, 'getWriteAlias')) {
			$this->delete($configurator->getWriteAlias());
		}

		$this->delete($configurator->getName());
	}

	protected function delete(string $indexName): void
	{
		$indices = ElasticClient::indices();

		$isAlias = $indices->existsAlias(['name' => $indexName]);
		if ($isAlias) {
			return;
		}

		$hasIndex = $indices->exists(['index' => $indexName]);
		if (!$hasIndex) {
			return;
		}

		$indices->delete(['index' => $indexName]);
		$this->info(sprintf('The index %s was deleted!', $indexName));
	}
}
