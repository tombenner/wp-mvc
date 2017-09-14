<?php

function mvc_plugin_app_path($plugin) {
    $plugin_app_paths = MvcConfiguration::get('PluginAppPaths');
    return $plugin_app_paths[$plugin];
}

function mvc_plugin_app_url($plugin) {
    $abspath = rtrim(ABSPATH, '/').'/';
    $site_url = rtrim(site_url(), '/').'/';
    $url = str_replace($abspath, $site_url, mvc_plugin_app_path($plugin));
    return $url;
}

function mvc_model($model_name) {
    $model_underscore = MvcInflector::underscore($model_name);
    $file_includer = new MvcFileIncluder();
    $file_includer->include_first_app_file('models/'.$model_underscore.'.php');
    if (class_exists($model_name)) {
        return new $model_name();
    }

    throw new Exception('Unable to load the "'.$model_name.'" model.');
}

function mvc_setting($settings_name, $setting_key) {
    $settings_name = 'mvc_'.MvcInflector::underscore($settings_name);
    $option = get_option($settings_name);
    if (isset($option[$setting_key])) {
        return $option[$setting_key];
    }
    return null;
}

function mvc_render_to_string($view, $vars=array()) {
    $view_pieces = explode('/', $view);
    $model_tableized = $view_pieces[0];
    $model_camelized = MvcInflector::camelize($model_tableized);
    $controller_name = $model_camelized.'Controller';
    if (!class_exists($controller_name)) {
        $controller_name = 'MvcPublicController';
    }
    $controller = new $controller_name();
    $controller->init();
    $controller->set($vars);
    $string = $controller->render_to_string($view);
    return $string;
}

function mvc_public_url($options) {
    return MvcRouter::public_url($options);
}

function mvc_admin_url($options) {
    return MvcRouter::admin_url($options);
}

function mvc_css_url($plugin, $filename, $options=array()) {

    $defaults = array(
        'add_extension' => true
    );

    $options = array_merge($defaults, $options);
    
    if ($options['add_extension']) {
        if (!preg_match('/\.[\w]{2,4}/', $filename)) {
            $filename .= '.css';
        }
    }
    
    return mvc_plugin_app_url($plugin).'public/css/'.$filename;
    
}

function mvc_js_url($plugin, $filename, $options=array()) {

    $defaults = array(
        'add_extension' => true
    );

    $options = array_merge($defaults, $options);
    
    if ($options['add_extension']) {
        if (!preg_match('/\.[\w]{2,4}/', $filename)) {
            $filename .= '.js';
        }
    }
    
    return mvc_plugin_app_url($plugin).'public/js/'.$filename;
    
}

function mvc_add_plugin($plugin) {
    $added = false;
    $plugins = mvc_get_plugins();
    if (!in_array($plugin, $plugins)) {
        $plugins[] = $plugin;
        $added = true;
    }
    update_option('mvc_plugins', $plugins);
    return $added;
}

function mvc_remove_plugin($plugin) {
    $removed = false;
    $plugins = mvc_get_plugins();
    if (in_array($plugin, $plugins)) {
        foreach ($plugins as $key => $existing_plugin) {
            if ($plugin == $existing_plugin) {
                unset($plugins[$key]);
                $removed = true;
            }
        }
        $plugins = array_values($plugins);
    }
    update_option('mvc_plugins', $plugins);
    return $removed;
}

function mvc_get_plugins() {
    $plugins = get_option('mvc_plugins', array());
    return $plugins;
}

function is_mvc_page() {
    global $mvc_params;
    if (!empty($mvc_params['controller'])) {
        return true;
    }
    return false;
}

?>