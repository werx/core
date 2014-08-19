<?php
namespace werx\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Extension to Plates to work better with our routing and auto-sanitize view variables.
 */
class Template extends \League\Plates\Template
{
	public $engine;
	protected $unguarded = ['unguarded', 'engine'];

	public function __construct($path = null)
	{
		// Create a new engine with the proper path to views directory.
		$this->engine = new \League\Plates\Engine($path);

		parent::__construct($this->engine);
	}

	public function uri($resource)
	{
		return $_SERVER['SCRIPT_NAME'] . '/' . ltrim($resource, '/');
	}

	public function asset($resource)
	{
		return dirname($_SERVER['SCRIPT_NAME']) . $resource;
	}

	/**
	 * Sanitize the data before rendering.
	 */
	public function render($view, array $data = null)
	{
		// Sanitize variables already in the template.
		foreach (get_object_vars($this) as $key => $value) {
			if (!in_array($key, $this->unguarded)) {
				$this->$key = $this->scrub($value);
			}
		}

		// Also sanitize any variables we are passing in to the template
		$data = $this->scrub($data);

		return parent::render($view, $data);
	}

	/**
	 * Output the content instead of just render.
	 */
	public function output($view, array $data = null)
	{
		$response = new Response($this->render($view, $data), Response::HTTP_OK, ['Content-Type' => 'text/html']);
		$response->send();
	}

	public function addFolder($name, $path)
	{
		$this->engine->addFolder($name, $path);
	}

	/**
	 * Recursively sanitize output
	 */
	public function scrub($var)
	{
		if (is_string($var)) {
			// Sanitize strings
			return $this->escape($var);

		} elseif (is_array($var)) {
			// Sanitize arrays
			while (list($key) = each($var)) {
				// casting key to string for the case of numeric indexed arrays, i.e. 0, 1, etc. b/c 0 == any string in php
				if (!in_array((string)$key, $this->unguarded)) {
					$var[$key] = $this->scrub($var[$key]);
				}
			}

			return $var;
		} elseif (is_object($var)) {
			// Sanitize objects
			$values = get_object_vars($var);

			foreach ($values as $key => $value) {
				$var->$key = $this->scrub($value);
			}
			return $var;

		} else {
			// Not sure what this is. null or bool? Just return it.
			return $var;
		}
	}

	public function unguard($key)
	{
		if (is_array($key)) {
			foreach ($key as $k) {
				$this->unguard($k);
			}
		} else {
			$this->unguarded[] = $key;
		}
	}

	public function setPrefill($data = [])
	{
		$this->data(['prefill' => $data]);
	}

	public function prefill($key, $default = null)
	{
		if (isset($this->prefill)) {
			return isset($this->prefill[$key]) ? $this->prefill[$key] : $default;
		} else {
			return $default;
		}
	}

	public function loadExtension($extension)
	{
		return $this->engine->loadExtension($extension);
	}
}
