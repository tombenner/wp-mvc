<?php

class MvcDispatcher {

    static function dispatch($options=array()) {
        
        $controller_name = $options['controller'];
        $action = $options['action'];
        $params = $options;
        
        $controller_class = MvcInflector::camelize($controller_name).'Controller';
        
        $controller = new $controller_class();
        
        $controller->name = $controller_name;
        $controller->action = $action;
        $controller->init();
        
        if (!is_callable(array($controller, $action))) {
            MvcError::fatal('A method for the action "'.$action.'" doesn\'t exist in "'.$controller_class.'"');
        }
        
        $request_params = $_REQUEST;
        $request_params = self::escape_params($request_params);
        
        $params = array_merge($request_params, $params);
        
        if (is_admin()) {
            unset($params['page']);
        }
        
        $controller->params = $params;
        if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 70000) { //prevent conflicts with php < 7.0
            $controller->set('this', $controller);
        }
        if (!empty($controller->before)) {
            foreach ($controller->before as $method) {
                $controller->{$method}();
            }
        }
        
        // If we can access the response from our controller's actions (methods)
        // we can use them (the actions) as Widgets in the both sides - outside of wp-mvc plugin and inside of it.
        // The action should return whatever you want, except $this->render_view('view_name').
        // Example: Let's say we have 'UserController' and action 'list' and we want somewhere in Wordpress or another
        // plugin to get all users and to reuse UserController list method, so:
        // class UserController extends MvcPublicController {
        //      public function list() {
        //            $this->view_rendered = true;
        //            $this->set_objects();
        //            $response = this->render_to_string('users/list');
        //            return $response;
        //      }
        // }
        // and where we want to reuse it, we can get it in the following way:
        // $widget = MvcDispatcher::dispatch(array(
        // 			'controller' => 'users',
        // 			'action'	 => 'list'
        // ));
        $response = $controller->{$action}();
        
        if (!empty($controller->after)) {
            foreach ($controller->after as $method) {
                $controller->{$method}();
            }
        }
        $controller->after_action($action);
        
        if (!$controller->view_rendered) {
            $controller->render_view($controller->views_path.$action, $options);
        }
        
        return $response;
    
    }
    
    static function escape_params($params) {
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
