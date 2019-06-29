<?php

class MvcFormHelper extends MvcHelper {

	protected $is_in_admin_table = false;

    public function create($model_name, $options=array()) {
        $defaults = array(
            'action' => $this->controller->action,
            'controller' => MvcInflector::tableize($model_name),
            'public' => false,
            'enctype' => 'application/x-www-form-urlencoded',
            'is_admin' => false,
        );
        $options = array_merge($defaults, $options);
        $this->model_name = $model_name;
        $this->object = MvcObjectRegistry::get_object($model_name);
        $this->model = MvcModelRegistry::get_model($model_name);
        $this->schema = $this->model->schema;
        $this->is_admin = (bool) $options['is_admin'];
        $object_id = !empty($this->object) && !empty($this->object->__id) ? $this->object->__id : null;
        $router_options = array('controller' => $options['controller'], 'action' => $options['action']);
        if ($object_id) {
            $router_options['id'] = $object_id;
        }

        if ($options['public']) {
            $html = '<form enctype="'.$options['enctype'].'" action="'.MvcRouter::public_url($router_options).'" method="post">';
        } else {
            $html = '<form enctype="'.$options['enctype'].'" action="'.MvcRouter::admin_url($router_options).'" method="post">';
        }

        if ($object_id) {
            $html .= '<input type="hidden" id="'.$this->input_id('hidden_id').'" name="'.$this->input_name('id').'" value="'.$object_id.'" />';
        }
        return $html;
    }

    public function end($label='Submit') {
        $html = "";
        // Allows the omission of Submit button from end of form if $label == false. Useful for using custom submit buttons with more specific stylings etc..
        if ($label) {
			$class = ($this->is_admin) ? 'class="button button-primary"' : '';
            $html = '<div><input type="submit" value="'.$this->esc_attr($label).'"'.$class.' /></div>';
        }

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
                'label' => __(MvcInflector::titleize($schema['field']), $this->plugin_name),
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

    public function file_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'file'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = $this->before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= $this->after_input($field_name, $options);
        return $html;
    }
	
