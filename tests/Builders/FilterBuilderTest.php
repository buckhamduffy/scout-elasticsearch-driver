<?php

namespace ScoutElastic\Tests\Builders;

use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Tests\AbstractTestCase;
use ScoutElastic\Tests\Dependencies\Model;

class FilterBuilderTest extends AbstractTestCase
{
	use Model;

	public function testCreationWithSoftDelete(): void
	{
		$builder = new FilterBuilder($this->mockModel(), null, true);

		$this->assertSame(
			[
				'must' => [
					[
						'term' => [
							'__soft_deleted' => 0,
						],
					],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testCreationWithoutSoftDelete(): void
	{
		$builder = new FilterBuilder($this->mockModel(), null, false);

		$this->assertSame(
			[
				'must'     => [],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereEq(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->where('foo', 0)
			->where('bar', '=', 1);

		$this->assertSame(
			[
				'must' => [
					['term' => ['foo' => 0]],
					['term' => ['bar' => 1]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereNotEq(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->where('foo', '!=', 'bar');

		$this->assertSame(
			[
				'must'     => [],
				'must_not' => [
					['term' => ['foo' => 'bar']],
				],
			],
			$builder->wheres
		);
	}

	public function testWhereGt(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->where('foo', '>', 0);

		$this->assertSame(
			[
				'must' => [
					['range' => ['foo' => ['gt' => 0]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereGte(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->where('foo', '>=', 0);

		$this->assertSame(
			[
				'must' => [
					['range' => ['foo' => ['gte' => 0]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereLt(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->where('foo', '<', 0);

		$this->assertSame(
			[
				'must' => [
					['range' => ['foo' => ['lt' => 0]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereLte(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->where('foo', '>=', 0);

		$this->assertSame(
			[
				'must' => [
					['range' => ['foo' => ['gte' => 0]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereIn(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereIn('foo', [0, 1]);

		$this->assertSame(
			[
				'must' => [
					['terms' => ['foo' => [0, 1]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereNotIn(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereNotIn('foo', [0, 1]);

		$this->assertSame(
			[
				'must'     => [],
				'must_not' => [
					['terms' => ['foo' => [0, 1]]],
				],
			],
			$builder->wheres
		);
	}

	public function testWhereBetween(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereBetween('foo', [0, 10]);

		$this->assertSame(
			[
				'must' => [
					['range' => ['foo' => ['gte' => 0, 'lte' => 10]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereNotBetween(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereNotBetween('foo', [0, 10]);

		$this->assertSame(
			[
				'must'     => [],
				'must_not' => [
					['range' => ['foo' => ['gte' => 0, 'lte' => 10]]],
				],
			],
			$builder->wheres
		);
	}

	public function testWhereExists(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereExists('foo');

		$this->assertSame(
			[
				'must' => [
					['exists' => ['field' => 'foo']],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereNotExists(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereNotExists('foo');

		$this->assertSame(
			[
				'must'     => [],
				'must_not' => [
					['exists' => ['field' => 'foo']],
				],
			],
			$builder->wheres
		);
	}

	public function testWhereMatch(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereMatch('tags', 'travel')
			->whereMatch('tags', 'spain');

		$this->assertEquals(
			[
				'must' => [
					['match' => ['tags' => 'travel']],
					['match' => ['tags' => 'spain']],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereNotMatch(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereNotMatch('tags', 'travel')
			->whereNotMatch('tags', 'spain');

		$this->assertEquals(
			[
				'must'     => [],
				'must_not' => [
					['match' => ['tags' => 'travel']],
					['match' => ['tags' => 'spain']],
				],
			],
			$builder->wheres
		);
	}

	public function testWhereRegexp(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereRegexp('foo', '.*')
			->whereRegexp('bar', '^test.*', 'EMPTY|NONE');

		$this->assertSame(
			[
				'must' => [
					['regexp' => ['foo' => ['value' => '.*', 'flags' => 'ALL']]],
					['regexp' => ['bar' => ['value' => '^test.*', 'flags' => 'EMPTY|NONE']]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhen(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->when(
				false,
				function(FilterBuilder $builder) {
					return $builder->where('case0', 0);
				}
			)
			->when(
				false,
				function(FilterBuilder $builder) {
					return $builder->where('case1', 1);
				},
				function(FilterBuilder $builder) {
					return $builder->where('case2', 2);
				}
			)
			->when(
				true,
				function(FilterBuilder $builder) {
					return $builder->where('case3', 3);
				}
			);

		$this->assertSame(
			[
				'must' => [
					['term' => ['case2' => 2]],
					['term' => ['case3' => 3]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereGeoDistance(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereGeoDistance('foo', [-20, 30], '10m');

		$this->assertSame(
			[
				'must' => [
					['geo_distance' => ['distance' => '10m', 'foo' => [-20, 30]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereGeoBoundingBox(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereGeoBoundingBox('foo', ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]);

		$this->assertSame(
			[
				'must' => [
					['geo_bounding_box' => ['foo' => ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereGeoPolygon(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->whereGeoPolygon('foo', [[-70, 40], [-80, 30], [-90, 20]]);

		$this->assertSame(
			[
				'must' => [
					['geo_polygon' => ['foo' => ['points' => [[-70, 40], [-80, 30], [-90, 20]]]]],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testWhereGeoShape(): void
	{
		$shape = [
			'type'        => 'circle',
			'radius'      => '1km',
			'coordinates' => [
				4.89994,
				52.37815,
			],
		];

		$relation = 'WITHIN';

		$builder = (new FilterBuilder($this->mockModel()))
			->whereGeoShape('foo', $shape, $relation);

		$this->assertSame(
			[
				'must' => [
					[
						'geo_shape' => [
							'foo' => [
								'shape'    => $shape,
								'relation' => $relation,
							],
						],
					],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testOrderBy(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->orderBy('foo')
			->orderBy('bar', 'DESC');

		$this->assertSame(
			[
				['foo' => 'asc'],
				['bar' => 'desc'],
			],
			$builder->orders
		);
	}

	public function testOrderRaw(): void
	{
		$orderRaw = [
			'_geo_distance' => [
				'coordinates' => [
					'lat' => 51.507351,
					'lon' => -0.127758,
				],
				'order' => 'asc',
				'unit'  => 'm',
			],
		];

		$builder = (new FilterBuilder($this->mockModel()))
			->orderRaw($orderRaw);

		$this->assertSame([$orderRaw], $builder->orders);
	}

	public function testWith(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->with('RelatedModel');

		$this->assertSame(
			'RelatedModel',
			$builder->with
		);
	}

	public function testFrom(): void
	{
		$builder = new FilterBuilder($this->mockModel());

		$this->assertNull(
			$builder->offset
		);

		$builder->from(100);

		$this->assertSame(
			100,
			$builder->offset
		);
	}

	public function testCollapse(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->collapse('foo');

		$this->assertSame(
			'foo',
			$builder->collapse
		);
	}

	public function testSelect(): void
	{
		$builder = (new FilterBuilder($this->mockModel()))
			->select(['foo', 'bar']);

		$this->assertSame(
			['foo', 'bar'],
			$builder->select
		);
	}

	public function testWithTrashed(): void
	{
		$builder = (new FilterBuilder($this->mockModel(), null, true))
			->withTrashed()
			->where('foo', 'bar');

		$this->assertSame(
			[
				'must' => [
					[
						'term' => [
							'foo' => 'bar',
						],
					],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testOnlyTrashed(): void
	{
		$builder = (new FilterBuilder($this->mockModel(), null, true))
			->onlyTrashed()
			->where('foo', 'bar');

		$this->assertSame(
			[
				'must' => [
					[
						'term' => [
							'__soft_deleted' => 1,
						],
					],
					[
						'term' => [
							'foo' => 'bar',
						],
					],
				],
				'must_not' => [],
			],
			$builder->wheres
		);
	}

	public function testMinScore(): void
	{
		$builder = (new FilterBuilder($this->mockModel()));

		$this->assertNull(
			$builder->minScore
		);

		$builder = (new FilterBuilder($this->mockModel()))
			->minScore(0.5);

		$this->assertSame(
			0.5,
			$builder->minScore
		);
	}
}
