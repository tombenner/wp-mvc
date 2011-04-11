<?php

class GenerateShell extends MvcShell {

	private $templater = null;

	public function init() {
		$this->templater = new MvcTemplater();
		$this->templater->set_template_directory($this->core_path.'templates/');
	}

	public function controllers($args) {
	
		if (empty($args[0])) {
			MvcError::fatal('Please specify a name for the model.');
		}
		
		$name = $args[0];
		$this->generate_controllers($name);
		
	}

	public function model($args) {
	
		if (empty($args[0])) {
			MvcError::fatal('Please specify a name for the model.');
		}
		
		$name = $args[0];
		$this->generate_model($name);
		
	}

	public function scaffold($args) {
	
		if (empty($args[0])) {
			MvcError::fatal('Please specify a name for the model.');
		}
		
		$name = $args[0];
		$this->generate_controllers($name);
		$this->generate_model($name);
		$this->generate_views($name);
		
	}

	public function views($args) {
	
		if (empty($args[0])) {
			MvcError::fatal('Please specify a name for the model.');
		}
		
		$name = $args[0];
		$this->generate_views($name);
		
	}
	
	private function generate_controllers($name) {
	
		$name_tableized = Inflector::tableize($name);
		$name_pluralized = Inflector::pluralize($name);
		
		$vars = array('name_pluralized' => $name_pluralized);
		
		$target_path = $this->app_path.'controllers/'.$name_tableized.'_controller.php';
		$this->templater->create('public_controller', $target_path, $vars);
		
		$target_path = $this->app_path.'controllers/admin/admin_'.$name_tableized.'_controller.php';
		$this->templater->create('admin_controller', $target_path, $vars);
		
	}
	
	private function generate_model($name) {
		$name_camelized = Inflector::camelize($name);
		$name_underscored = Inflector::underscore($name);
		$target_path = $this->app_path.'models/'.$name_underscored.'.php';
		$vars = array('name' => $name_camelized);
		$this->templater->create('model', $target_path, $vars);
	}
	
	private function generate_views($name) {
	
		$name_tableized = Inflector::tableize($name);
		$name_titleized = Inflector::titleize($name);
		$name_titleized_pluralized = Inflector::pluralize($name_titleized);
		$name_underscored = Inflector::underscore($name);
		
		$directory = new MvcDirectory();
		$public_directory = $this->app_path.'views/'.$name_tableized.'/';
		$directory->create($public_directory);
		$admin_directory = $this->app_path.'views/admin/'.$name_tableized.'/';
		$directory->create($admin_directory);
		
		$vars = array(
			'name_tableized' => $name_tableized,
			'name_titleized' => $name_titleized,
			'name_titleized_pluralized' => $name_titleized_pluralized,
			'name_underscored' => $name_underscored,
		);
		
		$this->templater->create('views/_item', $public_directory.'_item.php', $vars);
		$this->templater->create('views/index', $public_directory.'index.php', $vars);
		$this->templater->create('views/show', $public_directory.'show.php', $vars);
		
		$this->templater->create('views/admin/add', $admin_directory.'/add.php', $vars);
		$this->templater->create('views/admin/edit', $admin_directory.'/edit.php', $vars);
		
	}

}

?>