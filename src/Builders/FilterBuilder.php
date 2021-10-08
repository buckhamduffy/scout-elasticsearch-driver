<?php

namespace ScoutElastic\Builders;

use Closure;
use Exception;
use Laravel\Scout\Builder;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use ScoutElastic\ElasticEngine;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Interfaces\AggregateRuleInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as ModelCollection;

/**
 * @method ElasticEngine engine()
 */
class FilterBuilder extends Builder
{

	/**
	 * The condition array.
	 *
	 * @var array
	 */
	public $wheres = [
		'must'     => [],
		'must_not' => [],
	];

	/**
	 * The with array.
	 *
	 * @var array|string
	 */
	public $with;

	/**
	 * The offset.
	 */
	public ?int $offset = null;

	/**
	 * The collapse parameter.
	 */
	public ?string $collapse = null;

	/**
	 * The select array.
	 */
	public array $select = [];

	public array $aggregates = [];

	/**
	 * The min_score parameter.
	 */
	public ?float $minScore = null;

	/**
	 * Determines if the score should be returned with the model.
	 *
	 * @var bool - false
	 */
	public bool $withScores = false;

	/**
	 * FilterBuilder constructor.
	 *
	 * @param callable|null $callback
	 * @param bool $softDelete
	 * @return void
	 */
	public function __construct(Model $model, $callback = null, $softDelete = false)
	{
		$this->model = $model;
		$this->callback = $callback;

		if ($softDelete) {
			$this->wheres['must'][] = [
				'term' => [
					'__soft_deleted' => 0,
				],
			];
		}
	}

	/**
	 * Add a where condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html Term query
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
	 *
	 * Supported operators are =, &gt;, &lt;, &gt;=, &lt;=, &lt;&gt;
	 *
	 * @param string|Closure $field
	 * @param null $operator
	 * @param null $value
	 * @param string $boolean
	 * @return $this|FilterBuilder
	 */
	public function where($field, $operator = null, $value = null, $boolean = 'must'): self
	{
		if ($field instanceof Closure) {
			return $this->whereNested($field, $boolean);
		}

		[$value, $operator] = $this->prepareValueAndOperator(
			$value,
			$operator,
			func_num_args() === 2
		);

		switch ($operator) {
			case '=':
				$this->wheres[$boolean][] = [
					'term' => [
						$field => $value,
					],
				];
				break;

			case '>':
				$this->wheres[$boolean][] = [
					'range' => [
						$field => [
							'gt' => $value,
						],
					],
				];
				break;

			case '<':
				$this->wheres[$boolean][] = [
					'range' => [
						$field => [
							'lt' => $value,
						],
					],
				];
				break;

			case '>=':
				$this->wheres[$boolean][] = [
					'range' => [
						$field => [
							'gte' => $value,
						],
					],
				];
				break;

			case '<=':
				$this->wheres[$boolean][] = [
					'range' => [
						$field => [
							'lte' => $value,
						],
					],
				];
				break;

			case '!=':
			case '<>':
				$term = [
					'term' => [
						$field => $value,
					],
				];
				$this->setNegativeCondition($term, $boolean);
				break;
		}

		return $this;
	}

	/**
	 * @param $column
	 * @param null $operator
	 * @param null $value
	 * @return $this|\Illuminate\Database\Query\Builder
	 */
	public function orWhere(
		$column,
		string $operator = null,
		string $value = null
	): \ScoutElastic\Builders\FilterBuilder
	{
		[$value, $operator] = $this->prepareValueAndOperator(
			$value,
			$operator,
			func_num_args() === 2
		);

		return $this->where($column, $operator, $value, 'should');
	}

	public function whereNested(Closure $callback, string $boolean = 'must'): self
	{
		/** @var $filter FilterBuilder */
		call_user_func($callback, $filter = $this->model::search('*'));

		$payload = $filter->buildPayload();
		$this->wheres[$boolean][] = $payload[0]['body']['query']['bool']['filter'];

		return $this;
	}


	/**
	 * Adds Nested query
	 */
	public function whereHas(string $path, Closure $callback, string $boolean = 'must'): self
	{
		/** @var $filter FilterBuilder */
		call_user_func($callback, $filter = $this->model::search('*'));

		$payload = $filter->buildPayload();
		$this->wheres[$boolean][] = [
			'nested' => [
				'path'  => $path,
				'query' => $payload[0]['body']['query']['bool']['filter'],
			],
		];

		return $this;
	}

