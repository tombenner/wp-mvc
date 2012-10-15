<?php

require_once 'mvc_loader.php';

class MvcPublicLoader extends MvcLoader {
	
	public function flush_rewrite_rules($rules) {
		global $wp_rewrite;
		
		$wp_rewrite->flush_rules();
	}
	
	public function add_rewrite_rules($rules) {
		global $wp_rewrite;
		
		$new_rules = array();
		
		$routes = MvcRouter::get_public_routes();
		
		// Use default routes if none have been defined
		if (empty($routes)) {
			MvcRouter::public_connect('{:controller}', array('action' => 'index'));
			MvcRouter::public_connect('{:controller}/{:id:[\d]+}', array('action' => 'show'));
			MvcRouter::public_connect('{:controller}/{:action}/{:id:[\d]+}');
			$routes = MvcRouter::get_public_routes();
		}
		
		foreach ($routes as $route) {
			
			$route_path = $route[0];
			$route_defaults = $route[1];
			
			if (strpos($route_path, '{:controller}') !== false) {
				foreach ($this->public_controller_names as $controller) {
					$route_rules = $this->get_rewrite_rules($route_path, $route_defaults, $controller);
					$new_rules = array_merge($route_rules, $new_rules);
				}
			} else if (!empty($route_defaults['controller'])) {
				$route_rules = $this->get_rewrite_rules($route_path, $route_defaults, $route_defaults['controller'], 1);
				$new_rules = array_merge($route_rules, $new_rules);
			}
		}
		
		$rules = array_merge($new_rules, $rules);
		$rules = apply_filters('mvc_public_rewrite_rules', $rules);
		return $rules;
	}
	
	protected function get_rewrite_rules($route_path, $route_defaults, $controller, $first_query_var_match_index=0) {

		add_rewrite_tag('%'.$controller.'%', '(.+)');
		
		$rewrite_path = $route_path;
		$query_vars = array();
		$query_var_counter = $first_query_var_match_index;
		$query_var_match_string = '';
		
		// Add any route params from the route path (e.g. '{:controller}/{:id:[\d]+}') to $query_vars
		// and append them to the match string for use in a WP rewrite rule
		preg_match_all('/{:(.+?)(:.*?)?}/', $rewrite_path, $matches);
		foreach ($matches[1] as $query_var) {
			$query_var = 'mvc_'.$query_var;
			if ($query_var != 'mvc_controller') {
				$query_var_match_string .= '&'.$query_var.'=$matches['.$query_var_counter.']';
			}
			$query_vars[] = $query_var;
			$query_var_counter++;
		}
		
		// Do the same as above for route params that are defined as route defaults (e.g. array('action' => 'show'))
		if (!empty($route_defaults)) {
			foreach ($route_defaults as $query_var => $value) {
				$query_var = 'mvc_'.$query_var;
				if ($query_var != 'mvc_controller') {
					$query_var_match_string .= '&'.$query_var.'='.$value;
					$query_vars[] = $query_var;
				}
			}
		}
		
		$this->query_vars = array_unique(array_merge($this->query_vars, $query_vars));
		$rewrite_path = str_replace('{:controller}', $controller, $route_path);
		
		// Replace any route params (e.g. {:param_name}) in the route path with the default pattern ([^/]+)
		$rewrite_path = preg_replace('/({:[\w_-]+})/', '([^/]+)', $rewrite_path);
		// Replace any route params with defined patterns (e.g. {:param_name:[\d]+}) in the route path with
		// their pattern (e.g. ([\d]+))
		$rewrite_path = preg_replace('/({:[\w_-]+:)(.*?)}/', '(\2)', $rewrite_path);
		$rewrite_path = '^'.$rewrite_path.'/?$';
		
		$controller_value = empty($route_defaults['controller']) ? $controller : $route_defaults['controller'];
		$controller_rules = array();
		$controller_rules[$rewrite_path] = 'index.php?mvc_controller='.$controller_value.$query_var_match_string;
		
		return $controller_rules;
	}
	
	public function add_query_vars($vars) {
		$vars = array_merge($vars, $this->query_vars);
		return $vars;
	}
	
	public function template_redirect() {
		global $wp_query, $mvc_params;
		
		$routing_params = $this->get_routing_params();
		
		if ($routing_params) {
			$mvc_params = $routing_params;
			do_action('mvc_public_init', $routing_params);

			// Pre filter
			$routing_params = apply_filters('wpmvc_routing_params', $routing_params);

			// Post filter
			ob_start();
			$this->dispatcher->dispatch($routing_params);
			echo apply_filters('wpmvc_dispatcher', ob_get_clean(), $routing_params);
		}
	}
	
	protected function get_routing_params() {
		global $wp_query;
		
		$controller = $wp_query->get('mvc_controller');
		
		if ($controller) {
			$query_params = $wp_query->query;
			$params = array();
			foreach ($query_params as $key => $value) {
				$key = preg_replace('/^(mvc_)/', '', $key);
				$params[$key] = $value;
			}
			return $params;
		}
		
		return false;
	}

}

?>