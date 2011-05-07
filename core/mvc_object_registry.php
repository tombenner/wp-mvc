<?php

class MvcObjectRegistry {

	var $__objects = array();

	function &get_instance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new MvcObjectRegistry();
		}
		return $instance[0];
	}

	function &get_object($key) {
		$_this =& MvcObjectRegistry::get_instance();
		$key = MvcInflector::camelize($key);
		$return = false;
		if (isset($_this->__objects[$key])) {
			$return =& $_this->__objects[$key];
		}
		return $return;
	}
	
	function add_object($key, &$object) {
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