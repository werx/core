<?php

namespace werx\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Prelude\Dsn\DsnParser;

class Database
{
	public static $driver = [];

	public static function init($config = null)
	{
		$defaults = [
			'driver'	=> 'mysql',
			'host'		=> 'localhost',
			'database'	=> 'mysql',
			'username'	=> 'root',
			'password'	=> null,
			'charset'	=> 'utf8',
			'collation'	=> 'utf8_unicode_ci',
			'prefix'	=> null
		];

		$capsule = new Capsule;

		if (is_string($config)) {
			$config = self::parseDsn($config);
		}

		if (is_array($config)) {

			// missing 'driver' key, so it must be an array of arrays
			if (!array_key_exists('driver', $config)) {

				// if we have an array of connections, iterate through them.  connections should be stored in the form of name => conn_info
				foreach ($config as $connection_name => $connection_info) {

					// if it's a dsn string, then parse it
					if (is_string($connection_info)) {
						$connection_info = self::parseDsn($connection_info);
					}

					// now merge it into our options
					$options[$connection_name] = array_merge($defaults, $connection_info);
				}
			} else {
				$options['default'] = array_merge($defaults, $config);
			}
		} else {
			$options['default'] = $defaults;
		}

		// add each connection, then set as global and boot
		foreach ($options as $name => $info) {
			$capsule->addConnection($info, $name);
			self::$driver[$name] = $info['driver'];
		}

		$capsule->setAsGlobal();
		$capsule->bootEloquent();
	}

	public static function random($connection_name = 'default')
	{
		if (self::$driver[$connection_name] == 'sqlite') {
			return 'random()';
		} else {
			return 'rand()';
		}
	}

	public static function parseDsn($string = null)
	{
		$opts = null;

		if (!empty($string)) {
			$dsn = (object) DsnParser::parseUrl($string)->toArray();

			$opts = [
				'driver'	=> $dsn->driver,
				'host'		=> $dsn->host,
				'database'	=> $dsn->dbname,
				'username'	=> $dsn->user,
				'password'	=> isset($dsn->pass) ? $dsn->pass : null
			];
		}

		return $opts;
	}
}
