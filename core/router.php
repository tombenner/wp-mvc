<?php

class Router {

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

}

?>