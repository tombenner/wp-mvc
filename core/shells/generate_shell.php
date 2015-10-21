<?php

/**
 * Shell for automating the creation of WP MVC-based plugins and the 
 * models, views, and controllers used in those plugins.
 */
class GenerateShell extends MvcShell {

    private $templater = null;

    public function init($args = array()) {
        $this->templater = new MvcTemplater();
        $this->templater->set_template_directory($this->core_path.'templates/plugin/app/');
    }
    
    /**
     * Generate a MVC Plugin.
     * wpmvc generate plugin <plugin>
     * @param mixed $args 
     */
    public function plugin($args) {
        if (empty($args[0])) {
            MvcError::fatal('Please specify a name for the plugin (e.g. "wpmvc generate plugin MyPlugin").');
        }
        $plugin = $args[0];
        $this->generate_app($plugin);
    }

    /**
     * Generate controller and admin controller:
     * wpmvc generate controllers <plugin> <name>
     * @param mixed $args 
     */
    public function controllers($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->generate_controllers($plugin, $name);
    }

    /**
     * Generate model for plugin
     * wpmvc generate model <plugin> <model>
     * @param mixed $args 
     */
    public function model($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->generate_model($plugin, $name);
    }

    /**
     * Generate models, views, and controllers for an entity
     * wpmvc generate scaffold <plugin> <model>
     * @param mixed $args 
     */
    public function scaffold($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->generate_controllers($plugin, $name);
        $this->generate_model($plugin, $name);
        $this->generate_views($plugin, $name);
    }

    /**
     * Generate all views for CRUD operations for model
     * wpmvc generate views <plugin> <model>
     * @param mixed $args 
     */
    public function views($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->generate_views($plugin, $name);
    }
    
    /**
     * Generate a basic wordpress widget
     * wpmvc generate widget <plugin> <name>
     * @param array $args 
     */
    public function widget($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->generate_widget($plugin, $name);
    }
    
    private function generate_app($plugin) {
    
        $plugin_camelized = MvcInflector::camelize($plugin);
        $plugin_titleized = MvcInflector::titleize($plugin);
        $plugin_underscored = MvcInflector::underscore($plugin);
        $plugin_hyphenized = str_replace('_', '-', $plugin_underscored);
        
        $plugin_path = $this->get_plugin_path($plugin);
        $plugin_app_path = $this->get_plugin_app_path($plugin);
        
        $directory = new MvcDirectory();
        $app_directories = array(
            'config',
            'controllers',
            'controllers/admin',
            'models',
            'public',
            'public/css',
            'public/js',
            'views',
            'views/admin'
        );
        foreach ($app_directories as $path) {
            $directory->create($plugin_app_path.$path.'/');
        }
        
        $vars = array(
            'name_camelized' => $plugin_camelized,
            'name_humanized' => $plugin_hyphenized,
            'name_titleized' => $plugin_titleized,
            'name_underscored' => $plugin_underscored
        );
        
        $this->templater->set_template_directory($this->core_path.'templates/plugin/');
        
        $target_path = $plugin_path.$plugin_underscored.'.php';
        $this->templater->create('plugin', $target_path, $vars);
        
        $target_path = $plugin_path.$plugin_underscored.'_loader.php';
        $this->templater->create('plugin_loader', $target_path, $vars);
        
    }
    
    private function generate_controllers($plugin, $name) {
        
        $plugin_app_path = $this->get_plugin_app_path($plugin);
    
        $name_tableized = MvcInflector::tableize($name);
        $name_pluralized = MvcInflector::pluralize($name);
        
        $vars = array('name_pluralized' => $name_pluralized);
        
        $target_path = $plugin_app_path.'controllers/'.$name_tableized.'_controller.php';
        $this->templater->create('public_controller', $target_path, $vars);
        
        $target_path = $plugin_app_path.'controllers/admin/admin_'.$name_tableized.'_controller.php';
        $this->templater->create('admin_controller', $target_path, $vars);
        
    }
    
    /**
     * Generate a basic wordpress widget
     * @param string $plugin plugin where widget will reside
     * @param string $name  name of widget
     */
    private function generate_widget($plugin, $name) {
    
        $plugin_app_path = $this->get_plugin_app_path($plugin);
    
        $title = MvcInflector::titleize($name);
        
        $file_name = MvcInflector::underscore($name);
        
        $class_name = MvcInflector::camelize($plugin).'_'.MvcInflector::camelize($name);
        
        $name_underscore = MvcInflector::underscore($class_name);
        
        $vars = array(
            'name' => $name,
            'title' => $title,
            'name_underscore' => $name_underscore,
            'class_name' => $class_name
            );
        
        $target_path = $plugin_app_path.'widgets/'.$file_name.'.php';
        $this->templater->create('widget', $target_path, $vars);
        
    }
    
    private function generate_model($plugin, $name) {
        $plugin_app_path = $this->get_plugin_app_path($plugin);
        $name_camelized = MvcInflector::camelize($name);
        $name_underscored = MvcInflector::underscore($name);
        $target_path = $plugin_app_path.'models/'.$name_underscored.'.php';
        $vars = array('name' => $name_camelized);
        $this->templater->create('model', $target_path, $vars);
    }
    
    private function generate_views($plugin, $name) {
        
        $plugin_app_path = $this->get_plugin_app_path($plugin);
    
        $name_tableized = MvcInflector::tableize($name);
        $name_titleized = MvcInflector::titleize($name);
        $name_titleized_pluralized = MvcInflector::pluralize($name_titleized);
        $name_underscored = MvcInflector::underscore($name);
        
        $directory = new MvcDirectory();
        $public_directory = $plugin_app_path.'views/'.$name_tableized.'/';
        $directory->create($public_directory);
        $admin_directory = $plugin_app_path.'views/admin/'.$name_tableized.'/';
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
    
    private function get_plugin_model_args($args) {
        if (empty($args[0]) || empty($args[1])) {
            MvcError::fatal('Please specify a plugin and name for the model.');
        }
        $args[0] = str_replace('_', '-', MvcInflector::underscore($args[0]));
        return $args;
    }
    
    private function get_plugin_path($plugin_name) {
        $plugin_underscored = MvcInflector::underscore($plugin_name);
        $plugin_hyphenized = str_replace('_', '-', $plugin_underscored);
        $plugin_path = WP_PLUGIN_DIR.'/'.$plugin_hyphenized.'/';
        return $plugin_path;
    }
    
    private function get_plugin_app_path($plugin_name) {
        $plugin_path = $this->get_plugin_path($plugin_name);
        $plugin_app_path = $plugin_path.'app/';
        return $plugin_app_path;
    }

}

?>
