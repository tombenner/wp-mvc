<?php

class Inflector {
	
	public function class_name_from_filename($filename) {
		return Inflector::camelize(str_replace('.php', '', $filename));
	}
	
	public function camelize($string) {
		$string = str_replace('_', ' ', $string);
		$string = ucwords($string);
		$string = str_replace(' ', '', $string);
		return $string;
	}
	
	public function tableize($string) {
		$string = Inflector::underscore($string);
		$string = Inflector::pluralize($string);
		return $string;
	}
	
	public function underscore($string) {
		$string = preg_replace('/[A-Z]/', ' $0', $string);
		$string = trim(strtolower($string));
		$string = str_replace(' ', '_', $string);
		return $string;
	}
	
	public function pluralize($string) {
		return $string.'s';
	}
	
	public function singularize($string) {
		return preg_replace('/s$/', '', $string);
	}
	
	public function titleize($string) {
		$string = preg_replace('/[A-Z]/', ' $0', $string);
		$string = trim(str_replace('_', ' ', $string));
		$string = ucwords($string);
		return $string;
	}

}

?>