<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class AggregateRuleMakeCommand extends GeneratorCommand
{
	/**
	 * @var string
	 */
	protected $name = 'make:aggregate-rule';

	/**
	 * @var string
	 */
	protected $description = 'Create a new aggregate rule';

	/**
	 * @var string
	 */
	protected $type = 'Rule';

	protected function getStub(): string
	{
		return __DIR__ . '/stubs/aggregate_rule.stub';
	}
}
