<?php

class MvcRouter {
    
    public $routes = array();

    static function public_url($options=array()) {
        $options = apply_filters('mvc_before_public_url', $options);
        $defaults = array(
            'action' => 'index',
            'controller' => null
        );
        $options = array_merge($defaults, $options);
        $routes = self::get_public_routes();
        $controller = $options['controller'];
        $action = $options['action'];
        $matched_route = null;
        if (!empty($options['object']) && is_object($options['object'])) {
            if (!empty($options['object']->__model_name) && !$controller) {
                $model_name = $options['object']->__model_name;
                $controller = MvcInflector::tableize($model_name);
            } else if ($controller) {
                $model_name = MvcInflector::camelize(MvcInflector::singularize($controller));
            } else {
                MvcError::warning('Incomplete arguments for MvcRouter::public_url().');
            }
            $model = MvcModelRegistry::get_model($model_name);
            if (!empty($model) && method_exists($model, 'to_url')) {
                $route_prefix = self::get_route_prefix();
                $url = home_url((string)$route_prefix . '/');

                $method = new ReflectionMethod(get_class($model), 'to_url');
                $parameter_count = $method->getNumberOfParameters();

                if ($parameter_count == 2) {
                    $url .= $model->to_url($options['object'], $options);
                } else {
                    $url .= $model->to_url($options['object']);
                }
                return $url;
            }
            if (empty($options['id']) && !empty($options['object']->__id)) {
                $options['id'] = $options['object']->__id;
            }
        }
        foreach ($routes as $route) {
            $route_path = $route[0];
            $route_defaults = $route[1];
            if (!empty($route_defaults['controller']) && $route_defaults['controller'] == $controller) {
                if (!empty($route_defaults['action']) && $route_defaults['action'] == $action) {
                    $matched_route = $route;
                }
            }
        }
        $route_prefix = self::get_route_prefix();
        $url = home_url((string)$route_prefix . '/');
        if ($matched_route) {
            $path_pattern = $matched_route[0];
            preg_match_all('/{:([\w]+).*?}/', $path_pattern, $matches, PREG_SET_ORDER);
            $path = $path_pattern;
            foreach ($matches as $match) {
                $pattern = $match[0];
                $option_key = $match[1];
                if (isset($options[$option_key])) {
                    $value = $options[$option_key];
                    $path = preg_replace('/'.preg_quote($pattern).'/', $value, $path, 1);
                } else if (isset($options['object']) && is_object($options['object'])) {
                    if (isset($options['object']->{$option_key})) {
                        $value = $options['object']->{$option_key};
                        $path = preg_replace('/'.preg_quote($pattern).'/', $value, $path, 1);
                        $path = rtrim($path, '.*?');
                    }
                }
            }
            $path = rtrim($path, '/').'/';
            $url .= $path;
        } else {
            $url .= $controller.'/';
            if (!empty($action) && !in_array($action, array('show', 'index'))) {
                $url .= $action.'/';
            }
            if (!empty($options['id'])) {
                $url .= $options['id'].'/';
            }
        }
        return $url;
    }

    static function admin_url($options=array()) {
        if (!empty($options['object']) && is_object($options['object'])) {
            if (empty($options['id']) && !empty($options['object']->__id)) {
                $options['id'] = $options['object']->__id;
            }
            if (empty($options['controller']) && !empty($options['object']->__model_name)) {
                $options['controller'] = MvcInflector::tableize($options['object']->__model_name);
            }
        }
        $url = get_admin_url().'admin.php';
        $params = http_build_query(self::admin_url_params($options));
        if ($params) {
            $url .= '?'.$params;
        }
        return $url;
    }
    
    static function admin_url_params($options=array()) {
        $params = array();
        if (!empty($options['controller'])) {
            $controller = preg_replace('/^admin_/', '', $options['controller']);
            $params['page'] = 'mvc_'.$controller;
            if (!empty($options['action']) && $options['action'] != 'index') {
                $params['page'] .= '-'.$options['action'];
            }
        }
        if (!empty($options['id'])) {
            $params['id'] = $options['id'];
        }
        return $params;
    }
    
    static function admin_page_param($options=array()) {
        if (is_string($options)) {
            $options = array('model' => $options);
        }
        if (!empty($options['model'])) {
            return 'mvc_'.MvcInflector::tableize($options['model']);
        }
        return false;
    }

    static function public_connect( $route, $defaults = array() ) {
        $_this =& MvcRouter::get_instance();
        $_this->maybe_add_route_prefix($route);
        $_this->add_public_route($route, $defaults);
    }
    
    static function admin_ajax_connect($route) {
        $_this =& MvcRouter::get_instance();
        $_this->add_admin_ajax_route($route);
    }

    static function &get_instance() {
        static $instance = array();
        if (!$instance) {
            $mvc_router = new MvcRouter();
            $instance[0] =& $mvc_router;
            $instance[0]->routes = array(
                'public' => array(),
                'admin_ajax' => array()
            );
        }
        return $instance[0];
    }

    static function &get_public_routes() {
        $_this =& self::get_instance();
        $return =& $_this->routes['public'];
        return $return;
    }

    static function &get_admin_ajax_routes() {
        $_this =& self::get_instance();
        $return =& $_this->routes['admin_ajax'];
        return $return;
    }
    
    static function add_public_route($route, $defaults) {
        $_this =& self::get_instance();
        $_this->routes['public'][] = array($route, $defaults);
    }
    
    static function add_admin_ajax_route($route) {
        $_this =& self::get_instance();
        if (empty($route['wp_action'])) {
            $route['wp_action'] = $route['controller'].'_'.$route['action'];
        }
        $_this->routes['admin_ajax'][] = $route;
    }

    static function maybe_add_route_prefix(&$route){
        $route_prefix = MvcConfiguration::get( 'CustomRoutePrefix' );
        if ( ! empty( $route_prefix ) ) {
            $route = $route_prefix . '/' . $route;
        }
    }

    static function get_route_prefix(){
        return MvcConfiguration::get( 'CustomRoutePrefix' );
    }

}

?>
