<?php

require_once 'mvc_loader.php';

class MvcAdminLoader extends MvcLoader {
	
	public function admin_init() {
		
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
			session_start();
		 
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
		
		foreach ($this->model_names as $model_name) {
		
			$model = MvcModelRegistry::get_model($model_name);
			
			if (!$model->hide_menu) {
				$model_tableized = MvcInflector::tableize($model_name);
				$model_pluralize_titleized = MvcInflector::pluralize_titleize($model_name);
		
				$controller_name = 'admin_'.$model_tableized;
			
				$top_level_handle = 'mvc_'.$model_tableized;
			
				$admin_pages = $model->admin_pages;
			
				$method = $controller_name.'_index';
				$this->dispatcher->{$method} = create_function('', 'MvcDispatcher::dispatch(array("controller" => "'.$controller_name.'", "action" => "index"));');
				add_menu_page(
					$model_pluralize_titleized,
					$model_pluralize_titleized,
					'administrator',
					$top_level_handle,
					array($this->dispatcher, $method),
					null,
					$menu_position
				);
			
				foreach ($admin_pages as $key => $admin_page) {
				
					$method = $controller_name.'_'.$admin_page['action'];
				
					if (!method_exists($this->dispatcher, $method)) {
						$this->dispatcher->{$method} = create_function('', 'MvcDispatcher::dispatch(array("controller" => "'.$controller_name.'", "action" => "'.$admin_page['action'].'"));');
					}
				
					$page_handle = $top_level_handle.'-'.$key;
				
					if ($admin_page['in_menu']) {
						add_submenu_page(
							$top_level_handle,
							$admin_page['label'].' &lsaquo; '.$model_pluralize_titleized,
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