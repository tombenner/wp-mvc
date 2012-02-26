<?php

class MvcModelRegistry {

	var $__models = array();

	private function &get_instance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new MvcModelRegistry();
		}
		return $instance[0];
	}

	public function &get_model($key) {
		$_this =& self::get_instance();
		$key = MvcInflector::camelize($key);
		$return = false;
		if (isset($_this->__models[$key])) {
			$return =& $_this->__models[$key];
		} else if (class_exists($key)) {
			$_this->__models[$key] = new $key();
			$return =& $_this->__models[$key];
		}
		return $return;
	}

	public function &get_models() {
		$_this =& self::get_instance();
		$return =& $_this->__models;
		return $return;
	}
	
	public function add_model($key, &$model) {
		$_this =& self::get_instance();
		$key = MvcInflector::camelize($key);
		if (!isset($_this->__models[$key])) {
			$_this->__models[$key] = $model;
			return true;
		}
		return false;
	}

}

?>