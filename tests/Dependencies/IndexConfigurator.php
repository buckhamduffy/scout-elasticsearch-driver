<?php

namespace ScoutElastic\Tests\Dependencies;

use ScoutElastic\Interfaces\IndexConfiguratorInterface;

trait IndexConfigurator
{
	/**
	 * @param  array                      $params Available parameters: name, settings, default_mapping, methods.
	 * @return IndexConfiguratorInterface
	 */
	public function mockIndexConfigurator(array $params = [])
	{
		$name = $params['name'] ?? 'test';

		$methods = array_merge($params['methods'] ?? [], [
			'getName',
			'getSettings',
			'getDefaultMapping',
			'getWriteAlias',
		]);

		$mock = $this->getMockBuilder(IndexConfiguratorInterface::class)
			->setMethods($methods)->getMock();

		$mock->method('getName')
			->willReturn($name);

		$mock->method('getSettings')
			->willReturn($params['settings'] ?? []);

		$mock->method('getDefaultMapping')
			->willReturn($params['default_mapping'] ?? []);

		$mock->method('getWriteAlias')
			->willReturn($name . '_write');

		return $mock;
	}
}
