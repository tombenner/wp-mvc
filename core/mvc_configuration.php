<?php

class MvcConfiguration {

    static function &get_instance($boot = true) {
        static $instance = array();
        
        if (!$instance) {
            $mvc_configuration = new MvcConfiguration();
            $instance[0] =& $mvc_configuration;
        }
        
        return $instance[0];
    }

    static function set($config, $value = null) {
        $_this =& MvcConfiguration::get_instance();

        if (!is_array($config)) {
            $config = array($config => $value);
        }

        foreach ($config as $name => $value) {
            if (strpos($name, '.') === false) {
                $_this->{$name} = $value;
            } else {
                $names = explode('.', $name, 2);
                if (count($names) == 2) {
                    $_this->{$names[0]}[$names[1]] = $value;
                }
            }
        }
        
        return true;
    }

    static function append($config, $value = null) {
        $_this =& MvcConfiguration::get_instance();

        if (!is_array($config)) {
            $config = array($config => $value);
        }

        foreach ($config as $name => $value) {
            if (empty($_this->{$name})) {
                $_this->{$name} = $value;
            } else {
                $_this->{$name} = array_merge($_this->{$name}, $value);
            }
        }
        
        return true;
    }

    static function get($config) {
        $_this =& MvcConfiguration::get_instance();

        if (strpos($config, '.') !== false) {
            $names = explode('.', $config, 2);
            $config = $names[0];
        }
        if (!isset($_this->{$config})) {
            return null;
        }
        if (!isset($names[1])) {
            return $_this->{$config};
        }
        if (count($names) == 2) {
            if (isset($_this->{$config}[$names[1]])) {
                return $_this->{$config}[$names[1]];
            }
        }
        return null;
    }

}

?>
