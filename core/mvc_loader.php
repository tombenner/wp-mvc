<?php

class MvcLoader {

	private $admin_controller_names = array();
	private $core_path = '';
	private $dispatcher = null;
	private $file_includer = null;
	private $model_names = array();
	private $public_controller_names = array();
	private $query_vars = array();

	function __construct() {
	
		if (!defined('MVC_CORE_PATH')) {
			define('MVC_CORE_PATH', MVC_PLUGIN_PATH.'core/');
		}
		
		$this->core_path = MVC_CORE_PATH;
		
		$this->load_core();
		$this->load_plugins();
		
		$this->file_includer = new MvcFileIncluder();
		$this->file_includer->include_all_app_files('config/bootstrap.php');
		$this->file_includer->include_all_app_files('config/routes.php');
		
		$this->dispatcher = new MvcDispatcher();
		
	}
	
	private function load_core() {
		
		$files = array(
			'mvc_error',
			'mvc_configuration',
			'mvc_directory',
			'mvc_dispatcher',
			'mvc_file',
			'mvc_file_includer',
			'mvc_model_registry',
			'mvc_object_registry',
			'mvc_plugin_loader',
			'mvc_templater',
			'mvc_inflector',
			'mvc_router',
			'controllers/mvc_controller',
			'controllers/mvc_admin_controller',
			'controllers/mvc_public_controller',
			'models/mvc_database_adapter',
			'models/mvc_database',
			'models/mvc_data_validation_error',
			'models/mvc_data_validator',
			'models/mvc_model',
			'helpers/mvc_helper',
			'helpers/mvc_form_helper',
			'helpers/mvc_html_helper',
			'shells/mvc_shell',
			'shells/mvc_shell_dispatcher'
		);
		
		foreach ($files as $file) {
			require_once $this->core_path.$file.'.php';
		}
		
	}
	
	private function load_plugins() {
	
		$plugins = $this->get_ordered_plugins();
		$plugin_app_paths = array();
		foreach ($plugins as $plugin) {
			$plugin_app_paths[$plugin] = rtrim(WP_PLUGIN_DIR, '/').'/'.$plugin.'/app/';
		}

		MvcConfiguration::set(array(
			'Plugins' => $plugins,
			'PluginAppPaths' => $plugin_app_paths
		));

		$this->plugin_app_paths = $plugin_app_paths;
	
	}
	
	private function get_ordered_plugins() {
	
		$plugins = get_option('mvc_plugins', array());
		$plugin_app_paths = array();
		
		// Allow plugins to be loaded in a specific order by setting a PluginOrder config value like
		// this ('all' is an optional token; it includes all unenumerated plugins):
		// MvcConfiguration::set(array(
		//		'PluginOrder' => array('my-first-plugin', 'my-second-plugin', 'all', 'my-last-plugin')
		// );
		$plugin_order = MvcConfiguration::get('PluginOrder');
		if (!empty($plugin_order)) {
			$ordered_plugins = array();
			$index_of_all = array_search('all', $plugin_order);
			if ($index_of_all !== false) {
				$first_plugins = array_slice($plugin_order, 0, $index_of_all - 1);
				$last_plugins = array_slice($plugin_order, $index_of_all);
				$middle_plugins = array_diff($plugins, $first_plugins, $last_plugins);
				$plugins = array_merge($first_plugins, $middle_plugins, $last_plugins);
			} else {
				$unordered_plugins = array_diff($plugins, $plugin_order);
				$plugins = array_merge($plugin_order, $unordered_plugins);
			}
		}
		
		return $plugins;
		
	}
	
	public function plugins_loaded() {
		$this->add_admin_ajax_routes();
	}
	
	public function init() {
	
		$this->load_controllers();
		$this->load_libs();
		$this->load_models();
		$this->load_functions();
	
	}
	
