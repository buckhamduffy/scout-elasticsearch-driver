<?php

namespace ScoutElastic;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Laravel\Scout\EngineManager;
use Illuminate\Support\Facades\Config;
use ScoutElastic\Indexers\BulkIndexer;
use Illuminate\Support\ServiceProvider;
use ScoutElastic\Indexers\SingleIndexer;
use ScoutElastic\Console\SearchRuleMakeCommand;
use ScoutElastic\Console\ElasticIndexDropCommand;
use ScoutElastic\Console\AggregateRuleMakeCommand;
use ScoutElastic\Console\ElasticIndexCreateCommand;
use ScoutElastic\Console\ElasticIndexUpdateCommand;
use ScoutElastic\Console\ElasticMigrateModelCommand;
use ScoutElastic\Console\ElasticUpdateMappingCommand;
use ScoutElastic\Console\ElasticCompareModelCommand;
use ScoutElastic\Console\IndexConfiguratorMakeCommand;

class ScoutElasticServiceProvider extends ServiceProvider
{

	/**
	 * Boot the service provider.
	 *
	 * @return mixed
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../config/scout_elastic.php' => $this->app->configPath('scout_elastic.php'),
		]);

		$this->commands([
			// make commands
			IndexConfiguratorMakeCommand::class,
			AggregateRuleMakeCommand::class,
			SearchRuleMakeCommand::class,

			// elastic commands
			ElasticIndexCreateCommand::class,
			ElasticIndexUpdateCommand::class,
			ElasticIndexDropCommand::class,
			ElasticUpdateMappingCommand::class,
			ElasticMigrateModelCommand::class,
            ElasticCompareModelCommand::class,
		]);

		$this
			->app
			->make(EngineManager::class)
			->extend('elastic', function(): ElasticEngine {
				$indexerType = config('scout_elastic.indexer', 'single');
				$updateMapping = config('scout_elastic.update_mapping', true);

				switch ($indexerType) {
					case 'bulk':
						return new ElasticEngine(new BulkIndexer(), $updateMapping);
					case 'single':
					default:
						return new ElasticEngine(new SingleIndexer(), $updateMapping);
				}
			});
	}

	/**
	 * Register the service provider.
	 *
	 * @return mixed
	 */
	public function register(): void
	{
		$this
			->app
			->singleton('scout_elastic.client', function(): Client {
				$config = Config::get('scout_elastic.client');

				return ClientBuilder::fromConfig($config);
			});
	}

}
