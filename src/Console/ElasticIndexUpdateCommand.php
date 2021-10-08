<?php

namespace ScoutElastic\Console;

use Exception;
use LogicException;
use ScoutElastic\Migratable;
use Illuminate\Console\Command;
use ScoutElastic\Payloads\RawPayload;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;
use ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;

class ElasticIndexUpdateCommand extends Command
{
	use RequiresIndexConfiguratorArgument;

	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $name = 'elastic:update-index';

	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $description = 'Update settings and mappings of an Elasticsearch index';

	/**
	 * Update the index.
	 *
	 * @throws Exception
	 */
	protected function updateIndex(): void
	{
		$configurator = $this->getIndexConfigurator();

		$indexPayload = (new IndexPayload($configurator))->get();

		$indices = ElasticClient::indices();

		if (!$indices->exists($indexPayload)) {
			throw new LogicException(sprintf(
				"Index %s doesn't exist",
				$configurator->getName()
			));
		}

		try {
			$indices->close($indexPayload);

			if ($settings = $configurator->getSettings()) {
				$indexSettingsPayload = (new IndexPayload($configurator))
					->set('body.settings', $settings)
					->get();

				$indices->putSettings($indexSettingsPayload);
			}

			$indices->open($indexPayload);
		} catch (Exception $exception) {
			$indices->open($indexPayload);

			throw $exception;
		}

		$this->info(sprintf(
			'The index %s was updated!',
			$configurator->getName()
		));
	}

	/**
	 * Create a write alias.
	 */
	protected function createWriteAlias(): void
	{
		$configurator = $this->getIndexConfigurator();

		if (!in_array(Migratable::class, class_uses_recursive($configurator))) {
			return;
		}

		$indices = ElasticClient::indices();

		$existsPayload = (new RawPayload())
			->set('name', $configurator->getWriteAlias())
			->get();

		if ($indices->existsAlias($existsPayload)) {
			return;
		}

		$putPayload = (new IndexPayload($configurator))
			->set('name', $configurator->getWriteAlias())
			->get();

		$indices->putAlias($putPayload);

		$this->info(sprintf(
			'The %s alias for the %s index was created!',
			$configurator->getWriteAlias(),
			$configurator->getName()
		));
	}

	/**
	 * Handle the command.
	 *
	 * @var string
	 */
	public function handle(): void
	{
		$this->updateIndex();

		$this->createWriteAlias();
	}
}
