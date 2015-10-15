<?php

class MvcFormHelper extends MvcHelper {
    
    public function create($model_name, $options=array()) {
        $defaults = array(
            'action' => $this->controller->action,
            'controller' => MvcInflector::tableize($model_name),
            'public' => false
        );
        $options = array_merge($defaults, $options);
        $this->model_name = $model_name;
        $this->object = MvcObjectRegistry::get_object($model_name);
        $this->model = MvcModelRegistry::get_model($model_name);
        $this->schema = $this->model->schema;
        $object_id = !empty($this->object) && !empty($this->object->__id) ? $this->object->__id : null;
        $router_options = array('controller' => $options['controller'], 'action' => $options['action']);
        if ($object_id) {
            $router_options['id'] = $object_id;
        }
        
        if ($options['public']) {
            $html = '<form action="'.MvcRouter::public_url($router_options).'" method="post">';
        } else {
            $html = '<form action="'.MvcRouter::admin_url($router_options).'" method="post">';
        }
        
        if ($object_id) {
            $html .= '<input type="hidden" id="'.$this->input_id('hidden_id').'" name="'.$this->input_name('id').'" value="'.$object_id.'" />';
        }
        return $html;
    }
    
    public function end($label='Submit') {
        $html = '<div><input type="submit" value="'.$this->esc_attr($label).'" /></div>';
        $html .= '</form>';
        return $html;
    }
    
    // Generalized method that chooses the appropriate input type based on the SQL type of the field
    public function input($field_name, $options=array()) {
        if (!empty($this->schema[$field_name])) {
            $schema = $this->schema[$field_name];
            $type = $this->get_type_from_sql_schema($schema);
            $defaults = array(
                'type' => $type,
                'label' => MvcInflector::titleize($schema['field']),
                'value' => empty($this->object->$field_name) ? '' : $this->object->$field_name
            );
            if ($type == 'checkbox') {
                unset($defaults['value']);
            }
            $options = array_merge($defaults, $options);
            $options['type'] = empty($options['type']) ? 'text' : $options['type'];
            $html = $this->{$options['type'].'_input'}($field_name, $options);
            return $html;
        } else {
            MvcError::fatal('Field "'.$field_name.'" not found for use in a form input.');
            return '';
        }
    }
    
