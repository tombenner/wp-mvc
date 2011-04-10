<?php

class MvcFileIncluder {

	function __construct() {
	
		$this->core_directory = MVC_CORE_PATH;
		$this->app_directory = MVC_APP_PATH;
				
	}
	
	public function require_app_or_core_file($relative_filepath) {
		if (!$this->require_app_file_if_exists($relative_filepath)) {
			if (!$this->require_core_file_if_exists('pluggable/'.$relative_filepath)) {
				$this->require_core_file($relative_filepath);
			}
		}
	}
	
	public function require_core_file($filepath) {
		return $this->require_file($this->core_directory.$filepath);
	}
	
	public function require_app_file($filepath) {
		return $this->require_file($this->app_directory.$filepath);
	}
	
	public function require_core_file_if_exists($filepath) {
		return $this->require_file_if_exists($this->core_directory.$filepath);
	}
	
	public function require_app_file_if_exists($filepath) {
		return $this->require_file_if_exists($this->app_directory.$filepath);
	}
	
	public function get_php_files_in_directory($directory, $options=array()) {
		
		$filenames = array();
		if (is_dir($directory)) {
			if (isset($options['recursive'])) {
				$filenames = $this->scandir_recursive($directory);
			} else {
				$filenames = scandir($directory);
			}
			$filenames = array_filter($filenames, array($this, 'is_php_file'));
		}
		
		return $filenames;
	
	}
	
	public function require_php_files_in_directory($directory, $options=array()) {
	
		$filenames = $this->get_php_files_in_directory($directory);
		$filepaths = array();
		
		foreach($filenames as $filename) {
			$filepath = $directory.$filename;
			$filepaths[] = $filepath;
			$this->require_file($filepath);
		}
		
		return $filenames;
		
	}
	
	private function require_file($filepath) {
		require_once $filepath;
	}
	
	private function require_file_if_exists($filepath) {
		if (file_exists($filepath)) {
			$this->require_file($filepath);
			return true;
		}
		return false;
	}
	
	private function is_php_file($filename) {
		return preg_match('/.+\.php$/', $filename);
	}
	
	private function scandir_recursive($directory) {
	
		$file_tmp = glob($directory.'*', GLOB_MARK | GLOB_NOSORT);
		$files = array();
		
		foreach($file_tmp as $item){
			if (substr($item, -1) != DIRECTORY_SEPARATOR) {
				$files[] = $item;
			} else {
				$files = array_merge($files, $this->scandir_recursive($item));
			}
		}
	
		return $files;
	
	}

}

?>