<?php

class MvcHtmlHelper extends MvcHelper {
	
	public function link($title, $url, $options=array()) {
		
		if (is_array($url)) {
			$url = Router::public_url($url);
		}
	
		$defaults = array(
			'href' => $url,
			'title' => $title
		);
		$options = array_merge($defaults, $options);
	
		$attributes_html = self::attributes_html($options, 'a');
		
		$html = '<a'.$attributes_html.'>'.$title.'</a>';
		return $html;
		
	}
	
	public function object_url($object, $options) {
		$options['id'] = $object->__id;
		$url = Router::public_url($options);
		return $url;
	}
	
	public function object_link($object, $options) {
		$url = self::object_url($object, $options);
		$title = empty($options['title']) ? $object->__name : $options['title'];
		return self::link($title, $url);
	}
	
	public function admin_object_url($object, $options) {
		$options['id'] = $object->__id;
		$url = Router::admin_url($options);
		return $url;
	}
	
	public function admin_object_link($object, $options) {
		$url = self::admin_object_url($object, $options);
		$title = $object->__name;
		return self::link($title, $url);
	}
	
	public function __call($method, $args) {
		if (property_exists($this, $method)) {
			if (is_callable($this->$method)) {
				return call_user_func_array($this->$method, $args);
			}
		}
	}
	
}

?>