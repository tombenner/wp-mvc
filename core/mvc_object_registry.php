<?php

class MvcObjectRegistry {

    var $__objects = array();

    static function &get_instance() {
        static $instance = array();
        if (!$instance) {
            $mvc_object_registry = new MvcObjectRegistry();
            $instance[0] =& $mvc_object_registry;
        }
        return $instance[0];
    }

    static function &get_object($key) {
        $_this =& MvcObjectRegistry::get_instance();
        $key = MvcInflector::camelize($key);
        $return = false;
        if (isset($_this->__objects[$key])) {
            $return =& $_this->__objects[$key];
        }
        return $return;
    }
    
    static function add_object($key, &$object) {
        $_this =& MvcObjectRegistry::get_instance();
        $key = MvcInflector::camelize($key);
        if (!isset($_this->__objects[$key])) {
            $_this->__objects[$key] =& $object;
            return true;
        }
        return false;
    }

}

?>
