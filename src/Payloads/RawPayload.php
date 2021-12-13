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
	 * @param mixed $value
	 */
	public function set(string $key, $value)
	{
		Arr::set($this->payload, $key, $value);

		return $this;
	}

	/**
	 * Unset a value.
	 *
	 * @param mixed $value
	 */
	public function unset($key): self
	{
		Arr::forget($this->payload, $key);

		return $this;
	}

	/**
	 * Set a value if it's not empty.
	 *
	 * @param mixed $value
	 */
	public function setIfNotEmpty(string $key, $value): self
	{
		if (empty($value)) {
			return $this;
		}

		return $this->set($key, $value);
	}

	/**
	 * Set a value if it's not null.
	 *
	 * @param mixed $value
	 */
	public function setIfNotNull(string $key, $value): self
	{
		if (is_null($value)) {
			return $this;
		}

		return $this->set($key, $value);
	}

	/**
	 * Checks that the payload key has a value.
	 */
	public function has(string $key): bool
	{
		return Arr::has($this->payload, $key);
	}

	/**
	 * Add a value.
	 *
	 * @param mixed $value
	 */
	public function add(string $key, $value): self
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
	 * @param mixed $value
	 */
	public function addIfNotEmpty(string $key, $value): self
	{
		if (empty($value)) {
			return $this;
		}

		return $this->add($key, $value);
	}

	/**
	 * Get value.
	 *
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get($key = null, $default = null)
	{
		return Arr::get($this->payload, $key, $default);
	}

}
