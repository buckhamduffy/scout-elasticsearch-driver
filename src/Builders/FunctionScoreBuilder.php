<?php

namespace ScoutElastic\Builders;

use ScoutElastic\Payloads\RawPayload;

class FunctionScoreBuilder
{
	private ?string $score_mode = null;
	private ?string $boost_mode = null;
	private array $functions = [];
	private array $script_score = [];
	private array $random_score = [];
	private array $field_value_factory = [];
	private ?float $max_boost = null;
	private ?float $min_score = null;
	private ?float $weight = null;

	public function getScoreMode(): ?string
	{
		return $this->score_mode;
	}

	public function setScoreMode(string $score_mode): FunctionScoreBuilder
	{
		$this->score_mode = $score_mode;
		return $this;
	}

	public function getBoostMode(): ?string
	{
		return $this->boost_mode;
	}

	public function setBoostMode(string $boost_mode): FunctionScoreBuilder
	{
		$this->boost_mode = $boost_mode;
		return $this;
	}

	/**
	 * @return mixed[]
	 */
	public function getFunctions(): array
	{
		return $this->functions;
	}

	/**
	 * @param mixed[] $functions
	 */
	public function setFunctions(array $functions): FunctionScoreBuilder
	{
		$this->functions = $functions;
		return $this;
	}

	/**
	 * @return mixed[]
	 */
	public function getScriptScore(): array
	{
		return $this->script_score;
	}

	/**
	 * @param mixed[] $script_score
	 */
	public function setScriptScore(array $script_score): FunctionScoreBuilder
	{
		$this->script_score = $script_score;
		return $this;
	}

	/**
	 * @return mixed[]
	 */
	public function getRandomScore(): array
	{
		return $this->random_score;
	}

	/**
	 * @param mixed[] $random_score
	 */
	public function setRandomScore(array $random_score): FunctionScoreBuilder
	{
		$this->random_score = $random_score;
		return $this;
	}

	/**
	 * @return mixed[]
	 */
	public function getFieldValueFactory(): array
	{
		return $this->field_value_factory;
	}

	/**
	 * @param mixed[] $field_value_factory
	 */
	public function setFieldValueFactory(array $field_value_factory): FunctionScoreBuilder
	{
		$this->field_value_factory = $field_value_factory;
		return $this;
	}

	public function getMaxBoost(): ?float
	{
		return $this->max_boost;
	}

	public function setMaxBoost(?float $max_boost): FunctionScoreBuilder
	{
		$this->max_boost = $max_boost;
		return $this;
	}

	public function getMinScore(): ?float
	{
		return $this->min_score;
	}

	public function setMinScore(?float $min_score): FunctionScoreBuilder
	{
		$this->min_score = $min_score;
		return $this;
	}

	public function getWeight(): ?float
	{
		return $this->weight;
	}

	public function setWeight(?float $weight): FunctionScoreBuilder
	{
		$this->weight = $weight;
		return $this;
	}

	public function buildPayload(): RawPayload
	{
		$payload = new RawPayload();
		$payload->setIfNotNull('score_mode', $this->score_mode)
			->setIfNotNull('boost_mode', $this->boost_mode)
			->setIfNotEmpty('functions', $this->functions)
			->setIfNotEmpty('script_score', $this->script_score)
			->setIfNotEmpty('random_score', $this->random_score)
			->setIfNotEmpty('field_value_factor', $this->field_value_factory)
			->setIfNotNull('max_boost', $this->max_boost)
			->setIfNotNull('min_score', $this->min_score)
			->setIfNotNull('weight', $this->weight);

		return $payload;
	}
}
