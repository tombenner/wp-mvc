<?php

class MvcSettings {

    public $name = null;
    public $title = null;
    public $key = null;
    public $settings = null;
    
    function __construct() {
        $this->name = get_class($this);
        $this->title = MvcInflector::titleize($this->name);
        $this->key = 'mvc_'.MvcInflector::underscore($this->name);
        $this->init_settings();
    }
    
    public function description() {
    }

    public function page() {
        echo '<div>';
        echo '<h2>'.$this->title.'</h2>';
        echo '<form action="options.php" method="post">';
        settings_fields($this->key);
        do_settings_sections($this->key);
        echo '<input name="Submit" type="submit" value="'.esc_attr('Save Changes').'" />';
        echo '</form>';
        echo '</div>';
    }
    
    public function display_field($setting_key) {
        $setting = $this->settings[$setting_key];
        $options = get_option($this->key);
        $value = isset($options[$setting_key]) ? $options[$setting_key] : null;
        if (is_null($value)) {
            if (!empty($setting['default'])) {
                $value = $setting['default'];
            } else if (!empty($setting['default_method'])) {
                $value = $this->{$setting['default_method']}();
            }
        }
        $input_value = $value;
        if ($setting['type'] == 'checkbox') {
            $input_value = '1';
        }
        $input_options = array(
            'id' => $setting['key'],
            'name' => $this->key.'['.$setting['key'].']',
            'type' => $setting['type'],
            'value' => $input_value
        );
        if ($setting['type'] == 'checkbox' && $value) {
            $input_options['checked'] = 'checked';
        }
        if ($setting['type'] == 'select') {
            if ($setting['options']) {
                $input_options['options'] = $setting['options'];
            } else if ($setting['options_method']) {
                $input_options['options'] = $this->{$setting['options_method']}();
            }
        }
        $html = MvcFormTagsHelper::input($setting_key, $input_options);
        echo $html;
    }
    
    public function validate_fields($inputs) {
        foreach ($inputs as $setting_key => $value) {
            if (method_exists($this, 'validate_field_'.$setting_key)) {
                $inputs[$setting_key] = $this->{'validate_field_'.$setting_key}($setting_key, $value);
            } else {
                $inputs[$setting_key] = $this->validate_field($setting_key, $value);
            }
        }
        return $inputs;
    }
    
    public function validate_field($setting_key, $value) {
        return $value;
    }
    
    protected function init_settings() {
        if (empty($this->settings)) {
            $this->settings = array();
        }
        $settings = array();
        foreach ($this->settings as $key => $setting) {
            $defaults = array(
                'key' => $key,
                'type' => 'text',
                'label' => MvcInflector::titleize($key),
                'value' => null,
                'value_method' => null,
                'default' => null,
                'default_method' => null,
                'options' => null,
                'options_method' => null
            );
            $setting = array_merge($defaults, $setting);
            $settings[$key] = $setting;
        }
        $this->settings = $settings;
    }

    public function __call($method_name, $arguments) {
        if (substr($method_name, 0, 14) == 'display_field_') {
            $setting_key = substr($method_name, 14);
            $this->display_field($setting_key);
            return true;
        }
        if (substr($method_name, 0, 15) == 'validate_field_') {
            $setting_key = substr($method_name, 15);
            $this->validate_field($setting_key, $arguments[0]);
            return true;
        }
        MvcError::fatal('Undefined method: '.get_class($this).'::'.$method_name.'.');
    }

}

?>