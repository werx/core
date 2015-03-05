<?php

namespace werx\Core;

use Aura\Router\RouterFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Dispatcher
{
	public $opts = [];
	public $router;
	public $namespace = 'werx\Skeleton';
	public $controller = 'Home';
	public $action;
	public $id;
	public $app_dir;

	public function __construct($opts = [])
	{
		foreach ($opts as $key => $value) {
			$this->$key = $value;
		}

		$this->opts = $opts;

		// @todo I feel a little dirty about this. Will revisit later.
		if (array_key_exists('app_dir', $opts)) {
			$GLOBALS['WERX_BASE_PATH'] = $opts['app_dir'];
		}
		
		$this->initializeRoutes();
	}

	public function dispatch()
	{
		// What resource was requested?
		$request = Request::createFromGlobals();

		$path = $request->getPathInfo();

		// Remove trailing slash from the path. This gives us a little more forgiveness in routing
		if ($path != '/') {
			$path = rtrim($path, '/');
		}

		// Find a matching route
		$route = $this->router->match($path, $_SERVER);

		if (!$route) {
			// no route object was returned
			return $this->pageNotFound();
		}

		list($controller, $action, $id) = $this->getAction($route);

		$_SERVER['TS_NAMESPACE'] = $this->namespace;
		$this->controller = strtolower($controller);
		$this->action = $action;

		if (substr($controller, 0, 1) == '\\') {
			// Fully qualified namespace
			$class = $controller;
			$this->controller = strtolower(last(explode('\\', $controller)));
		} else {
			// instantiate the controller class from the default namespace
			$class = join('\\', [$this->namespace, 'Controllers', $controller]);
		}

		if (!class_exists($class)) {
			return $this->pageNotFound();
		} else {
			$GLOBALS['app_instance'] = $this;
			$page = new $class();
			$page->app = $this;

			// invoke the action method with the id
			$page->$action($id);
		}
	}

	/**
	 * What routes have been configured for this app?
	 */
	public function initializeRoutes()
	{
		$router_factory = new RouterFactory;
		$router = $router_factory->newInstance();

		$routes_file = $this->getAppResourcePath('config/routes.php');

		if (file_exists($routes_file)) {
			// Let the app specify it's own routes.
			include_once($routes_file);
		} else {
			// Fall back on some sensible defaults.
			$router->add(null, '/');
			$router->add(null, '/{controller}');
			$router->add(null, '/{controller}/{action}');
			$router->add(null, '/{controller}/{action}/{id}');
		}

		$this->router = $router;
	}

	/**
	 * @param string $message
	 */
	public function pageNotFound($message = 'Not Found')
	{
		$response = new Response($message, 404, ['Content-Type' => 'text/plain']);
		$response->send();
	}

	/**
	 * @param null $file
	 * @return string
	 */
	public function getAppResourcePath($file = null)
	{
		return $this->getSrcDir() . '/' . $file;
	}

	/**
	 * @return string
	 */
	public function getSrcDir()
	{
		return array_key_exists('app_dir', $this->opts) ? $this->opts['app_dir'] . '/src' : dirname(__DIR__) . '/src';
	}

	/**
	 * @param $route
	 * @return array
	 */
	public function getAction($route)
	{
		// does the route indicate a controller?
		if (isset($route->params['controller'])) {

			$namespace = "";

			if (isset($route->params['namespace'])) {
				$namespace = rtrim($route->params['namespace'], '\\') . '\\';
			}

			// explode out our route parts in case there are any namespaces
			$controller_parts = explode('\\', $route->params['controller']);

			// only run strtolower/ucfirst on the last part since that is the controller
			$controller_parts[count($controller_parts) - 1] = ucfirst(strtolower($controller_parts[count($controller_parts) - 1]));

			// put back together the route parts
			$controller = implode('\\', $controller_parts);

			// if we found a namespace above, then prepend it to the controller
			if (!empty($namespace)) {
				$controller = $namespace . $controller;
			}
		} else {
			// use a default controller
			$controller = $this->controller;
		}

		// does the route indicate an action?
		if (isset($route->params['action'])) {
			// take the action method directly from the route
			$action = $route->params['action'];
		} else {
			// use a default action
			$action = 'index';
		}

		$tokens = array_keys($route->tokens);

		if (count($tokens) > 0) {

			$method_params = [];
			foreach ($tokens as $t) {
				if (array_key_exists($t, $route->params)) {
					$method_params[$t] = $route->params[$t];
				}
			}

			// Use an array of method parameters.
			return array($controller, $action, $method_params);
		} elseif (isset($route->params['id'])) {
			// Route contains an id
			return array($controller, $action, $route->params['id']);
		} else {
			// No id specified, just send null.
			return array($controller, $action, null);
		}
	}
}
