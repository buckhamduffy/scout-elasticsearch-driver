<?php

namespace ScoutElastic\Interfaces;

interface AggregateRuleInterface
{
	/**
	 * @return mixed[]
	 */
	public function buildAggregatePayload(): array;
}
