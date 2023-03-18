<?php

namespace ScoutElastic\Tests\Stubs;

use ScoutElastic\SearchRule as ElasticSearchRule;

class SearchRule extends ElasticSearchRule
{
	/**
	 * {@inheritDoc}
	 */
	public function buildHighlightPayload(): ?array
	{
		$highlight = null;

		foreach ($this->builder->select as $field) {
			if (empty($highlight)) {
				$highlight = [
					'fields' => [],
				];
			}

			$highlight['fields'][$field] = [
				'type' => 'plain',
			];
		}

		return $highlight;
	}
}
