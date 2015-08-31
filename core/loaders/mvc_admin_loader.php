<?php

require_once 'mvc_loader.php';

class MvcAdminLoader extends MvcLoader {
    
    public $settings = null;
    
    public function admin_init() {
        $this->load_controllers();
        $this->load_settings();
        $this->register_settings();
        $this->dispatch();
    }
    
    public function dispatch() {
        
        global $plugin_page;
        
        // If the beginning of $plugin_page isn't 'mvc_', then this isn't a WP MVC-generated page
        if (substr($plugin_page, 0, 4) != 'mvc_') {
            return false;
        }
        
        $plugin_page_split = explode('-', $plugin_page, 2);
        
        $controller = $plugin_page_split[0];
        // Remove 'mvc_' from the beginning of the controller value
        $controller = substr($controller, 4);
        
        if (!empty($controller)) {
        
            global $title;
            
            // Necessary for flash()-related functionality
            if (session_id() == '') {
                session_start();
            }

            $action = empty($plugin_page_split[1]) ? 'index' : $plugin_page_split[1];
            
            $mvc_admin_init_args = array(
                'controller' => $controller,
                'action' => $action
            );
            do_action('mvc_admin_init', $mvc_admin_init_args);
        
            $title = MvcInflector::titleize($controller);
            if (!empty($action) && $action != 'index') {
                $title = MvcInflector::titleize($action).' &lsaquo; '.$title;
            }
            $title = apply_filters('mvc_admin_title', $title);
        
        }
    
    }
    
    public function add_menu_pages() {

        
        global $_registered_pages;
    
        $menu_position = 12;
        
        $menu_position = apply_filters('mvc_menu_position', $menu_position);
        
        $admin_pages = MvcConfiguration::get('AdminPages');
        
        foreach ($this->admin_controller_names as $controller_name) {
        
            if (isset($admin_pages[$controller_name])) {
                if (empty($admin_pages[$controller_name]) || !$admin_pages[$controller_name]) {
                    continue;
                }
                $pages = $admin_pages[$controller_name];
            } else {
                $pages = null;
            }
            
            $processed_pages = $this->process_admin_pages($controller_name, $pages);
            
            $hide_menu = isset($pages['hide_menu']) ? $pages['hide_menu'] : false;
            
            if (!$hide_menu) {

                $menu_icon = 'dashicons-admin-generic'; 
                /* check if there is a corresponding model with a menu_icon post type argument */
                try {
                    $model_name = MvcInflector::singularize(MvcInflector::camelize($controller_name));
                    $model = mvc_model($model_name);
                    if(isset($model->wp_post['post_type']['args']['menu_icon'])) {
                        $menu_icon = $model->wp_post['post_type']['args']['menu_icon'];    
                    }
                } catch (Exception $e) {
                    ; //not every controller must have a corresponding model, continue silently
                }

                $controller_titleized = MvcInflector::titleize($controller_name);
        
                $admin_controller_name = 'admin_'.$controller_name;
            
                $top_level_handle = 'mvc_'.$controller_name;

            
                $method = $admin_controller_name.'_index';
                $this->dispatcher->{$method} = create_function('', 'MvcDispatcher::dispatch(array("controller" => "'.$admin_controller_name.'", "action" => "index"));');
                $capability = $this->admin_controller_capabilities[ $controller_name ];
                add_menu_page(
                    $controller_titleized,
                    $controller_titleized,
                    $capability,
                    $top_level_handle,
                    array($this->dispatcher, $method),
                    $menu_icon,
                    $menu_position
                );
            
                foreach ($processed_pages as $key => $admin_page) {
                
                    $method = $admin_controller_name.'_'.$admin_page['action'];
                
                    if (!method_exists($this->dispatcher, $method)) {
                        $this->dispatcher->{$method} = create_function('', 'MvcDispatcher::dispatch(array("controller" => "'.$admin_controller_name.'", "action" => "'.$admin_page['action'].'"));');
                    }
                
                    $page_handle = $top_level_handle.'-'.$key;
                    $parent_slug = empty($admin_page['parent_slug']) ? $top_level_handle : $admin_page['parent_slug'];
                
                    if ($admin_page['in_menu']) {
                        add_submenu_page(
                            $parent_slug,
                            $admin_page['label'].' &lsaquo; '.$controller_titleized,
                            $admin_page['label'],
                            $admin_page['capability'],
                            $page_handle,
                            array($this->dispatcher, $method)
                        );
                    } else {
                        // It looks like there isn't a more native way of creating an admin page without
                        // having it show up in the menu, but if there is, it should be implemented here.
                        // To do: set up capability handling and page title handling for these pages that aren't in the menu
                        $hookname = get_plugin_page_hookname($page_handle,'');
                        if (!empty($hookname)) {
                            add_action($hookname, array($this->dispatcher, $method));
                        }
                        $_registered_pages[$hookname] = true;
                    }
            
                }
                $menu_position++;

            }
            
        }
    
    }
    
