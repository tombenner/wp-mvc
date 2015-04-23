<?php

class MvcSettingsRegistry {

    var $__settings = array();

    private function &get_instance() {
        static $instance = array();
        if (!$instance) {
            $mvc_settings_registry = new MvcSettingsRegistry();
            $instance[0] =& $mvc_settings_registry;
        }
        return $instance[0];
    }

    public function &get_settings($key) {
        $_this =& self::get_instance();
        $key = MvcInflector::camelize($key);
        $return = false;
        if (isset($_this->__settings[$key])) {
            $return =& $_this->__settings[$key];
        } else if (class_exists($key)) {
            $_this->__settings[$key] = new $key();
            $return =& $_this->__settings[$key];
        }
        return $return;
    }
    
    public function add_settings($key, &$settings) {
        $_this =& self::get_instance();
        $key = MvcInflector::camelize($key);
        if (!isset($_this->__settings[$key])) {
            $_this->__settings[$key] = $settings;
            return true;
        }
        return false;
    }

}

?>
