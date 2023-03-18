<?php

namespace ScoutElastic\Interfaces;

use ScoutElastic\Searchable;
use Illuminate\Database\Eloquent\Collection;

interface IndexerInterface
{
	/**
	 * Update documents.
	 * @param $models Searchable[]
	 */
	public function update(Collection $models): void;

	/**
	 * Delete documents.
	 * @param $models Searchable[]
	 */
	public function delete(Collection $models): void;
}
