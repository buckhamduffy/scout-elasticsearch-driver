<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class SearchRuleMakeCommand extends GeneratorCommand
{
	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $name = 'make:search-rule';

	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $description = 'Create a new search rule';

	/**
	 * {@inheritdoc}
	 * @var string
	 */
	protected $type = 'Rule';

	/**
	 * {@inheritdoc}
	 */
	protected function getStub(): string
	{
		return __DIR__ . '/stubs/search_rule.stub';
	}
}
