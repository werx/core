<?php

namespace werx\Core\Tests;

use werx\Core\Input;

class InputTests extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$_POST['foo'] = 'Foo';
		$_POST['bar']['yolo'] = 'YOLO';

		$_GET['foo'] = 'Foo';
		$_GET['bar']['yolo'] = 'YOLO';

	}
	
	public function tearDown()
	{
		unset($_POST['foo']);
		unset($_POST['bar']);

		unset($_GET['foo']);
		unset($_GET['bar']);
	}
	
	public function testFetchInvalidPostAttributeReturnsNull()
	{
		$input = new Input();
		$this->assertEquals(null, $input->post('football'));
	}
	
	public function testCanFetchPostAttribute()
	{
		$input = new Input();
		$this->assertEquals('Foo', $input->post('foo'));
	}

	public function testCanFetchPostAttributeDeep()
	{
		$input = new Input();
		$this->assertEquals('YOLO', $input->post('bar[yolo]'));
	}

	public function testCanFetchPostAll()
	{
		$input = new Input();
		$this->assertEquals(2, count($input->post()));
	}

	public function testFetchEmptyPostReturnsEmptyArray()
	{
		$this->tearDown();
		$input = new Input();
		$this->assertInternalType('array', $input->post());
		$this->assertEquals(0, count($input->post()));
	}
	
	public function testFetchInvalidGetAttributeReturnsNull()
	{
		$input = new Input();
		$this->assertEquals(null, $input->get('football'));
	}
	
	public function testCanFetchGetAttribute()
	{
		$input = new Input();
		$this->assertEquals('Foo', $input->get('foo'));
	}

	public function testCanFetchGetAttributeDeep()
	{
		$input = new Input();
		$this->assertEquals('YOLO', $input->get('bar[yolo]'));
	}

	public function testCanFetchGetAll()
	{
		$input = new Input();
		$this->assertEquals(2, count($input->get()));
	}

	public function testFetchEmptyGetReturnsEmptyArray()
	{
		$this->tearDown();
		$input = new Input();
		$this->assertInternalType('array', $input->get());
		$this->assertEquals(0, count($input->get()));
	}
}
