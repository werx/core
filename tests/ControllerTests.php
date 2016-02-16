<?php

namespace werx\Core\Tests;

use werx\Core\Tests\App\Controllers as Controllers;

class ControllerTests extends \PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$GLOBALS['WERX_BASE_PATH'] = __DIR__;
		ob_start();
	}

	public function __destruct()
	{
		echo ob_get_clean();
	}

	public function testBasicControllerAction()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$controller->index();

		$this->expectOutputString('HOME\INDEX');
	}

	public function testCanOutputJson()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$controller->json(['foo' => 'bar']);

		$this->expectOutputString('{"foo":"bar"}');
	}

	public function testCanOutputJsonp()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$controller->jsonp(['foo' => 'bar']);

		$this->expectOutputString('/**/callback({"foo":"bar"});');
	}

	public function testCanRenderTemplate()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$controller->renderTemplate();

		$this->expectOutputString('<p>bar</p>');
	}

	public function testCanOutputTemplate()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$controller->outputTemplate();

		$this->expectOutputString('<p>bar</p>');
	}

//	public function testCanRedirectExternal()
//	{
//		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
//		$controller->redirect('http://www.example.com');
//
//		$this->expectOutputRegex('/Redirecting to http:\/\/www.example.com/');
//	}
//
//	public function testCanRedirectLocal()
//	{
//		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
//		$controller->redirect('home/people', 1);
//
//		$this->expectOutputRegex('/home\/people\/1/');
//	}
//
//	public function testCanRedirectLocalArray()
//	{
//		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
//		$controller->redirect('home/people/{foo},{bar}', ['foo' => 'Foo', 'bar' => 'Bar']);
//
//		$this->expectOutputRegex('/home\/people\/Foo,Bar/');
//	}
//
//	public function testCanRedirectLocalQueryString()
//	{
//		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
//		$controller->redirect('home/people', ['lastname' => 'Foo', 'firstname' => 'Bar'], true);
//
//		$this->expectOutputRegex('/home\/people\?lastname=Foo&amp;firstname=Bar/');
//	}

	public function testCanExtendConsole()
	{
		$controller = new Controllers\Console(['app_dir' => $this->getAppDir()]);
		$controller->sayHello('Dave');
		$this->expectOutputString('Hello, Dave');
	}

	public function testCanPrefillFromSession()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$controller->prefillFromSession();
		$this->expectOutputString('from session', "Prefill in session should render the specified value");
	}

	public function testCanPrefillFromSessionDefaultValue()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$controller->prefillFromSessionDefaultValue();
		$this->expectOutputString('default', "No prefill in session should render default value");
	}

	public function testCanGetRequestedController()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$this->assertEquals('home', $controller->getRequestedController());
	}

	public function testgetRequestedControllerShouldReturnDefault()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$this->assertEquals('none', $controller->getRequestedController('none'));
	}

	public function testCanGetRequestedAction()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$this->assertEquals('index', $controller->getRequestedAction());
	}

	public function testgetRequestedActionShouldReturnDefault()
	{
		$controller = new Controllers\Home(['app_dir' => $this->getAppDir()]);
		$this->assertEquals('none', $controller->getRequestedAction('none'));
	}

	protected function getAppDir()
	{
		return __DIR__ . DIRECTORY_SEPARATOR . 'resources';
	}
}
