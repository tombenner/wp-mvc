<?php

class MvcDispatcher {

	function dispatch($options=array()) {
		
		$controller_name = $options['controller'];
		$action = $options['action'];
		$params = $options;
		
		$controller_class = MvcInflector::camelize($controller_name).'Controller';
		
		$controller = new $controller_class();
		
		$controller->name = $controller_name;
		$controller->action = $action;
		$controller->init();
		
		if (!method_exists($controller, $action)) {
			MvcError::fatal('A method for the action "'.$action.'" doesn\'t exist in "'.$controller_class.'"');
		}
		
		$request_params = $_REQUEST;
		$request_params = self::escape_params($request_params);
		
		$params = array_merge($request_params, $params);
		
		if (is_admin()) {
			unset($params['page']);
		}
		
		$controller->params = $params;
		$controller->set('this', $controller);
		if (!empty($controller->before)) {
			foreach ($controller->before as $method) {
				$controller->{$method}();
			}
		}
		$controller->{$action}();
		if (!empty($controller->after)) {
			foreach ($controller->after as $method) {
				$controller->{$method}();
			}
		}
		$controller->after_action($action);
		
		if (!$controller->view_rendered) {
			$controller->render_view($controller->views_path.$action, $options);
		}
	
	}
	
	private function escape_params($params) {
		if (is_array($params)) {
			foreach ($params as $key => $value) {
				if (is_string($value)) {
					$params[$key] = stripslashes($value);
				} else if (is_array($value)) {
					$params[$key] = self::escape_params($value);
				}
			}
		}
		return $params;
	}

	public function __call($method, $args) {
		if (isset($this->$method) === true) {
			$function = $this->$method;
			$function();
		}
	}

}

?>