<?php

namespace ScoutElastic;

use Exception;
use Illuminate\Support\Arr;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;
use Laravel\Scout\Searchable as SourceSearchable;
use ScoutElastic\Interfaces\IndexConfiguratorInterface;

trait Searchable
{

	use SourceSearchable {
		SourceSearchable::getScoutKeyName as sourceGetScoutKeyName;
	}

	/**
	 * The highlights.
	 */
	private ?Highlight $highlight = null;

	/**
	 * The score returned from elasticsearch.
	 */
	public ?float $_score = null;

	/**
	 * Get the index configurator.
	 *
	 * @throws Exception
	 */
	public function getIndexConfigurator(): IndexConfiguratorInterface
	{
		static $indexConfigurator;

		if (!$indexConfigurator) {
			if (!isset($this->indexConfigurator) || empty($this->indexConfigurator)) {
				throw new Exception(sprintf(
					'An index configurator for the %s model is not specified.',
					self::class
				));
			}

			$indexConfiguratorClass = $this->indexConfigurator;
			$indexConfigurator = new $indexConfiguratorClass();
		}

		return $indexConfigurator;
	}

	/**
	 * Get the mapping.
	 *
	 * @return array
	 */
	public function getMapping()
	{
		$mapping = $this->mapping ?? [];

		if ($this::usesSoftDelete() && config('scout.soft_delete', false)) {
			Arr::set($mapping, 'properties.__soft_deleted', ['type' => 'integer']);
		}

		return $mapping;
	}

	/**
	 * Get the search rules.
	 *
	 * @return array
	 */
	public function getSearchRules()
	{
		return isset($this->searchRules) && count($this->searchRules) > 0 ? $this->searchRules : [SearchRule::class];
	}

	/**
	 * Execute the search.
	 *
	 * @param callable|null $callback
	 * @return FilterBuilder|SearchBuilder|void
	 */
	public static function search(string $query, $callback = null)
	{
		$softDelete = static::usesSoftDelete() && config('scout.soft_delete', false);

		if ($query === '*') {
			return new FilterBuilder(new static(), $callback, $softDelete);
		}

		return new SearchBuilder(new static(), $query, $callback, $softDelete);
	}

	/**
	 * Execute a raw search.
	 *
	 * @return array
	 */
	public static function searchRaw(array $query)
	{
		$model = new static();

		return $model->searchableUsing()
			->searchRaw($model, $query);
	}

	/**
	 * Set the highlight attribute.
	 *
	 * @return void
	 */
	public function setHighlightAttribute(Highlight $value)
	{
		$this->highlight = $value;
	}

	/**
	 * Get the highlight attribute.
	 *
	 * @return Highlight|null
	 */
	public function getHighlightAttribute()
	{
		return $this->highlight;
	}

	/**
	 * Get the key name used to index the model.
	 *
	 * @return mixed
	 */
	public function getScoutKeyName()
	{
		return $this->getKeyName();
	}

	/**
	 * @return string[]|mixed
	 */
	public function getAggregateRules()
	{
		return isset($this->aggregateRules) && count($this->aggregateRules) > 0 ? $this->aggregateRules : [AggregateRule::class];
	}

}
