<?php

class Router {
	
	public $routes = array();

	public function public_url($options=array()) {
		$url = site_url().'/'.$options['controller'].'/';
		if (!empty($options['action']) && $options['action'] != 'show') {
			$url .= $options['action'].'/';
		}
		if (!empty($options['id'])) {
			$url .= $options['id'];
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
		$_this =& Router::get_instance();
		$_this->add_public_route($route, $defaults);
	}

	private function &get_instance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new Router();
			$instance[0]->routes = array(
				'public' => array(),
				'admin' => array()
			);
		}
		return $instance[0];
	}

	public function &get_public_routes() {
		$_this =& self::get_instance();
		$return =& $_this->routes['public'];
		return $return;
	}

	public function &get_admin_routes() {
		$_this =& self::get_instance();
		$return =& $_this->routes['admin'];
		return $return;
	}
	
	public function add_public_route($route, $defaults) {
		$_this =& self::get_instance();
		$_this->routes['public'][] = array($route, $defaults);
	}
	
	public function add_admin_route($route, $defaults) {
		$_this =& self::get_instance();
		$_this->routes['admin'][] = array($route, $defaults);
	}

}

?>