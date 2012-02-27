<?php

class MvcWpPost extends MvcModel {

	var $table = '{prefix}posts';
	var $primary_key = 'ID';
	var $order = 'post_date DESC';
	var $display_field = 'post_title';
	
}

?>