<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Console\Features\RequiresModelArgument;

class ElasticCompareModelCommand extends Command
{
	use RequiresModelArgument {
		RequiresModelArgument::getArguments as private modelArgument;
	}

	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $name = 'elastic:compare-model';

	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $description = 'Compare the count of the model with the count of the index';

	/**
	 * Handle the command.
	 */
	public function handle(): void
	{
		$sourceModel = $this->getModel();
		$sourceIndexConfigurator = $sourceModel->getIndexConfigurator();

		$modelCount = $sourceModel->count();
		$indexCount = ElasticClient::count([
			'index' => $sourceIndexConfigurator->getName(),
			'body'  => []
		])['count'] ?? 0;

		$this->table(
			['Model', 'Index', 'Difference'],
			[[$modelCount, $indexCount, abs($modelCount - $indexCount)]]
		);
	}
}
