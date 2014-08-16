<?php

namespace werx\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * Wrapper for the Symfony httpFoundation Request class.
 *
 * Exposes post/get data with friendlier (IMHO) syntax.
 */
class Input
{
	public function __construct($request = null)
	{
		if (empty($request)) {
			$this->request = Request::createFromGlobals();
		} else {
			$this->request = $request;
		}
	}
	
	public function post($key = null, $default = null, $deep = true)
	{
		if (!empty($key)) {
			return $this->request->request->get($key, $default, $deep);
		} else {
			return $this->request->request->all();
		}
	}

	public function get($key = null, $default = null, $deep = true)
	{
		if (!empty($key)) {
			return $this->request->query->get($key, $default, $deep);
		} else {
			return $this->request->query->all();
		}
	}
}
