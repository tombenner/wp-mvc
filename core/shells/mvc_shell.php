<?php

class MvcShell {

	protected $core_path = '';
	protected $app_path = '';
	protected $file_includer = null;

	function __construct($args=array()) {
	
		$this->core_path = MVC_CORE_PATH;
		$this->app_path = MVC_APP_PATH;
		$this->file_includer = new MvcFileIncluder();
	
		$this->init($args);
	
	}
	
	// This can be overwritten by descendant classes to add custom functionality during shell initialization
	protected function init($args) {
	
	}

	public function main($args) {
		echo 'To handle commands without any arguments, please define a main() method in the shell.';
	}
	
	public function out($string) {
		echo $string."\n";
	}
	
	protected function load_helper($helper_name) {
		$helper_name = $helper_name.'Helper';
		$helper_underscore = Inflector::underscore($helper_name);
		
		$this->file_includer->require_app_or_core_file('helpers/'.$helper_underscore.'.php');
		
		if (class_exists($helper_name)) {
			$helper_method_name = str_replace('_helper', '', $helper_underscore);
		
			$this->{$helper_method_name} = new $helper_name();
		}
	}
	
	protected function load_model($model_name) {
		$model_underscore = Inflector::underscore($model_name);
		
		$this->file_includer->require_app_or_core_file('models/'.$model_underscore.'.php');
		
		if (class_exists($model_name)) {
			$this->{$model_name} = new $model_name();
		}
	}
	
	protected function load_models($model_names) {
		foreach($model_names as $model_name) {
			$this->load_model($model_name);
		}
	}

}

?>