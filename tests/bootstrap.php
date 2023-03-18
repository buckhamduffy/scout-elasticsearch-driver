<?php

use ScoutElastic\Tests\Config;

if (!function_exists('config')) {
	/**
	 * @param null|string $key
	 * @param null|mixed  $default
	 */
	function config($key = null, $default = null)
	{
		return Config::get($key, $default);
	}
}

include __DIR__ . '/../vendor/autoload.php';
