<?php

class DestroyShell extends MvcShell {

	public function controllers($args) {
	
		if (empty($args)) {
			MvcError::fatal('Please specify a name for the model.');
		}
		
		$name = $args[0];
		$this->destroy_controllers($name);
		
	}

	public function model($args) {
	
		if (empty($args)) {
			MvcError::fatal('Please specify a name for the model.');
		}
		
		$name = $args[0];
		$this->destroy_model($name);
		
	}

	public function scaffold($args) {
	
		if (empty($args)) {
			MvcError::fatal('Please specify a name for the model.');
		}
		
		$name = $args[0];
		$this->destroy_controllers($name);
		$this->destroy_model($name);
		$this->destroy_views($name);
		
	}

	public function views($args) {
	
		if (empty($args)) {
			MvcError::fatal('Please specify a name for the model.');
		}
		
		$name = $args[0];
		$this->destroy_views($name);
		
	}
	
	private function destroy_controllers($name) {
		$file = new MvcFile();
		$name_tableized = Inflector::tableize($name);
		$target_directory = $this->app_path.'controllers/';
		$file->delete($target_directory.$name_tableized.'_controller.php');
		$file->delete($target_directory.'admin/admin_'.$name_tableized.'_controller.php');
	}
	
	private function destroy_model($name) {
		$file = new MvcFile();
		$name_underscore = Inflector::underscore($name);
		$target_path = $this->app_path.'models/'.$name_underscore.'.php';
		$file->delete($target_path);
	}
	
	private function destroy_views($name) {
		$name_tableized = Inflector::tableize($name);
		$public_directory = $this->app_path.'views/'.$name_tableized;
		$admin_directory = $this->app_path.'views/admin/'.$name_tableized;
		$directory = new MvcDirectory();
		$directory->delete($public_directory);
		$directory->delete($admin_directory);
	}

}

?>