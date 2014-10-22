<?php

namespace werx\Core\Tests\App\Controllers;

use werx\Core\Controller;

class Home extends Controller
{
	public function __construct($opts)
	{
		parent::__construct($opts);
	}

	public function index()
	{
		print 'HOME\INDEX';
	}
	
	public function renderTemplate()
	{
		echo $this->template->render('foo', ['foo' => 'bar']);
	}

	public function outputTemplate()
	{
		$this->template->output('foo', ['foo' => 'bar']);
	}

	public function prefillFromSession()
	{
		// Save our prefill content. This could come from anywhere like a form get/post.
		$data = ['foo' => 'from session'];
		
		// Store our prefill content in session.
		$this->session->set('prefill', $data);
		
		// Grab our prefill content back from session and mass assign.
		$this->template->setPrefill($this->session->get('prefill'));
		
		$this->template->output('prefill');
	}

	public function prefillFromSessionDefaultValue()
	{
		// Save our prefill content. This could come from anywhere like a form get/post.
		// In this case, we are actually going to clear the data from session to test it being empty.
		$this->session->remove('prefill');
		
		// Grab our prefill content back from session and mass assign.
		$this->template->setPrefill($this->session->get('prefill'));
		
		$this->template->output('prefill');
	}
}
