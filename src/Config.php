<?php

namespace werx\Core;

/**
 * Thin wrapper for werx\config.
 */
class Config
{
	public $config = null;
	public $base_path = null;
	
	public function __construct($base_path = null)
	{
		// We need to know paths to our resources.
		if (!empty($base_path)) {
			$this->base_path = $base_path;
		} elseif (array_key_exists('WERX_BASE_PATH', $GLOBALS)) {
			$this->base_path = rtrim($GLOBALS['WERX_BASE_PATH'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'src';
		} else {
			throw new \Exception('base_path not defined!');
		}
		
		// determine our environment and config path
		$environment = $this->getEnvironment();
		$path = $this->resolvePath('config');
		
		// Set up configs.
		$provider = new \werx\Config\Providers\ArrayProvider($path);
		$this->config = new \werx\Config\Container($provider, $environment);

		return $this->config;
	}
	
	public function resolvePath($resource)
	{
		return rtrim($this->base_path . DIRECTORY_SEPARATOR . $resource, DIRECTORY_SEPARATOR);
	}
	
	public function getEnvironment()
	{
		$environment_file = $this->resolvePath('config/environment');
		
		if (file_exists($environment_file)) {
			$environment = trim(file_get_contents($environment_file));
		} else {
			$environment = 'local';
		}
		
		return $environment;
	}

	public function getBaseUrl($include_script_name = false)
	{
		$this->config->load('config');
		$base_url = $this->config->get('base_url');

		if (empty($base_url)) {
			// No base_url defined in the config. Work it out based on server name.
			// This won't work if the app is running behind a proxied URL.
			$protocol = 'http://';

			if (array_key_exists('HTTPS', $_SERVER) && strtolower($_SERVER['HTTPS']) == 'on') {
				$protocol = 'https://';
			}

			$base_url = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']);
		}

		$base_url = rtrim($base_url, '/') . '/';

		if ($include_script_name) {
			# http://example.com/path/to/app/index.php
			return $base_url . basename($_SERVER['SCRIPT_NAME']);
		} else {
			# http://example.com/path/to/app/
			return $base_url;
		}
	}

	public function __call($method, $args = [])
	{
		return call_user_func_array([$this->config, $method], $args);
	}

	public function __get($property = null) {

		switch($property) {
			case 'base_url':
				return $this->getBaseUrl();
				break;
			case 'script_url':
				return $this->getScriptUrl();
				break;
			default:
				return parent::__get($property);
		}
	}
}
