<?php

namespace werx\Core\Tests;

use Illuminate\Database\Capsule\Manager;
use werx\Core\Database;

class DatabaseTests extends \PHPUnit_Framework_TestCase
{
	public function testCanParseDsn()
	{
		$dsn = Database::parseDsn('mysql://un:pw@hostname/dbname');

		$this->assertArrayHasKey('driver', $dsn);
		$this->assertArrayHasKey('host', $dsn);
		$this->assertArrayHasKey('database', $dsn);
		$this->assertArrayHasKey('username', $dsn);
		$this->assertArrayHasKey('password', $dsn);

		$this->assertEquals('mysql', $dsn['driver']);
		$this->assertEquals('hostname', $dsn['host']);
		$this->assertEquals('un', $dsn['username']);
		$this->assertEquals('pw', $dsn['password']);
	}

	public function testCanParseDsnNoPassword()
	{
		$dsn = Database::parseDsn('mysql://un@hostname/dbname');

		$this->assertArrayHasKey('driver', $dsn);
		$this->assertArrayHasKey('host', $dsn);
		$this->assertArrayHasKey('database', $dsn);
		$this->assertArrayHasKey('username', $dsn);
		$this->assertArrayHasKey('password', $dsn);

		$this->assertEquals('mysql', $dsn['driver']);
		$this->assertEquals('hostname', $dsn['host']);
		$this->assertEquals('un', $dsn['username']);
		$this->assertEquals(null, $dsn['password']);

		$dsn = Database::parseDsn('mysql://un:@hostname/dbname');

		$this->assertArrayHasKey('driver', $dsn);
		$this->assertArrayHasKey('host', $dsn);
		$this->assertArrayHasKey('database', $dsn);
		$this->assertArrayHasKey('username', $dsn);
		$this->assertArrayHasKey('password', $dsn);

		$this->assertEquals('mysql', $dsn['driver']);
		$this->assertEquals('hostname', $dsn['host']);
		$this->assertEquals('un', $dsn['username']);
		$this->assertEquals(null, $dsn['password']);
	}

	public function testCanQueryORM()
	{
		$this->databaseInitSimple();

		$result = \werx\Core\Tests\App\Models\Captain::where('last_name', 'Kirk')->first();
		$this->assertEquals('James', $result->first_name);
	}

	public function testChainedQueryBuilder()
	{
		$this->databaseInitSimple();

		$result = (object) \Illuminate\Database\Capsule\Manager::table('captains')->where('last_name', 'Kirk')->first();
		$this->assertEquals('James', $result->first_name);
	}

	public function testUnchainedQueryBuilder()
	{
		$this->databaseInitSimple();

		$query = \Illuminate\Database\Capsule\Manager::table('captains');
		$query->where('last_name', 'Kirk');

		$result = (object) $query->first();
		$this->assertEquals('James', $result->first_name);
	}

	public function testORMChainedQuery()
	{
		$this->databaseInitSimple();

		$result = (object) \werx\Core\Tests\App\Models\Captain::queryBuilder()->where('last_name', 'Kirk')->first();
		$this->assertEquals('James', $result->first_name);
	}

	public function testORMUnchainedQuery()
	{
		$this->databaseInitSimple();

		$query = \werx\Core\Tests\App\Models\Captain::queryBuilder();
		$query->where('last_name', 'Kirk');
		$result = (object) $query->first();
		$this->assertEquals('James', $result->first_name);
	}

	public function testORMQueryBuilderShouldReturnEmptyArray()
	{
		$this->databaseInitSimple();

		$result = \werx\Core\Tests\App\Models\Captain::search(['first_name' => 'James', 'last_name' => 'Picard']);

		$this->assertTrue(empty($result));
	}

	public function testORMQueryBuilderShouldReturnOneResult()
	{
		$this->databaseInitSimple();

		$result = \werx\Core\Tests\App\Models\Captain::search(['first_name' => 'James']);

		$this->assertTrue(count($result) == 1);
	}

	public function testORMQueryBuilderShouldReturnThreeResults()
	{
		$this->databaseInitSimple();

		$result = \werx\Core\Tests\App\Models\Captain::search(['first_name' => 'J']);

		$this->assertTrue(count($result) == 3);
	}

