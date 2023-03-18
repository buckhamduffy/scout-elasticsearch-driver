<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class SearchRuleMakeCommand extends GeneratorCommand
{
	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $name = 'make:search-rule';

	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $description = 'Create a new search rule';

	/**
	 * {@inheritDoc}
	 * @var string
	 */
	protected $type = 'Rule';

	/**
	 * {@inheritDoc}
	 */
	protected function getStub(): string
	{
		return __DIR__ . '/stubs/search_rule.stub';
	}
}
