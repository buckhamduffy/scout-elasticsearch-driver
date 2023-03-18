<?php

namespace ScoutElastic\Indexers;

use ScoutElastic\Migratable;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\DocumentPayload;
use Illuminate\Database\Eloquent\Collection;
use ScoutElastic\Interfaces\IndexerInterface;

class SingleIndexer implements IndexerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function update(Collection $models): void
	{
		$models->each(function($model) {
			if ($model::usesSoftDelete() && config('scout.soft_delete', false)) {
				$model->pushSoftDeleteMetadata();
			}

			$modelData = array_merge(
				$model->toSearchableArray(),
				$model->scoutMetadata()
			);

			if (empty($modelData)) {
				return true;
			}

			$indexConfigurator = $model->getIndexConfigurator();

			$payload = (new DocumentPayload($model))
				->set('body', $modelData);

			if (in_array(Migratable::class, class_uses_recursive($indexConfigurator))) {
				$payload->useAlias('write');
			}

			if ($documentRefresh = config('scout_elastic.document_refresh')) {
				$payload->set('refresh', $documentRefresh);
			}

			ElasticClient::index($payload->get());
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(Collection $models): void
	{
		$models->each(function($model): void {
			$payload = new DocumentPayload($model);

			if ($documentRefresh = config('scout_elastic.document_refresh')) {
				$payload->set('refresh', $documentRefresh);
			}

			$payload->set('client.ignore', 404);

			ElasticClient::delete($payload->get());
		});
	}
}
