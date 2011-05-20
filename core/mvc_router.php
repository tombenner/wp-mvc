<?php

class MvcRouter {
	
	public $routes = array();

	public function public_url($options=array()) {
		$routes = self::get_public_routes();
		$controller = $options['controller'];
		$action = empty($options['action']) ? 'index' : $options['action'];
		$matched_route = null;
		foreach($routes as $route) {
			$route_path = $route[0];
			$route_defaults = $route[1];
			if (!empty($route_defaults['controller']) && $route_defaults['controller'] == $controller) {
				if (!empty($route_defaults['action']) && $route_defaults['action'] == $action) {
					$matched_route = $route;
				}
			}
		}
		$url = site_url('/');
		if ($matched_route) {
			$path_pattern = $matched_route[0];
			preg_match_all('/{:([\w]+).*?}/', $path_pattern, $matches, PREG_SET_ORDER);
			$path = $path_pattern;
			foreach($matches as $match) {
				$pattern = $match[0];
				$option_key = $match[1];
				if (isset($options[$option_key])) {
					$value = $options[$option_key];
					$path = preg_replace('/'.preg_quote($pattern).'/', $value, $path, 1);
				}
			}
			$path = rtrim($path, '/').'/';
			$url .= $path;
		} else {
			$url .= $options['controller'].'/';
			if (!empty($options['action']) && $options['action'] != 'show') {
				$url .= $options['action'].'/';
			}
			if (!empty($options['id'])) {
				$url .= $options['id'].'/';
			}
		}
		return $url;
	}

	public function admin_url($options=array()) {
		$url = get_admin_url().'admin.php';
		$url .= '?'.http_build_query(self::admin_url_params($options));
		return $url;
	}
	
	public function admin_url_params($options=array()) {
		$params = array();
		if (!empty($options['controller'])) {
			$controller = preg_replace('/^admin_/', '', $options['controller']);
			$params['page'] = $controller;
			if (!empty($options['action']) && $options['action'] != 'index') {
				$params['page'] .= '-'.$options['action'];
			}
		}
		if (!empty($options['id'])) {
			$params['id'] = $options['id'];
		}
		return $params;
	}

	public function public_connect($route, $defaults=array()) {
		$_this =& MvcRouter::get_instance();
		$_this->add_public_route($route, $defaults);
	}
	
	public function admin_ajax_connect($route) {
		$_this =& MvcRouter::get_instance();
		$_this->add_admin_ajax_route($route);
	}

	private function &get_instance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new MvcRouter();
			$instance[0]->routes = array(
				'public' => array(),
				'admin_ajax' => array()
			);
		}
		return $instance[0];
	}

	public function &get_public_routes() {
		$_this =& self::get_instance();
		$return =& $_this->routes['public'];
		return $return;
	}

	public function &get_admin_ajax_routes() {
		$_this =& self::get_instance();
		$return =& $_this->routes['admin_ajax'];
		return $return;
	}
	
	public function add_public_route($route, $defaults) {
		$_this =& self::get_instance();
		$_this->routes['public'][] = array($route, $defaults);
	}
	
	public function add_admin_ajax_route($route) {
		$_this =& self::get_instance();
		if (empty($route['wp_action'])) {
			$route['wp_action'] = $route['controller'].'_'.$route['action'];
		}
		$_this->routes['admin_ajax'][] = $route;
	}

}

?>