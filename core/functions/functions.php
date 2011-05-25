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
		foreach($plugins as $key => $existing_plugin) {
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