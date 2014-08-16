<?php

namespace werx\Core\Tests;

use werx\Core\Config;

class ConfigTests extends \PHPUnit_Framework_TestCase
{
	public function testCanGetEnvironment()
	{
		$config = new Config($this->getAppDir());
		
		$this->assertEquals('dev', $config->getEnvironment());
	}

	public function testCanResolvePath()
	{
		$config = new Config($this->getAppDir());
		
		$this->assertEquals($this->getAppDir() . DIRECTORY_SEPARATOR . 'views', $config->resolvePath('views'));
	}
	
	public function testCanLoadDefaultConfig()
	{
		$config = new Config($this->getAppDir());
		$config->load('default');
		$this->assertEquals('bar', $config->get('foo'));
	}

	public function testCanLoadEnvironmentConfig()
	{
		$config = new Config($this->getAppDir());
		$config->load('envopts');
		$this->assertEquals('test', $config->get('env'));
	}

	public function testGetBaseUrlShouldReturnConfigItem()
	{
		$_SERVER['SERVER_NAME'] = 'localhost';
		$config = new Config($this->getAppDir());
		$this->assertEquals('http://test.server.name/werx/', $config->getBaseUrl());
	}

	protected function getAppDir()
	{
		return __DIR__ .	DIRECTORY_SEPARATOR . 'resources';
	}
}
