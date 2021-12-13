<?php

namespace ScoutElastic\Payloads\Features;

trait HasProtectedKeys
{

	/**
	 * Set a value.
	 *
	 * @param mixed $value
	 * @return $this
	 */
	public function set(string $key, $value): self
	{
		if (in_array($key, $this->protectedKeys)) {
			return $this;
		}

		return parent::set($key, $value);
	}

}
