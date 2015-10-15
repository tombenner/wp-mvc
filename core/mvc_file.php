<?php

// To do: make this more robust and add error messages
class MvcFile {

    private $path = '';

    function __construct($path=null) {
        $this->path = $path;
    }
    
    public function write($content, $path=null, $options=array()) {
        $defaults = array(
            'create_nonexistent_parent_directories' => true
        );
        $options = array_merge($defaults, $options);
        
        if (!$path) {
            $path = $this->path;
        }
        if (!$path) {
            return false;
        }
        if ($options['create_nonexistent_parent_directories']) {
            $directory = new MvcDirectory();
            $directories = explode('/', dirname($path));
            for ($i=1; $i<=count($directories); $i++) {
                $directory_path = implode('/', array_slice($directories, 0, $i));
                if (!$directory->exists($directory_path)) {
                    $directory->create($directory_path);
                }
            }
        }
        $handle = fopen($path, 'w');
        fwrite($handle, $content);
        fclose($handle);
    }
    
    public function delete($path=null) {
        if (!$path) {
            $path = $this->path;
        }
        if (!$path) {
            return false;
        }
        if (is_file($path)) {
            unlink($path);
        }
    }

}

?>