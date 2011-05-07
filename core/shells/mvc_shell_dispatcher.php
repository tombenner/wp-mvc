<?php

class MvcShellDispatcher {

	function __construct($args) {
	
		$this->file_includer = new MvcFileIncluder();
	
		$this->dispatch($args);
	
	}
	
	private function dispatch($args) {
	
		MvcConfiguration::set('ExecutionContext', 'shell');
		
		if (empty($args[1])) {
			MvcError::fatal('Please provide the name of the shell as the first argument.');
		}
	
		$shell_name = $args[1];
	
		$shell_name .= '_shell';
		$shell_class_name = MvcInflector::camelize($shell_name);
	
		$this->file_includer->require_first_app_file_or_core_file('shells/'.$shell_name.'.php');
		
		$args = array_slice($args, 2);
		
		if (empty($args[0])) {
			$args = array('main');
		}
		
		$shell = new $shell_class_name($args);
		
		$method = $args[0];
		$args = array_slice($args, 1);
		$shell->{$method}($args);
		
	
	}

}

?>