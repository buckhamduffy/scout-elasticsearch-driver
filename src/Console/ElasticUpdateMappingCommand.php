<?php

namespace ScoutElastic\Console;

use LogicException;
use ScoutElastic\Migratable;
use Illuminate\Console\Command;
use ScoutElastic\Payloads\TypePayload;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Console\Features\RequiresModelArgument;

class ElasticUpdateMappingCommand extends Command
{
	use RequiresModelArgument;

	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $name = 'elastic:update-mapping';

	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $description = 'Update a model mapping';

	/**
	 * Handle the command.
	 */
	public function handle(): void
	{
		if (!$model = $this->getModel()) {
			return;
		}

		$configurator = $model->getIndexConfigurator();

		$mapping = array_merge_recursive(
			$configurator->getDefaultMapping(),
			$model->getMapping()
		);

		if (empty($mapping)) {
			throw new LogicException('Nothing to update: the mapping is not specified.');
		}

		$payload = (new TypePayload($model))
			->set('body.' . $model->searchableAs(), $mapping)
			->set('include_type_name', 'true');

		if (in_array(Migratable::class, class_uses_recursive($configurator))) {
			$payload->useAlias('write');
		}

		ElasticClient::indices()
			->putMapping($payload->get());

		$this->info(sprintf(
			'The %s mapping was updated!',
			$model->searchableAs()
		));
	}
}