	/**
	 * Prepare the value and operator for a where clause.
	 *
	 *
	 * @return string[]
	 * @throws InvalidArgumentException
	 */
	public function prepareValueAndOperator(string $value, string $operator, bool $useDefault = false): array
	{
		if ($useDefault) {
			return [$operator, '='];
		}

		return [$value, $operator];
	}

	/**
	 * @param $condition
	 */
	public function setNegativeCondition($condition, string $boolean = 'must'): void
	{
		if ($boolean == 'should') {
			$cond['bool']['must_not'][] = $condition;

			$this->wheres[$boolean][] = $cond;
		} else {
			$this->wheres['must_not'][] = $condition;
		}
	}

	/**
	 * Add a whereIn condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
	 *
	 * @param string $field
	 */
	public function whereIn($field, array $value, string $boolean = 'must'): self
	{
		$this->wheres[$boolean][] = [
			'terms' => [
				$field => $value,
			],
		];

		return $this;
	}

	/**
	 * @param $field
	 */
	public function orWhereIn($field, array $value): self
	{
		return $this->whereIn($field, $value, 'should');
	}

	/**
	 * Add a whereNotIn condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
	 */
	public function whereNotIn(string $field, array $value, string $boolean = 'must'): self
	{
		$term = [
			'terms' => [
				$field => $value,
			],
		];
		$this->setNegativeCondition($term, $boolean);

		return $this;
	}

	/**
	 * @param $field
	 */
	public function orWhereNotIn(string $field, array $value): self
	{
		return $this->whereNotIn($field, $value, 'should');
	}

	/**
	 * Add a whereBetween condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
	 */
	public function whereBetween(string $field, array $value, string $boolean = 'must'): self
	{
		$this->wheres[$boolean][] = [
			'range' => [
				$field => [
					'gte' => $value[0],
					'lte' => $value[1],
				],
			],
		];

		return $this;
	}

	/**
	 * @param $field
	 */
	public function orWhereBetween(string $field, array $value): self
	{
		return $this->whereBetween($field, $value);
	}

	/**
	 * Add a whereNotBetween condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
	 */
	public function whereNotBetween(string $field, array $value, string $boolean = 'must'): self
	{
		$term = [
			'range' => [
				$field => [
					'gte' => $value[0],
					'lte' => $value[1],
				],
			],
		];
		$this->setNegativeCondition($term, $boolean);

		return $this;
	}

	/**
	 * @param $field
	 */
	public function orWhereNotBetween(string $field, array $value): self
	{
		return $this->whereNotBetween($field, $value, 'should');
	}

	/**
	 * Add a whereExists condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
	 */
	public function whereExists(string $field, string $boolean = 'must'): self
	{
		$this->wheres[$boolean][] = [
			'exists' => [
				'field' => $field,
			],
		];

		return $this;
	}

	/**
	 * @param $field
	 */
	public function orWhereExists(string $field): self
	{
		return $this->whereExists($field, 'should');
	}

	/**
	 * Add a whereNotExists condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
	 */
	public function whereNotExists(string $field, string $boolean = 'must'): self
	{
		$term = [
			'exists' => [
				'field' => $field,
			],
		];
		$this->setNegativeCondition($term, $boolean);

		return $this;
	}

	/**
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
	 *
	 * @return $this|FilterBuilder
	 */
	public function orWhereNotExists(string $field): self
	{
		return $this->whereNotExists($field, 'should');
	}

	/**
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html Match query
	 */
	public function whereMatch(string $field, string $value, string $boolean = 'must'): self
	{
		$this->wheres[$boolean][] = [
			'match' => [
				$field => $value,
			],
		];

		return $this;
	}

	/**
	 * @param $field
	 * @param $value
	 */
	public function orWhereMatch(string $field, string $value): self
	{
		return $this->whereMatch($field, $value, 'should');
	}

	/**
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html Match query
	 */
	public function whereNotMatch(string $field, string $value, string $boolean = 'must'): self
	{
		$term = [
			'match' => [
				$field => $value,
			],
		];
		$this->setNegativeCondition($term, $boolean);

		return $this;
	}