	public function register_widgets() {
		foreach ($this->plugin_app_paths as $plugin_app_path) {
			$directory = $plugin_app_path.'widgets/';
			$widget_filenames = $this->file_includer->require_php_files_in_directory($directory);
  
			$pluginReplace = array(
				WP_CONTENT_DIR,
				'/plugins/',
				'/app/'
			);
			
			$plugin = str_replace($pluginReplace, '',$plugin_app_path);

			foreach ($widget_filenames as $widget_file) {
				$widget_name = str_replace('.php', '', $widget_file);
				$widget_class = MvcInflector::camelize($plugin).'_'.MvcInflector::camelize($widget_name);
				register_widget($widget_class);
			}
		}
	}
	
	private function load_controllers() {
	
		foreach ($this->plugin_app_paths as $plugin_app_path) {
		
			$admin_controller_filenames = $this->file_includer->require_php_files_in_directory($plugin_app_path.'controllers/admin/');
			$public_controller_filenames = $this->file_includer->require_php_files_in_directory($plugin_app_path.'controllers/');
			
			foreach ($admin_controller_filenames as $filename) {
				if (preg_match('/admin_([^\/]+)_controller\.php/', $filename, $match)) {
					$this->admin_controller_names[] = $match[1];
				}
			}
			
			foreach ($public_controller_filenames as $filename) {
				if (preg_match('/([^\/]+)_controller\.php/', $filename, $match)) {
					$this->public_controller_names[] = $match[1];
				}
			}
		
		}
		
	}
	
	private function load_libs() {
		
		foreach ($this->plugin_app_paths as $plugin_app_path) {
		
			$this->file_includer->require_php_files_in_directory($plugin_app_path.'libs/');
			
		}
		
	}
	
	private function load_models() {
		
		$models = array();
		
		foreach ($this->plugin_app_paths as $plugin_app_path) {
		
			$model_filenames = $this->file_includer->require_php_files_in_directory($plugin_app_path.'models/');
			
			foreach ($model_filenames as $filename) {
				$models[] = MvcInflector::class_name_from_filename($filename);
			}
		
		}
		
		$this->model_names = array();
		
		foreach ($models as $model) {
			$this->model_names[] = $model;
			$model_class = MvcInflector::camelize($model);
			$model_instance = new $model_class();
			MvcModelRegistry::add_model($model, &$model_instance);
		}
		
	}
	
	private function load_functions() {
	
		$this->file_includer->require_php_files_in_directory($this->core_path.'functions/');
	
	}
	
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
				$tableized = MvcInflector::tableize($model_name);
				$pluralized = MvcInflector::pluralize($model_name);
				$titleized = MvcInflector::titleize($model_name);
				$pluralize_titleized = MvcInflector::pluralize_titleize($model_name);
		
				$controller_name = 'admin_'.$tableized;
			
				$top_level_handle = 'mvc_'.$tableized;
			
				$admin_pages = $model->admin_pages;
			