    public function url_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'url'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = $this->before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= $this->after_input($field_name, $options);
        return $html;
    }

    public function email_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'email'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = $this->before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= $this->after_input($field_name, $options);
        return $html;
    }

    public function time_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'time'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = $this->before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= $this->after_input($field_name, $options);
        return $html;
    }

    public function date_input($field_name, $options=array()) {
        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'date'
        );
        $options = array_merge($defaults, $options);
        $attributes_html = self::attributes_html($options, 'input');
        $html = $this->before_input($field_name, $options);
        $html .= '<input'.$attributes_html.' />';
        $html .= $this->after_input($field_name, $options);
        return $html;
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
        $textarea_content = $this->object ? $this->object->$field_name : '';
        $html = $this->before_input($field_name, $options);
        $html .= '<textarea'.$attributes_html.'>'.$textarea_content.'</textarea>';
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

    	$value = empty($this->object->{$field_name}) ? '' : $this->object->{$field_name};

        $defaults = array(
            'id' => $this->input_id($field_name),
            'name' => $this->input_name($field_name),
            'type' => 'hidden',
            'value' => $value
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

    public function editor($field_name, array $options = array()) {
        $id = $this->input_id($field_name);

        $defaults = array(
            'id' => $id,
            'name' => $this->input_name($field_name),
            'label' => __(MvcInflector::titleize($field_name), $this->plugin_name),
            'content' => empty($this->object->$field_name) ? '' : $this->object->$field_name,
            'rows' => 10,
            'editor_css' => '',
            'media_buttons' => true,
            'minimal' => false,
            'statusbar' => true,
            'quicktags' => true
        );
        $options = array_merge($defaults, $options);

        ob_start();

        if (!empty($options['label']))
            echo '<label for="' . $id . '">' . $options['label'] . '</label>';

        wp_editor($options['content'], $id, array(
            'editor_class' => $options['required'],
            'media_buttons' => $options['media_buttons'],
            'textarea_name' => $options['name'],
            'textarea_rows' => $options['rows'],
            'teeny' => $options['minimal'],
            'tinymce' => array(
                'statusbar' => $options['statusbar']
            ),
            'editor_css' => $options['editor_css'],
            'quicktags' => $options['quicktags'],
            'wpautop' => false
        ));

        return ob_get_clean();
    }

    public function select($field_name, $options=array()) {
        $html = $this->before_input($field_name, $options);
        $html .= $this->select_tag($field_name, $options);
        $html .= $this->after_input($field_name, $options);
        return $html;
    }

    function select_from_model($field_name, MvcModel $model, $find_options = array(), $select_options = array()) {

    	$default_find_options = array(
    			'selects' => array($model->primary_key, $model->display_field),
    			'order' => $model->display_field
    	);

    	$find_options = array_merge($default_find_options, $find_options);

    	$values =  $model->find($find_options);

    	$key = $value->__id;
    	$value = $value->__name;

    	$default_options = array(
    			'id' => $this->model_name.'_'.$field_name.'_select',
    			'name' => 'data['.$this->model_name.']['.$field_name.']',
    			'label' => __(MvcInflector::titleize($field_name), $this->plugin_name),
    			'empty' => true,
    			'value' => empty($this->object->$field_name) ? '' : $this->object->$field_name,
    			'options' => $values
    	);

    	$select_options = array_merge($default_options, $select_options);

    	return $this->select($default_options['name'], $select_options);
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
            if (is_array($options['value'])) {
                $selected_attribute = in_array($key, $options['value']) ? ' selected="selected"' : '';
            } else {
                $selected_attribute = $options['value'] == $key ? ' selected="selected"' : '';
            }

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
            'label' => __(MvcInflector::titleize($model_name), $this->plugin_name),
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

    public function has_many_dropdown($model_name, $select_options, $options=array(), $associated_objects=false) {
        $defaults = array(
            'select_id' => $this->model_name.'_'.$model_name.'_select',
            'select_name' => $this->model_name.'_'.$model_name.'_select',
            'list_id' => $this->model_name.'_'.$model_name.'_list',
            'ids_input_name' => 'data['.$this->model_name.']['.$model_name.'][ids]',
            'label' => __(MvcInflector::pluralize(MvcInflector::titleize($model_name)), $this->plugin_name),
            'options' => $select_options
        );
        $options = array_merge($defaults, $options);

        $select_options = $options;
        $select_options['id'] = $select_options['select_id'];

        $html = $this->before_input($options['select_name'], $select_options);
        $html .= $this->select_tag($options['select_name'], $select_options);

        // Fetch all associated objects.
        // If there aren't any, return empty array
        if($associated_objects === false){
            $associated_objects = isset($this->object->{MvcInflector::tableize($model_name)}) ? $this->object->{MvcInflector::tableize($model_name)} : '';
        }
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

                jQuery(document).on("click", ".remove-item", function() {
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
            'before' => ($this->is_in_admin_table) ? '<tr>' : '<div>',
        );
        $options = array_merge($defaults, $options);
        $html = $options['before'];
        if (!empty($options['label'])) {
            $label = '<label for="'.$options['id'].'">'.$options['label'].'</label>';
			$html .= ($this->is_in_admin_table) ? "<th scope='row'>{$label}</th><td>" : $label;
        } else {
			$html .= '<td colspan="2">';
		}
        return $html;
    }

    private function after_input($field_name, $options) {
        $defaults = array(
            'after' => ($this->is_in_admin_table) ? '</td></tr>' : '</div>',
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

	public function open_admin_table() {
		$this->is_in_admin_table = true;
		return '<table class="form-table"><tbody>';
	}

	public function close_admin_table() {
		$this->is_in_admin_table = false;
		return '</tbody></table>';
	}

}

?>
