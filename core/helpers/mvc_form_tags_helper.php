<?php

class MvcFormTagsHelper extends MvcHelper {
    
    // Generalized method that chooses the appropriate input type based on the SQL type of the field
    static function input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'type' => 'text',
            'label' => null,
            'value' => null
        );
        $options = array_merge($defaults, $options);
        $method = $options['type'].'_input';
        $html = self::$method($field_name, $options);
        return $html;
    }
    
    static function text_input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'type' => 'text'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = self::before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= self::after_input($field_name, $options);
        return $html;
    }

     static function password_input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'type' => 'password'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = self::before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= self::after_input($field_name, $options);
        return $html;
    }

    static function wp_editor_input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'value' => '',
            'settings' => array('textarea_name' => self::input_name($field_name)),
        );
        $options = array_merge($defaults, $options);
        $options['settings']['textarea_name'] = $options['name'];
        $html = self::before_input($field_name, $options);
        ob_start();
        wp_editor( $options['value'], $options['id'], $options['settings'] );
        $html .= ob_get_contents();
        ob_end_clean();
        $html .= self::after_input($field_name, $options);
        return $html;
    }
    
    static function textarea_input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'value' => ''
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'textarea');
        $html = self::before_input($field_name, $options);
        $html .= '<textarea'.$attributes_html.'>'.$options['value'].'</textarea>';
        $html .= self::after_input($field_name, $options);
        return $html;
    }
    
    static function number_input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'type' => 'number'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = self::before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= self::after_input($field_name, $options);
        return $html;
    }
    
    static function email_input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'type' => 'email'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = self::before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= self::after_input($field_name, $options);
        return $html;
    }
    
    static function checkbox_input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'type' => 'checkbox',
            'checked' => false,
            'value' => '1',
            'include_hidden_input' => true
        );
        $options = array_merge($defaults, $options);
        if (!$options['checked']) {
            unset($options['checked']);
        } else {
            $options['checked'] = 'checked';
        }
        $attributes_html = self::attributes_html($options, 'input');
        $html = self::before_input($field_name, $options);
        if ($options['include_hidden_input']) {
            // Included to allow for a workaround to the issue of unchecked checkbox fields not being sent by clients
            $html .= '<input type="hidden" name="'.self::esc_attr($options['name']).'" value="0" />';
        }
        $html .= '<input'.$attributes_html.' />';
        $html .= self::after_input($field_name, $options);
        return $html;
    }
    
    static function hidden_input($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'type' => 'hidden'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = '<input'.$attributes_html.' />';
        return $html;
    }
    
    static function select_input($field_name, $options=array()) {
        $html = self::before_input($field_name, $options);
        $html .= self::select_tag($field_name, $options);
        $html .= self::after_input($field_name, $options);
        return $html;
    }
    
    static function select_tag($field_name, $options=array()) {
        $defaults = array(
            'id' => self::input_id($field_name),
            'name' => self::input_name($field_name),
            'empty' => false,
            'value' => null
        );
        
        $options = array_merge($defaults, $options);
        $options['options'] = empty($options['options']) ? array() : $options['options'];
        $attributes_html = self::attributes_html($options, 'select');
        $html = '<select'.$attributes_html.'>';
        if ($options['empty']) {
            $empty_name = is_string($options['empty']) ? $options['empty'] : '';
            $html .= '<option value="">'.$empty_name.'</option>';
        }
        foreach ($options['options'] as $key => $value) {
            if (is_object($value)) {
                $key = $value->__id;
                $value = $value->__name;
            }
            $selected_attribute = $options['value'] == $key ? ' selected="selected"' : '';
            $html .= '<option value="'.self::esc_attr($key).'"'.$selected_attribute.'>'.$value.'</option>';
        }
        $html .= '</select>';
        return $html;
    }
    
    static function button($text, $options=array()) {
        $defaults = array(
            'id' => self::input_id($text),
            'type' => 'button',
            'class' => 'button'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = '<button'.$attributes_html.'>'.$text.'</button>';
        return $html;
    }
    
    static private function before_input($field_name, $options) {
        $defaults = array(
            'before' => ''
        );
        $options = array_merge($defaults, $options);
        $html = $options['before'];
        if (!empty($options['label'])) {
            $html .= '<label for="'.$options['id'].'">'.$options['label'].'</label>';
        }
        return $html;
    }
    
    static private function after_input($field_name, $options) {
        $defaults = array(
            'after' => ''
        );
        $options = array_merge($defaults, $options);
        $html = $options['after'];
        return $html;
    }
    
    static private function input_id($field_name) {
        return $field_name;
    }
    
    static private function input_name($field_name) {
        return $field_name;
    }

}

?>
