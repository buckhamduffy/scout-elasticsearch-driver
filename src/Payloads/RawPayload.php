<?php

namespace ScoutElastic\Payloads;

use Illuminate\Support\Arr;

class RawPayload
{
	/**
	 * The payload.
	 */
	protected array $payload = [];

	/**
	 * Set a value.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return $this
	 */
	public function set($key, $value)
	{
		Arr::set($this->payload, $key, $value);

		return $this;
	}

	/**
	 * Set a value if it's not empty.
	 *
	 * @param  mixed  $value
	 * @return $this
	 */
	public function setIfNotEmpty(string $key, $value)
	{
		if (empty($value)) {
			return $this;
		}

		return $this->set($key, $value);
	}

	/**
	 * Set a value if it's not null.
	 *
	 * @param  mixed  $value
	 * @return $this
	 */
	public function setIfNotNull(string $key, $value)
	{
		if (is_null($value)) {
			return $this;
		}

		return $this->set($key, $value);
	}

	/**
	 * Checks that the payload key has a value.
	 *
	 * @return bool
	 */
	public function has(string $key)
	{
		return Arr::has($this->payload, $key);
	}

	/**
	 * Add a value.
	 *
	 * @param  mixed  $value
	 * @return $this
	 */
	public function add(string $key, $value)
	{
		if (!is_null($key)) {
			$currentValue = Arr::get($this->payload, $key, []);

			if (!is_array($currentValue)) {
				$currentValue = Arr::wrap($currentValue);
			}

			$currentValue[] = $value;

			Arr::set($this->payload, $key, $currentValue);
		}

		return $this;
	}

	/**
	 * Add a value if it's not empty.
	 *
	 * @param  mixed  $value
	 * @return $this
	 */
	public function addIfNotEmpty(string $key, $value)
	{
		if (empty($value)) {
			return $this;
		}

		return $this->add($key, $value);
	}

	/**
	 * Get value.
	 *
	 * @param  string|null  $key
	 * @param  mixed|null  $default
	 * @return mixed
	 */
	public function get($key = null, $default = null)
	{
		return Arr::get($this->payload, $key, $default);
	}
}
