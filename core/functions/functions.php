<?php

function mvc_app_path() {
	return MVC_APP_PATH;
}

function mvc_app_url() {
	
	$abspath = rtrim(ABSPATH, '/').'/';
	$site_url = rtrim(site_url(), '/').'/';
	
	$url = str_replace($abspath, $site_url, mvc_app_path());
	return $url;
	
}

function mvc_public_url($options) {
	return Router::public_url($options);
}

function mvc_admin_url($options) {
	return Router::admin_url($options);
}

function mvc_css_url($filename, $options=array()) {

	$defaults = array(
		'add_extension' => true
	);

	$options = array_merge($defaults, $options);
	
	if ($options['add_extension']) {
		if (!preg_match('/\.[\w]{2,4}/', $filename)) {
			$filename .= '.css';
		}
	}
	
	return mvc_app_url().'public/css/'.$filename;
	
}

function mvc_js_url($filename, $options=array()) {

	$defaults = array(
		'add_extension' => true
	);

	$options = array_merge($defaults, $options);
	
	if ($options['add_extension']) {
		if (!preg_match('/\.[\w]{2,4}/', $filename)) {
			$filename .= '.js';
		}
	}
	
	return mvc_app_url().'public/js/'.$filename;
	
}

?>