    protected function process_admin_pages($controller_name, $pages) {
    
        $titleized = MvcInflector::titleize($controller_name);
    
        $default_pages = array(
            'add' => array(
                'label' => 'Add New'
            ),
            'delete' => array(
                'label' => 'Delete '.$titleized,
                'in_menu' => false
            ),
            'edit' => array(
                'label' => 'Edit '.$titleized,
                'in_menu' => false
            )
        );
        
        if (!$pages) {
            $pages = $default_pages;
        }
        
        $processed_pages = array();
        
        foreach ($pages as $key => $value) {
            if (is_int($key)) {
                $key = $value;
                $value = array();
            }
            if (!is_array($value)) {
                continue;
            }
                    $capability = $this->admin_controller_capabilities[ $controller_name ];
            $defaults = array(
                'action' => $key,
                'in_menu' => true,
                'label' => MvcInflector::titleize($key),
                'capability' => $capability
            );
            if (isset($default_pages[$key])) {
                $value = array_merge($default_pages[$key], $value);
            }
            $value = array_merge($defaults, $value);
            $processed_pages[$key] = $value;
        }
        
        return $processed_pages;
    }

    public function init_settings() {
        $this->settings = array();
        if (!empty($this->settings_names) && empty($this->settings)) {
            foreach ($this->settings_names as $settings_name) {
                $instance = MvcSettingsRegistry::get_settings($settings_name);
                $this->settings[$settings_name] = array(
                    'settings' => $instance->settings
                );
            }
        }
    }
    
    public function register_settings() {
        $this->init_settings();
        foreach ($this->settings as $settings_name => $settings) {
            $instance = MvcSettingsRegistry::get_settings($settings_name);
            $title = $instance->title;
            $settings_key = $instance->key;
            $section_key = $settings_key.'_main';
            add_settings_section($section_key, '', array($instance, 'description'), $settings_key);
            register_setting($settings_key, $settings_key, array($instance, 'validate_fields'));
            foreach ($instance->settings as $setting_key => $setting) {
                add_settings_field($setting_key, $setting['label'], array($instance, 'display_field_'.$setting_key), $settings_key, $section_key);
            }
        }
    }
    
    public function add_settings_pages() {
        $this->init_settings();
        foreach ($this->settings as $settings_name => $settings) {
            $title = MvcInflector::titleize($settings_name);
            $title = str_replace(' Settings', '', $title);
            $instance = MvcSettingsRegistry::get_settings($settings_name);
            add_options_page($title, $title, 'manage_options', $instance->key, array($instance, 'page'));
        }
    }
    
    public function add_admin_ajax_routes() {
        $routes = MvcRouter::get_admin_ajax_routes();
        if (!empty($routes)) {
            foreach ($routes as $route) {
                $route['is_admin_ajax'] = true;
                $method = 'admin_ajax_'.$route['wp_action'];
                $this->dispatcher->{$method} = create_function('', 'MvcDispatcher::dispatch(array("controller" => "'.$route['controller'].'", "action" => "'.$route['action'].'"));die();');
                add_action('wp_ajax_'.$route['wp_action'], array($this->dispatcher, $method));
            }
        }
    
    }

}

?>
