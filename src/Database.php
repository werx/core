<?php

namespace werx\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Nyholm\DSN;

class Database
{
	public static $driver = [];

	/**
	 * Initialize the connection to the database.
	 *
	 * @param null $config
	 */
	public static function init($config = null)
	{
		$defaults = [
			'driver'	    => 'mysql',
			'host'		    => 'localhost',
			'database'	    => 'mysql',
			'username'	    => 'root',
			'password'	    => null,
			'log_queries'   => true,
			'charset'	    => 'utf8',
			'collation'	    => 'utf8_unicode_ci',
			'prefix'	    => null
		];

		$capsule = new Capsule;

		if (is_string($config)) {
			$config = self::parseDsn($config);
		}

		if (is_array($config)) {
			// missing 'driver' key, so it must be an array of arrays
			if (!array_key_exists('driver', $config)) {
				// if we have an array of connections, iterate through them.
				// Cnnections should be stored in the form of name => conn_info
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

		// determine if we should log queries or not
		foreach ($options as $name => $info) {
			// make sure we use FALSE to disable queries, otherwise it'll just default to logging queries
			if (isset($info['log_queries']) && $info['log_queries'] === false) {
				Capsule::connection($name)->disableQueryLog();
			} else {
				Capsule::connection($name)->enableQueryLog();
			}
		}
	}

	/**
	 * Return the name of the random function based on the SQL dialect being used.
	 *
	 * @param string $connection_name
	 * @return string
	 */
	public static function random($connection_name = 'default')
	{
		if (self::$driver[$connection_name] == 'sqlite') {
			return 'random()';
		} else {
			return 'rand()';
		}
	}

	/**
	 * Take a string DSN and parse it into an array of its pieces
	 *
	 * @param null $string
	 * @return array|null
	 */
	public static function parseDsn($string = null)
	{
		$opts = null;

		if (!empty($string)) {
			$dsn = new DSN($string);

			$port = empty($dsn->getFirstPort()) ? '' : (':' . $dsn->getFirstPort());
			$password = empty($dsn->getPassword()) ? null : $dsn->getPassword();

			$opts = [
				'driver'	=> $dsn->getProtocol(),
				'host'		=> $dsn->getFirstHost() . $port,
				'database'	=> $dsn->getDatabase(),
				'username'	=> $dsn->getUsername(),
				'password'	=> $password
			];
		}

		return $opts;
	}

	/**
	 * Get a preview of what query will be run from a query builder.
	 *
	 * This DOES NOT run the query so it can be used for debugging potentially memory-intensive queries.
	 *
	 * @param QueryBuilder $query
	 * @return string
	 */
	public static function getQueryPreview(QueryBuilder $query = null)
	{
		if (empty($query)) {
			return "";
		}

		$sql        = str_replace('?', "'%s'", $query->toSql());
		$bindings   = $query->getBindings();

		return vsprintf($sql, $bindings);
	}

	/**
	 * Get the last query that was run with data that was used bound to it.
	 *
	 * @param string $connection
	 * @return string
	 */
	public static function getLastQuery($connection = "")
	{
		$last_query = "";
		$pretty_queries = self::getPrettyQueryLog($connection);

		if (!empty($pretty_queries)) {
			$last_query = $pretty_queries[ count($pretty_queries) - 1 ];
		}

		return $last_query;
	}

	/**
	 * Get a list of all queries formatted with their bindings in place
	 *
	 * @param string $connection
	 * @return array
	 */
	public static function getPrettyQueryLog($connection = "")
	{
		$return_queries = [];

		$queries = Capsule::connection($connection)->getQueryLog();

		foreach ($queries as $query) {
			$query_pattern = str_replace('?', "'%s'", $query['query']);
			$return_queries[] = vsprintf($query_pattern, $query['bindings']);
		}

		return $return_queries;
	}
}
