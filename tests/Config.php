<?php

namespace ScoutElastic\Tests;

use Illuminate\Support\Arr;

class Config
{
	/**
	 * @var array
	 */
	private static $values = [];

	/**
	 * @param string $key
	 */
	public static function set($key, $value): void
	{
		Arr::set(static::$values, $key, $value);
	}

	/**
	 * @param null|string $key
	 * @param null|mixed  $default
	 */
	public static function get($key = null, $default = null)
	{
		return Arr::get(static::$values, $key, $default);
	}

	public static function reset(array $values = []): void
	{
		static::$values = $values;

		foreach ($values as $key => $value) {
			static::set($key, $value);
		}
	}
}
