<?php

namespace ScoutElastic\Console;

use ScoutElastic\Migratable;
use Illuminate\Console\Command;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;
use ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;

class ElasticIndexCreateCommand extends Command
{
	use RequiresIndexConfiguratorArgument;

	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $name = 'elastic:create-index';

	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $description = 'Create an Elasticsearch index';

	/**
	 * Create an index.
	 */
	protected function createIndex(): void
	{
		$configurator = $this->getIndexConfigurator();

		$payload = (new IndexPayload($configurator))
			->setIfNotEmpty('body.settings', $configurator->getSettings())
			->get();

		ElasticClient::indices()
			->create($payload);

		$this->info(sprintf(
			'The %s index was created!',
			$configurator->getName()
		));
	}

	/**
	 * Create an write alias.
	 */
	protected function createWriteAlias(): void
	{
		$configurator = $this->getIndexConfigurator();

		if (!in_array(Migratable::class, class_uses_recursive($configurator))) {
			return;
		}

		$payload = (new IndexPayload($configurator))
			->set('name', $configurator->getWriteAlias())
			->get();

		ElasticClient::indices()
			->putAlias($payload);

		$this->info(sprintf(
			'The %s alias for the %s index was created!',
			$configurator->getWriteAlias(),
			$configurator->getName()
		));
	}

	/**
	 * Handle the command.
	 */
	public function handle(): void
	{
		$this->createIndex();

		$this->createWriteAlias();
	}
}
