<?php

namespace ScoutElastic\Facades;

use Illuminate\Support\Facades\Facade;

class ElasticClient extends Facade
{
	/**
	 * Get the facade.
	 */
	protected static function getFacadeAccessor(): string
	{
		return 'scout_elastic.client';
	}
}
