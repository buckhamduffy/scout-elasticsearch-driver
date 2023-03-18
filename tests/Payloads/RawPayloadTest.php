<?php

namespace ScoutElastic\Tests\Payloads;

use ScoutElastic\Payloads\RawPayload;
use ScoutElastic\Tests\AbstractTestCase;

class RawPayloadTest extends AbstractTestCase
{
	public function testSet(): void
	{
		$payload = (new RawPayload())
			->set('foo.bar', 10);

		$this->assertSame(
			['foo' => ['bar' => 10]],
			$payload->get()
		);
	}

	public function testSetIfNotEmpty(): void
	{
		$payload = (new RawPayload())
			->setIfNotEmpty('null', null)
			->setIfNotEmpty('false', false)
			->setIfNotEmpty('zero', 0)
			->setIfNotEmpty('empty_array', [])
			->setIfNotEmpty('empty_string', '')
			->setIfNotEmpty('foo', 'bar');

		$this->assertSame(
			['foo' => 'bar'],
			$payload->get()
		);
	}

	public function testSetIfNotNull(): void
	{
		$payload = (new RawPayload())
			->setIfNotNull('null', null)
			->setIfNotNull('false', false)
			->setIfNotNull('zero', 0)
			->setIfNotNull('empty_array', [])
			->setIfNotNull('empty_string', '')
			->setIfNotNull('foo', 'bar');

		$this->assertSame(
			[
				'false'        => false,
				'zero'         => 0,
				'empty_array'  => [],
				'empty_string' => '',
				'foo'          => 'bar',
			],
			$payload->get()
		);
	}

	public function testHas(): void
	{
		$payload = (new RawPayload())
			->set('foo.bar', 100);

		$this->assertTrue($payload->has('foo'));
		$this->assertTrue($payload->has('foo.bar'));
		$this->assertFalse($payload->has('not_exist'));
	}

	public function testAdd(): void
	{
		$payload = (new RawPayload())
			->set('foo', 0)
			->add('foo', 1);

		$this->assertSame(
			['foo' => [0, 1]],
			$payload->get()
		);
	}

	public function testAddIfNotEmpty(): void
	{
		$payload = (new RawPayload())
			->addIfNotEmpty('foo', 0)
			->addIfNotEmpty('foo', 1);

		$this->assertSame(
			['foo' => [1]],
			$payload->get()
		);
	}

	public function testGet(): void
	{
		$payload = (new RawPayload())
			->set('foo.bar', 0);

		$this->assertSame(
			['bar' => 0],
			$payload->get('foo')
		);

		$this->assertSame(
			['foo' => ['bar' => 0]],
			$payload->get()
		);

		$this->assertSame(
			['value' => 1],
			$payload->get('default', ['value' => 1])
		);
	}
}
