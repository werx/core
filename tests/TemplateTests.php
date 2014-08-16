<?php

namespace werx\Core\Tests;

use werx\Core\Template;

class TemplateTests extends \PHPUnit_Framework_TestCase
{
	public function testEscapeClassVars()
	{
		$template = new Template(__DIR__ . '/resources/views/');

		$template->layout('layouts/default');

		$template->foo = '<foo>';
		$output = $template->render('foo');

		$this->assertRegExp('/&lt;foo&gt;/', $output);
	}

	public function testEscapeViewVars()
	{
		$template = new Template(__DIR__ . '/resources/views/');

		$template->layout('layouts/default');

		$output = $template->render('foo', ['foo' => '<foo>', 'xss' => '<script>alert("xss")</script>']);

		$this->assertRegExp('/&lt;foo&gt;/', $output);
	}

	public function testUnguardViewVars()
	{
		$template = new Template(__DIR__ . '/resources/views/');

		$template->layout('layouts/default');
		$template->unguard('foo');

		$output = $template->render('foo', ['foo' => '<foo>']);

		$this->assertRegExp('/\<foo\>/', $output);
	}

	public function testUnguardClassVars()
	{
		$template = new Template(__DIR__ . '/resources/views/');

		$template->layout('layouts/default');
		$template->unguard('foo');

		$template->foo = '<foo>';
		$output = $template->render('foo');

		$this->assertRegExp('/\<foo\>/', $output);
	}
	
	public function testPrefill()
	{
		$template = new Template(__DIR__ . '/resources/views/');
		
		$template->layout('layouts/default');
		$template->prefill = ['foo' => 'test'];
		
		$this->assertEquals('test', $template->prefill('foo'), 'Should return the value of the template variable.');
	}

	public function testPrefillNull()
	{
		$template = new Template(__DIR__ . '/resources/views/');
		
		$template->layout('layouts/default');
		
		$this->assertEquals(null, $template->prefill('foo'), 'Should return the default null for unset template variable.');
	}

	public function testPrefillDefault()
	{
		$template = new Template(__DIR__ . '/resources/views/');
		
		$template->layout('layouts/default');
		
		$this->assertEquals('default', $template->prefill('foo', 'default'), 'Should return the passed default for unset template variable.');
	}
}
