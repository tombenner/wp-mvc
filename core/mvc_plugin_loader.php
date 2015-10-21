<?php

class MvcPluginLoader {
    
    protected $wpdb = null;
    protected $file_includer = null;
    
    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->file_includer = new MvcFileIncluder();
        $this->init();
    }
    
    public function init() {
    }
    
    public function activate_app($file_path) {
        $plugin = $this->get_plugin_name_from_file_path($file_path);
        $this->add_plugin($plugin);
        $this->init_settings_defaults($file_path);
        $this->load_plugin_routes();
    }
    
    public function deactivate_app($file_path) {
        $plugin = $this->get_plugin_name_from_file_path($file_path);
        $this->remove_plugin($plugin);
    }
    
    protected function add_plugin($plugin) {
        $added = false;
        $plugins = $this->get_plugins();
        if (!in_array($plugin, $plugins)) {
            $plugins[] = $plugin;
            $added = true;
        }
        $this->update_registered_plugins($plugins);
        return $added;
    }
    
    protected function remove_plugin($plugin) {
        $removed = false;
        $plugins = $this->get_plugins();
        if (in_array($plugin, $plugins)) {
            foreach ($plugins as $key => $existing_plugin) {
                if ($plugin == $existing_plugin) {
                    unset($plugins[$key]);
                    $removed = true;
                }
            }
            $plugins = array_values($plugins);
        }
        $this->update_registered_plugins($plugins);
        return $removed;
    }
    
    protected function update_registered_plugins($plugins) {
        $plugins = $this->filter_nonexistent_plugins($plugins);
        update_option('mvc_plugins', $plugins);
    }
    
    protected function filter_nonexistent_plugins($plugins) {
        foreach ($plugins as $key => $plugin) {
            if (!is_dir(WP_PLUGIN_DIR.'/'.$plugin)) {
                unset($plugins[$key]);
            }
        }
        $plugins = array_values($plugins);
        return $plugins;
    }
    
    protected function get_plugin_name_from_file_path($file_path) {
        $basename = plugin_basename($file_path);
        $basename_split = explode('/', $basename);
        if (!empty($basename_split[0])) {
            return $basename_split[0];
        }
        MvcError::fatal('A plugin name couldn\'t be derived from the file path "'.$file_path.'"');
    }
    
    protected function get_plugins() {
        $plugins = get_option('mvc_plugins', array());
        return $plugins;
    }
    
    public function init_settings_defaults($file_path) {
        $app_directory = dirname($file_path).'/app/';
        $settings_directory = $app_directory.'settings/';
        $settings_files = $this->file_includer->require_php_files_in_directory($settings_directory);
        if (!empty($settings_files)) {
            foreach ($settings_files as $file_path) {
                $settings_class = MvcInflector::class_name_from_filename(basename($file_path));
                $settings = new $settings_class();
                $existing_values = get_option($settings->key);
                if (!$existing_values) {
                    $values = array();
                    foreach ($settings->settings as $key => $setting) {
                        $value = null;
                        if (!empty($setting['default'])) {
                            $value = $setting['default'];
                        } else if (!empty($setting['default_method'])) {
                            $value = $this->{$setting['default_method']}();
                        }
                        $values[$key] = $value;
                    }
                    update_option($settings->key, $values);
                }
            }
        }
    }

    protected function load_plugin_routes() {
        require_once MVC_PLUGIN_PATH.'core/loaders/mvc_public_loader.php';
        $public_loader = new MvcPublicLoader();
        $public_loader->init();
        $public_loader->load_rewrite_rules();
        $public_loader->flush_rewrite_rules();
    }
    
}