    public function text_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'text'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = $this->before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= $this->after_input($field_name, $options);
        return $html;
    }
    
    public function textarea_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name)
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'textarea');
        $html = $this->before_input($field_name, $options);
        $html .= '<textarea'.$attributes_html.'>'.$options['value'].'</textarea>';
        $html .= $this->after_input($field_name, $options);
        return $html;
    }
    
    public function checkbox_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'checkbox',
            'checked' => !empty($this->object->$field_name) && $this->object->$field_name ? true : false,
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
        $html = $this->before_input($field_name, $options);
        if ($options['include_hidden_input']) {
            // Included to allow for a workaround to the issue of unchecked checkbox fields not being sent by clients
            $html .= '<input type="hidden" name="'.$this->esc_attr($options['name']).'" value="0" />';
        }
        $html .= '<input'.$attributes_html.' />';
        $html .= $this->after_input($field_name, $options);
        return $html;
    }
    
    public function hidden_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'hidden'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = '<input'.$attributes_html.' />';
        return $html;
    }
    
    public function password_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'password'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = $this->before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= $this->after_input($field_name, $options);
        return $html;
    }
    
    public function select($field_name, $options=array()) {
        $html = $this->before_input($field_name, $options);
        $html .= $this->select_tag($field_name, $options);
        $html .= $this->after_input($field_name, $options);
        return $html;
    }
    
    public function select_tag($field_name, $options=array()) {
        $defaults = array(
            'empty' => false,
            'value' => null
        );
        
        $options = array_merge($defaults, $options);
        $options['options'] = empty($options['options']) ? array() : $options['options'];
        $options['name'] = $field_name;
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
            $html .= '<option value="'.$this->esc_attr($key).'"'.$selected_attribute.'>'.$value.'</option>';
        }
        $html .= '</select>';
        return $html;
    }
    
    public function button($text, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($text),
            'type' => 'button',
            'class' => 'button'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = '<button'.$attributes_html.'>'.$text.'</button>';
        return $html;
    }
    
    public function belongs_to_dropdown($model_name, $select_options, $options=array()) {
    
        if (!empty($this->model->associations[$model_name])) {
            $foreign_key = $this->model->associations[$model_name]['foreign_key'];
        } else {
            $foreign_key = MvcInflector::underscore($model_name).'_id';
        }
        
        $value = empty($this->object->{$foreign_key}) ? '' : $this->object->{$foreign_key};
        
        $defaults = array(
            'id' => $this->model_name.'_'.$model_name.'_select',
            'name' => 'data['.$this->model_name.']['.$foreign_key.']',
            'label' => MvcInflector::titleize($model_name),
            'value' => $value,
            'options' => $select_options,
            'empty' => true
        );
        $options = array_merge($defaults, $options);
        $select_options = $options;
        $select_options['label'] = null;
        $select_options['before'] = null;
        $select_options['after'] = null;
        
        $field_name = $options['name'];
        
        $html = $this->before_input($field_name, $options);
        $html .= $this->select_tag($field_name, $select_options);
        $html .= $this->after_input($field_name, $options);
        
        return $html;
    }
    
    public function has_many_dropdown($model_name, $select_options, $options=array()) {
        $defaults = array(
            'select_id' => $this->model_name.'_'.$model_name.'_select',
            'select_name' => $this->model_name.'_'.$model_name.'_select',
            'list_id' => $this->model_name.'_'.$model_name.'_list',
            'ids_input_name' => 'data['.$this->model_name.']['.$model_name.'][ids]',
            'label' => MvcInflector::pluralize(MvcInflector::titleize($model_name)),
            'options' => $select_options
        );
        $options = array_merge($defaults, $options);
        
        $select_options = $options;
        $select_options['id'] = $select_options['select_id'];
        
        $html = $this->before_input($options['select_name'], $select_options);
        $html .= $this->select_tag($options['select_name'], $select_options);

        // Fetch all associated objects.
        // If there aren't any, return empty array
        $associated_objects = $this->object->{MvcInflector::tableize($model_name)};
        $associated_objects = empty($associated_objects) ? array() : $associated_objects;
        
        // An empty value is necessary to ensure that data with name $options['ids_input_name'] is submitted; otherwise,
        // if no association objects were selected the save() method wouldn't know that this association data is being
        // updated and that it should, as a result, delete existing association data.
        $html .= '<input type="hidden" name="'.$options['ids_input_name'].'[]" value="" />';
        
        $html .= '<ul id="'.$options['list_id'].'">';
        foreach ($associated_objects as $associated_object) {
            $html .= '
                <li>
                    '.$associated_object->__name.'
                    <a href="#" class="remove-item">Remove</a>
                    <input type="hidden" name="'.$options['ids_input_name'].'[]" value="'.$associated_object->__id.'" />
                </li>';
        }
        $html .= '</ul>';
        
        $html .= '
        
            <script type="text/javascript">
    
            jQuery(document).ready(function(){
            
                jQuery("#'.$options['select_id'].'").change(function() {
                    var option = jQuery(this).find("option:selected");
                    var id = option.attr("value");
                    if (id) {
                        var name = option.text();
                        var list_item = \'<li><input type="hidden" name="'.$options['ids_input_name'].'[]" value="\'+id+\'" />\'+name+\' <a href="#" class="remove-item">Remove</a></li>\';
                        jQuery("#'.$options['list_id'].'").append(list_item);
                        jQuery(this).val(\'\');
                    }
                    return false;
                });
                
                jQuery(".remove-item").live("click", function() {
                    jQuery(this).parents("li:first").remove();
                    return false;
                });
            
            });
            
            </script>
        
        ';
        $html .= $this->after_input($options['select_name'], $select_options);
        
        return $html;
        
    }
    
    private function before_input($field_name, $options) {
        $defaults = array(
            'before' => '<div>'
        );
        $options = array_merge($defaults, $options);
        $html = $options['before'];
        if (!empty($options['label'])) {
            $html .= '<label for="'.$options['id'].'">'.$options['label'].'</label>';
        }
        return $html;
    }
    
    private function after_input($field_name, $options) {
        $defaults = array(
            'after' => '</div>'
        );
        $options = array_merge($defaults, $options);
        $html = $options['after'];
        return $html;
    }
    
    private function input_id($field_name) {
        return $this->model_name.MvcInflector::camelize($field_name);
    }
    
    private function input_name($field_name) {
        return 'data['.$this->model_name.']['.MvcInflector::underscore($field_name).']';
    }
    
    private function get_type_from_sql_schema($schema) {
        switch($schema['type']) {
            case 'varchar':
                return 'text';
            case 'text':
                return 'textarea';
        
        }
        if ($schema['type'] == 'tinyint' && $schema['length'] == '1') {
            return 'checkbox';
        }
    }

}

?>