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

	/**
	 * Extend core session functionality to use the MockArraySessionStorage adapter for our tests.
	 *
	 * @param null $session_expiration
	 */
	protected function initializeSession($session_expiration = null)
	{
		$this->session = new \Symfony\Component\HttpFoundation\Session\Session(
			new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage
		);

		$this->config->load('config');

		// We need a unique session name for this app. Let's use last 10 characters the file path's sha1 hash.
		try {
			$this->session->setName('TSAPP' . substr(sha1(__FILE__), -10));
			$this->session->start();

			// Default session expiration 1 hour.
			// Can be overridden in method param or by setting session_expiration in config.php
			$session_expiration = !empty($session_expiration)
				? $session_expiration
				: $this->config->get('session_expiration', 3600);

			// Is this session too old?
			if (time() - $this->session->getMetadataBag()->getLastUsed() > $session_expiration) {
				$this->session->invalidate();
			}
		} catch (\LogicException $e) {
			// Session already active, can't change it now!
		}
	}
}
