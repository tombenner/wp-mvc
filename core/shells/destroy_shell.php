<?php

/**
 *  Tool to automate deletion of files created by the Generate shell
 */
class DestroyShell extends MvcShell {

    /**
     * Deletes controller source files 
     * wpmvc destroy <plugin> <resource>
     * @param type $args 
     */
    public function controllers($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->destroy_controllers($plugin, $name);
    }

    /**
     * Deletes model source files
     * wpmvc destroy <plugin> <resource>
     * @param type $args 
     */
    public function model($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->destroy_model($plugin, $name);
    }

    /**
     * Deletes model, controller, and view files for a resource
     * wpmvc destroy <plugin> <resource>
     * @param type $args 
     */
    public function scaffold($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->destroy_controllers($plugin, $name);
        $this->destroy_model($plugin, $name);
        $this->destroy_views($plugin, $name);
    }

    /**
     * Deletes view source files for a resource
     * wpmvc destroy views <plugin> <resource>
     * @param type $args 
     */
    public function views($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->destroy_views($plugin, $name);
    }
    
    /**
     * Delete a generated widget 
     * @param mixed $args 
     */
    public function widget($args) {
        list($plugin, $name) = $this->get_plugin_model_args($args);
        $this->destroy_widget($plugin, $name);   
    }
    
    private function destroy_controllers($plugin, $name) {
        $plugin_app_path = $this->get_plugin_app_path($plugin);
        $file = new MvcFile();
        $name_tableized = MvcInflector::tableize($name);
        $target_directory = $plugin_app_path.'controllers/';
        $file->delete($target_directory.$name_tableized.'_controller.php');
        $file->delete($target_directory.'admin/admin_'.$name_tableized.'_controller.php');
    }
    
    private function destroy_model($plugin, $name) {
        $plugin_app_path = $this->get_plugin_app_path($plugin);
        $file = new MvcFile();
        $name_underscore = MvcInflector::underscore($name);
        $target_path = $plugin_app_path.'models/'.$name_underscore.'.php';
        $file->delete($target_path);
    }
    
    private function destroy_views($plugin, $name) {
        $plugin_app_path = $this->get_plugin_app_path($plugin);
        $name_tableized = MvcInflector::tableize($name);
        $public_directory = $plugin_app_path.'views/'.$name_tableized;
        $admin_directory = $plugin_app_path.'views/admin/'.$name_tableized;
        $directory = new MvcDirectory();
        $directory->delete($public_directory);
        $directory->delete($admin_directory);
    }
    
    private function destroy_widget($plugin, $name) {
        $plugin_app_path = $this->get_plugin_app_path($plugin);
        $file = new MvcFile();
        $name_underscore = MvcInflector::underscore($name);
        $target_path = $plugin_app_path.'widgets/'.$name_underscore.'.php';
        $file->delete($target_path);
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