	public function testCanQueryMultipleORM()
	{
		$this->databaseInitComplex();

		$beer_result = App\Models\Beer::find(1);
		$captain_result = App\Models\CaptainComplex::find(1);

		$this->assertEquals('IPA', $beer_result->name);
		$this->assertEquals('James', $captain_result->first_name);
	}

	public function testGetLastQueryLogsEnabled()
	{
		$this->databaseInitSimple();

		// explicitly enable query logging
		\Illuminate\Database\Capsule\Manager::connection()->enableQueryLog();

		// run a query
		\werx\Core\Tests\App\Models\Captain::search(['first_name' => 'James', 'last_name' => 'Picard']);

		$query = Database::getLastQuery();
		$expected = 'select * from "captains" where "first_name" like \'James%\' and "last_name" like \'Picard%\'';

		$this->assertEquals($expected, $query);
	}

	public function testGetLastQueryLogsDisabled()
	{
		$this->databaseInitSimple();

		// explicitly enable query logging
		\Illuminate\Database\Capsule\Manager::connection()->disableQueryLog();

		// run a query
		\werx\Core\Tests\App\Models\Captain::search(['first_name' => 'James', 'last_name' => 'Picard']);

		$query = Database::getLastQuery();
		$expected = '';

		$this->assertEquals($expected, $query);
	}

	public function testGetLastQueryNoQueryRun()
	{
		$this->databaseInitSimple();

		// explicitly enable query logging
		\Illuminate\Database\Capsule\Manager::connection()->enableQueryLog();

		$query = Database::getLastQuery();
		$expected = '';

		$this->assertEquals($expected, $query);
	}

	public function testPreviewQuery()
	{
		$this->databaseInitSimple();

		$query = \werx\Core\Tests\App\Models\Captain::where('first_name', 'James');

		$sql = Database::getQueryPreview($query);
		$expected = 'select * from "captains" where "first_name" = \'James\'';

		$this->assertEquals($expected, $sql);
	}

	public function testOptionToggleQueryLoggingEnabled()
	{
		// connect with query logging turned on
		$config = $this->getTestDsnSimple();
		$config['log_queries'] = true;
		Database::init($config);

		// run a query
		\werx\Core\Tests\App\Models\Captain::search(['first_name' => 'James', 'last_name' => 'Picard']);

		// we should have one query in our query log
		$this->assertCount(1, Manager::connection('default')->getQueryLog());
	}

	public function testOptionToggleQueryLoggingDisabled()
	{
		// connect with query logging turned on
		$config = $this->getTestDsnSimple();
		$config['log_queries'] = false;
		Database::init($config);

		// run a query
		\werx\Core\Tests\App\Models\Captain::search(['first_name' => 'James', 'last_name' => 'Picard']);

		// we should have one query in our query log
		$this->assertCount(0, Manager::connection('default')->getQueryLog());
	}

	public function testGetPrettyQueries()
	{
		$this->databaseInitSimple();

		// run some queries
		\werx\Core\Tests\App\Models\Captain::search(['first_name' => 'James', 'last_name' => 'Picard']);
		\werx\Core\Tests\App\Models\Captain::search(['first_name' => 'Foo', 'last_name' => 'Bar']);

		$queries = Database::getPrettyQueryLog();
		$expected1 = 'select * from "captains" where "first_name" like \'James%\' and "last_name" like \'Picard%\'';
		$expected2 = 'select * from "captains" where "first_name" like \'Foo%\' and "last_name" like \'Bar%\'';

		$this->assertEquals($expected1, $queries[0]);
		$this->assertEquals($expected2, $queries[1]);
	}

	protected function getTestDsnSimple()
	{
		return ['driver' => 'sqlite','database' => __DIR__ . '/resources/storage/example.sqlite'];
	}

	protected function getTestDsnComplex()
	{
		return [
			'example' =>
				['driver' => 'sqlite','database' => __DIR__ . '/resources/storage/example.sqlite'],
			'beers' =>
				['driver' => 'sqlite','database' => __DIR__ . '/resources/storage/beers.sqlite']
		];
	}

	protected function databaseInitSimple()
	{
		Database::init($this->getTestDsnSimple());
	}

	protected function databaseInitComplex()
	{
		Database::init($this->getTestDsnComplex());
	}
}