	/**
	 * @param $field
	 * @param $value
	 */
	public function orWhereNotMatch(string $field, string $value): self
	{
		return $this->whereNotMatch($field, $value, 'should');
	}

	/**
	 * Add a whereRegexp condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html Regexp query
	 */
	public function whereRegexp(string $field, string $value, string $flags = 'ALL', string $boolean = 'must'): self
	{
		$this->wheres[$boolean][] = [
			'regexp' => [
				$field => [
					'value' => $value,
					'flags' => $flags,
				],
			],
		];

		return $this;
	}

	public function orWhereRegexp(string $field, string $value, string $flags = 'ALL'): self
	{
		return $this->whereRegexp($field, $value, $flags, 'should');
	}

	/**
	 * Add a whereGeoDistance condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html Geo
	 *     distance query
	 *
	 * @param string|array $value
	 * @param int|string $distance
	 */
	public function whereGeoDistance(string $field, $value, $distance, string $boolean = 'must'): self
	{
		$this->wheres[$boolean][] = [
			'geo_distance' => [
				'distance' => $distance,
				$field     => $value,
			],
		];

		return $this;
	}

	/**
	 * @param $field
	 * @param $value
	 * @param $distance
	 */
	public function orWhereGeoDistance(string $field, $value, $distance): self
	{
		return $this->whereGeoDistance($field, $value, $distance, 'should');
	}

	/**
	 * Add a whereGeoBoundingBox condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-query.html Geo
	 *     bounding box query
	 */
	public function whereGeoBoundingBox(string $field, array $value, string $boolean = 'must'): self
	{
		$this->wheres[$boolean][] = [
			'geo_bounding_box' => [
				$field => $value,
			],
		];

		return $this;
	}

	/**
	 * @param $field
	 * @param $value
	 */
	public function orWhereGeoBoundingBox(string $field, array $value): self
	{
		return $this->whereGeoBoundingBox($field, $value, 'should');
	}

	/**
	 * Add a whereGeoPolygon condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-query.html Geo
	 *     polygon query
	 */
	public function whereGeoPolygon(string $field, array $points, string $boolean = 'must'): self
	{
		$this->wheres[$boolean][] = [
			'geo_polygon' => [
				$field => [
					'points' => $points,
				],
			],
		];

		return $this;
	}

	/**
	 * @param $field
	 */
	public function orWhereGeoPolygon(string $field, array $points): self
	{
		return $this->whereGeoPolygon($field, $points, 'should');
	}

	/**
	 * Add a whereGeoShape condition.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html Querying Geo
	 *     Shapes
	 */
	public function whereGeoShape(
		string $field,
		array $shape,
		string $relation = 'INTERSECTS',
		string $boolean = 'must'
	): self
	{
		$this->wheres[$boolean][] = [
			'geo_shape' => [
				$field => [
					'shape'    => $shape,
					'relation' => $relation,
				],
			],
		];

		return $this;
	}

	/**
	 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/returning-only-agg-results.html
	 *
	 * @return $this
	 */
	public function aggregate(int $size = 0): array
	{
		$this->take($size);

		return $this->engine()->search($this);
	}

	/**
	 * @return $this
	 */
	public function sum(string $field): float
	{
		$this->aggregates = [
			$field => [
				'sum' => [
					'field' => $field,
				],
			],
		];

		$result = $this->aggregate();

		return $result['aggregations'][$field]['value'];
	}


	/**
	 * Adds rule to the aggregate rules of the builder.
	 * @param AggregateRuleInterface|Closure $rule
	 */
	public function addAggregate($rule): self
	{
		if ($rule instanceof AggregateRuleInterface) {
			$ruleEntity = new $rule();
			if ($aggregatePayload = $ruleEntity->buildAggregatePayload()) {
				$this->aggregates = array_merge($this->aggregates, $aggregatePayload);
			}

			return $this;
		}

		if ($rule instanceof Closure) {
			$ruleEntity = call_user_func($rule);
			if (is_array($ruleEntity)) {
				$this->aggregates = array_merge($this->aggregates, $ruleEntity);;
			}
		}

		return $this;
	}

	/**
	 * @param $field
	 */
	public function orWhereGeoShape(string $field, array $shape, string $relation = 'INTERSECTS'): self
	{
		return $this->whereGeoShape($field, $shape, $relation, 'should');
	}

