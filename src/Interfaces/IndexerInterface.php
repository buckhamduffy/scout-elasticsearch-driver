<?php

namespace ScoutElastic\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface IndexerInterface
{
	/**
	 * Update documents.
	 *
	 * @return void
	 */
	public function update(Collection $models);

	/**
	 * Delete documents.
	 *
	 * @return void
	 */
	public function delete(Collection $models);
}
