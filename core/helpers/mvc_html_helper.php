<?php

class MvcHtmlHelper extends MvcHelper {
	
	public function link($text, $url, $options=array()) {
		if (is_array($url)) {
			$url = MvcRouter::public_url($url);
		}
		$defaults = array(
			'href' => $url,
			'title' => $text
		);
		$options = array_merge($defaults, $options);
		$attributes_html = self::attributes_html($options, 'a');
		$html = '<a'.$attributes_html.'>'.$text.'</a>';
		return $html;
	}
	
	public function object_url($object, $options) {
		$defaults = array(
			'id' => $object->__id,
			'action' => 'show',
			'object' => $object
		);
		$options = array_merge($defaults, $options);
		$url = MvcRouter::public_url($options);
		return $url;
	}
	
	public function object_link($object, $options) {
		$url = self::object_url($object, $options);
		$text = empty($options['text']) ? $object->__name : $options['text'];
		return self::link($text, $url, $options);
	}
	
	public function admin_object_url($object, $options) {
		$defaults = array(
			'id' => $object->__id,
			'object' => $object
		);
		$options = array_merge($defaults, $options);
		$url = MvcRouter::admin_url($options);
		return $url;
	}
	
	public function admin_object_link($object, $options) {
		$url = self::admin_object_url($object, $options);
		$text = empty($options['text']) ? $object->__name : $options['text'];
		return self::link($text, $url);
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