				$method = $controller_name.'_index';
				$this->dispatcher->{$method} = create_function('', 'MvcDispatcher::dispatch(array("controller" => "'.$controller_name.'", "action" => "index"));');
				add_menu_page(
					$pluralize_titleized,
					$pluralize_titleized,
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
							$admin_page['label'].' &lsaquo; '.$pluralize_titleized,
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
	
	public function flush_rewrite_rules($rules) {
		global $wp_rewrite;
		
		$wp_rewrite->flush_rules();
	}
	
	public function add_rewrite_rules($rules) {
		global $wp_rewrite;
		
		$new_rules = array();
		
		$routes = MvcRouter::get_public_routes();
		
		// Use default routes if none have been defined
		if (empty($routes)) {
			MvcRouter::public_connect('{:controller}', array('action' => 'index'));
			MvcRouter::public_connect('{:controller}/{:id:[\d]+}', array('action' => 'show'));
			MvcRouter::public_connect('{:controller}/{:action}/{:id:[\d]+}');
			$routes = MvcRouter::get_public_routes();
		}
		
		foreach ($routes as $route) {
			
			$route_path = $route[0];
			$route_defaults = $route[1];
			
			if (strpos($route_path, '{:controller}') !== false) {
				foreach ($this->public_controller_names as $controller) {
					$route_rules = $this->get_rewrite_rules($route_path, $route_defaults, $controller);
					$new_rules = array_merge($route_rules, $new_rules);
				}
			} else if (!empty($route_defaults['controller'])) {
				$route_rules = $this->get_rewrite_rules($route_path, $route_defaults, $route_defaults['controller'], 1);
				$new_rules = array_merge($route_rules, $new_rules);
			}
		}
		
		$rules = array_merge($new_rules, $rules);
		$rules = apply_filters('mvc_public_rewrite_rules', $rules);
		return $rules;
	}
	
	private function get_rewrite_rules($route_path, $route_defaults, $controller, $first_query_var_match_index=0) {

		add_rewrite_tag('%'.$controller.'%', '(.+)');
		
		$rewrite_path = $route_path;
		$query_vars = array();
		$query_var_counter = $first_query_var_match_index;
		$query_var_match_string = '';
		
		// Add any route params from the route path (e.g. '{:controller}/{:id:[\d]+}') to $query_vars
		// and append them to the match string for use in a WP rewrite rule
		preg_match_all('/{:(.+?)(:.*?)?}/', $rewrite_path, $matches);
		foreach ($matches[1] as $query_var) {
			$query_var = 'mvc_'.$query_var;
			if ($query_var != 'mvc_controller') {
				$query_var_match_string .= '&'.$query_var.'=$matches['.$query_var_counter.']';
			}
			$query_vars[] = $query_var;
			$query_var_counter++;
		}
		
		// Do the same as above for route params that are defined as route defaults (e.g. array('action' => 'show'))
		if (!empty($route_defaults)) {
			foreach ($route_defaults as $query_var => $value) {
				$query_var = 'mvc_'.$query_var;
				if ($query_var != 'mvc_controller') {
					$query_var_match_string .= '&'.$query_var.'='.$value;
					$query_vars[] = $query_var;
				}
			}
		}
		
		$this->query_vars = array_unique(array_merge($this->query_vars, $query_vars));
		$rewrite_path = str_replace('{:controller}', $controller, $route_path);
		
		// Replace any route params (e.g. {:param_name}) in the route path with the default pattern ([^/]+)
		$rewrite_path = preg_replace('/({:[\w_-]+})/', '([^/]+)', $rewrite_path);
		// Replace any route params with defined patterns (e.g. {:param_name:[\d]+}) in the route path with
		// their pattern (e.g. ([\d]+))
		$rewrite_path = preg_replace('/({:[\w_-]+:)(.*?)}/', '(\2)', $rewrite_path);
		$rewrite_path = '^'.$rewrite_path.'/?$';
		
		$controller_value = empty($route_defaults['controller']) ? $controller : $route_defaults['controller'];
		$controller_rules = array();
		$controller_rules[$rewrite_path] = 'index.php?mvc_controller='.$controller_value.$query_var_match_string;
		
		return $controller_rules;
	}
	
	public function add_query_vars($vars) {
		$vars = array_merge($vars, $this->query_vars);
		return $vars;
	}
	
	public function get_routing_params() {
		global $wp_query;
		
		$controller = $wp_query->get('mvc_controller');
		
		if ($controller) {
			$query_params = $wp_query->query;
			$params = array();
			foreach ($query_params as $key => $value) {
				$key = preg_replace('/^(mvc_)/', '', $key);
				$params[$key] = $value;
			}
			return $params;
		}
		
		return false;
	}
	
	public function template_redirect() {
		global $wp_query, $mvc_params;
		
		$routing_params = $this->get_routing_params();
		
		if ($routing_params) {
			$mvc_params = $routing_params;
			do_action('mvc_public_init', $routing_params);
			$this->dispatcher->dispatch($routing_params);
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