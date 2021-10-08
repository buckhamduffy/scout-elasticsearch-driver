<?php

namespace ScoutElastic;

use ScoutElastic\Builders\SearchBuilder;

class SearchRule
{
	/**
	 * The builder.
	 */
	protected SearchBuilder $builder;

	/**
	 * SearchRule constructor.
	 *
	 * @return void
	 */
	public function __construct(SearchBuilder $builder)
	{
		$this->builder = $builder;
	}

	/**
	 * Check if this is applicable.
	 */
	public function isApplicable(): bool
	{
		return true;
	}

	/**
	 * Build the highlight payload.
	 */
	public function buildHighlightPayload(): ?array
	{
		return null;
	}

	/**
	 * Build the query payload.
	 *
	 * @return array<string, array<string, array<string, string>>>
	 */
	public function buildQueryPayload(): array
	{
		return [
			'must' => [
				'query_string' => [
					'query' => $this->builder->query,
				],
			],
		];
	}
}
