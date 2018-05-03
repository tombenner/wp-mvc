<?php

class MvcFileIncluder {

    private $core_path = '';
    private $plugin_paths = array();
    private $theme_path = '';

    function __construct() {
        $this->core_path = MVC_CORE_PATH;
        $this->plugin_app_paths = MvcConfiguration::get('PluginAppPaths');
        $this->theme_path = get_stylesheet_directory();
    }

    public function find_theme_or_view_file($filepath)
    {
		$possible_filepaths = array( $filepath, str_replace( '_', '-', $filepath ) );
		foreach ( $possible_filepaths as $filepath ) {
			foreach(MvcConfiguration::get('Plugins') as $plugin) {
				if (file_exists($this->theme_path."/$plugin/$filepath.php")) {
					return $this->theme_path."/$plugin/$filepath.php";
				}
			}

			$app_or_cor_file = $this->find_first_app_file_or_core_file("views/$filepath.php");
			if ( $app_or_cor_file ) {
				return $app_or_cor_file;
			}
		}
		return false;
    }

    public function find_first_app_file_or_core_file($filepath) {
        foreach ($this->plugin_app_paths as $plugin_app_path) {
            if (file_exists($plugin_app_path.$filepath)) {
                return $plugin_app_path.$filepath;
            }
        }
        if (file_exists($this->core_path.'pluggable/'.$filepath)) {
            return $this->core_path.'pluggable/'.$filepath;
        }
        if (file_exists($this->core_path.$filepath)) {
            return $this->core_path.$filepath;
        }
        return false;
    }

    public function require_first_app_file_or_core_file($filepath) {
        if ($this->include_first_app_file($filepath)) {
            return true;
        }
        if ($this->include_core_file('pluggable/'.$filepath)) {
            return true;
        }
        if ($this->require_core_file($filepath)) {
            return true;
        }
        return false;
    }

    public function require_all_app_files_or_core_file($filepath) {
        if ($this->include_all_app_files($filepath)) {
            return true;
        }
        if ($this->include_core_file('pluggable/'.$filepath)) {
            return true;
        }
        if ($this->require_core_file($filepath)) {
            return true;
        }
        return false;
    }

    public function require_core_file($filepath) {
        if ($this->include_core_file('pluggable/'.$filepath)) {
            return true;
        }
        if ($this->require_file($this->core_path.$filepath)) {
            return true;
        }
        return false;
    }

    public function require_first_app_file($filepath) {
        foreach ($this->plugin_app_paths as $plugin_app_path) {
            if ($this->include_file($plugin_app_path.$filepath)) {
                return true;
            }
        }
        MvcError::fatal('The file "'.$filepath.'" couldn\'t be found in any apps.');
    }

    public function include_all_app_files($filepath) {
        $included = false;
        foreach ($this->plugin_app_paths as $plugin_app_path) {
            if ($this->include_file($plugin_app_path.$filepath)) {
                $included = true;
            }
        }
        return $included;
    }

    public function include_core_file($filepath) {
        return $this->include_file($this->core_path.$filepath);
    }

    public function include_first_app_file($filepath) {
        foreach ($this->plugin_app_paths as $plugin_app_path) {
            if ($this->include_file($plugin_app_path.$filepath)) {
                return true;
            }
        }
        return false;
    }

    public function include_first_app_file_or_core_file($filepath) {
        foreach ($this->plugin_app_paths as $plugin_app_path) {
            if ($this->include_file($plugin_app_path.$filepath)) {
                return true;
            }
        }
        if (!$this->include_core_file('pluggable/'.$filepath)) {
            if (!$this->include_core_file($filepath)) {
                return false;
            }
        }
        return true;
    }

    public function require_all_app_files($filepath) {
        $included = false;
        foreach ($this->plugin_app_paths as $plugin_app_path) {
            if ($this->include_require_file($plugin_app_path.$filepath)) {
                $included = true;
            }
        }
        return $included;
    }

    public function get_php_files_in_directory($directory, $options=array()) {

        $filenames = array();
        if (is_dir($directory)) {
            if (isset($options['recursive'])) {
                $filenames = $this->scandir_recursive($directory);
            } else {
                $filenames = scandir($directory);
            }
            $filenames = array_filter($filenames, array($this, 'is_php_file'));
        }

        return $filenames;

    }

    public function require_php_files_in_directory($directory, $options=array()) {

        $filenames = $this->get_php_files_in_directory($directory);
        $filepaths = array();

        foreach ($filenames as $filename) {
            $filepath = $directory.$filename;
            $filepaths[] = $filepath;
            $this->require_file($filepath);
        }

        return $filenames;

    }

    private function require_file($filepath) {
        require_once $filepath;
        return true;
    }

    private function include_file($filepath) {
        if (file_exists($filepath)) {
            $this->require_file($filepath);
            return true;
        }
        return false;
    }

    private function is_php_file($filename) {
        return preg_match('/.+\.php$/', $filename);
    }

    private function scandir_recursive($directory) {

        $file_tmp = glob($directory.'*', GLOB_MARK | GLOB_NOSORT);
        $files = array();

        foreach ($file_tmp as $item){
            if (substr($item, -1) != DIRECTORY_SEPARATOR) {
                $files[] = $item;
            } else {
                $files = array_merge($files, $this->scandir_recursive($item));
            }
        }

        return $files;

    }

}

?>
