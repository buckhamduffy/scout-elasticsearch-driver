<?php

namespace ScoutElastic\Interfaces;

use ScoutElastic\Searchable;
use Illuminate\Database\Eloquent\Collection;

interface IndexerInterface
{

	/**
	 * Update documents.
	 * @param $models Searchable[]
	 * @return void
	 */
	public function update(Collection $models);

	/**
	 * Delete documents.
	 * @param $models Searchable[]
	 * @return void
	 */
	public function delete(Collection $models);

}