	/**
	 * Add a orderBy clause.
	 *
	 * @param string $field
	 * @param string $direction
	 */
	public function orderBy($field, $direction = 'asc'): self
	{
		$this->orders[] = [
			$field => strtolower($direction) === 'asc' ? 'asc' : 'desc',
		];

		return $this;
	}

	/**
	 * Add a raw order clause.
	 */
	public function orderRaw(array $payload): self
	{
		$this->orders[] = $payload;

		return $this;
	}

	/**
	 * Explain the request.
	 *
	 * @return mixed[]
	 */
	public function explain(): array
	{
		return $this
			->engine()
			->explain($this);
	}

	/**
	 * Profile the request.
	 *
	 * @return mixed[]
	 */
	public function profile(): array
	{
		return $this
			->engine()
			->profile($this);
	}

	/**
	 * Build the payload.
	 *
	 * @return mixed[]
	 */
	public function buildPayload(): Collection
	{
		return $this
			->engine()
			->buildSearchQueryPayloadCollection($this);
	}

	/**
	 * @return mixed|mixed[]|string
	 * @throws Exception
	 */
	public function toQuery(bool $json = false)
	{
		$queries = $this->buildPayload()->map(fn($query) => $query['body']);

		if ($queries->isEmpty()) {
			throw new Exception('no query found');
		}

		if ($queries->count() === 1) {
			return $json ? json_encode($queries->first()) : $queries->first();
		}

		return $json ? $queries->toJson() : $queries->toArray();
	}

	/**
	 * Eager load some some relations.
	 *
	 * @param array|string $relations
	 */
	public function with($relations): self
	{
		$this->with = $relations;

		return $this;
	}

	/**
	 * Set the query offset.
	 */
	public function from(int $offset): self
	{
		$this->offset = $offset;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(): ModelCollection
	{
		$collection = parent::get();

		if (isset($this->with) && $collection->count() > 0) {
			$collection->load($this->with);
		}

		return $collection;
	}

	public function getRaw(): Collection
	{
		$results = $this->engine()->search($this);

		if ($results['hits']['total'] === 0) {
			return new Collection();
		}

		return (new Collection($results['hits']['hits']))
			->map(fn($row) => array_merge(
				$row['_source'],
				[
					'score' => $row['_score'],
				]
			));
	}

	/**
	 * Bypasses Eloquent and directly hydrates the Models
	 */
	public function hydrate(): Collection
	{
		$className = get_class($this->model);

		return $this->getRaw()->map(fn($row) => (new $className())->forceFill($row));
	}

	/**
	 * {@inheritdoc}
	 */
	public function paginate($perPage = null, $pageName = 'page', $page = null): LengthAwarePaginator
	{
		$paginator = parent::paginate($perPage, $pageName, $page);

		if (isset($this->with) && $paginator->total() > 0) {
			$paginator
				->getCollection()
				->load($this->with);
		}

		return $paginator;
	}

	/**
	 * Collapse by a field.
	 */
	public function collapse(string $field): self
	{
		$this->collapse = $field;

		return $this;
	}

	/**
	 * Select one or many fields.
	 *
	 * @param mixed $fields
	 */
	public function select($fields): self
	{
		$this->select = array_merge(
			$this->select,
			Arr::wrap($fields)
		);

		return $this;
	}

	/**
	 * Set the min_score on the filter.
	 */
	public function minScore(float $score): self
	{
		$this->minScore = $score;

		return $this;
	}

	/**
	 * Set the withScores property.
	 *
	 * @param bool $withScores - true
	 */
	public function withScores(bool $withScores = true): self
	{
		$this->withScores = $withScores;

		return $this;
	}

	/**
	 * Get the count.
	 */
	public function count(): int
	{
		return $this
			->engine()
			->count($this);
	}

	/**
	 * {@inheritdoc}
	 */
	public function withTrashed(): self
	{
		$this->wheres['must'] = collect($this->wheres['must'])
			->filter(fn($item): bool => Arr::get($item, 'term.__soft_deleted') !== 0)
			->values()
			->all();

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onlyTrashed()
	{
		return tap($this->withTrashed(), function(): void {
			$this->wheres['must'][] = ['term' => ['__soft_deleted' => 1]];
		});
	}


}
