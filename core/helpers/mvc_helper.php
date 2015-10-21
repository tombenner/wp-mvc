<?php

class MvcHelper {

    protected $file_includer = null;
    
    function __construct() {
        $this->file_includer = new MvcFileIncluder();
        $this->init();
    }
    
    public function init() {
    }
    
    public function render_view($path, $view_vars=array()) {
        extract($view_vars);
        $filepath = $this->file_includer->find_first_app_file_or_core_file('views/'.$path.'.php');
        if (!$filepath) {
            $path = preg_replace('/admin\/(?!layouts)([\w_]+)/', 'admin', $path);
            $filepath = $this->file_includer->find_first_app_file_or_core_file('views/'.$path.'.php');
            if (!$filepath) {
                MvcError::warning('View "'.$path.'" not found.');
            }
        }
        require $filepath;
    }
    
    public function esc_attr($string) {
        return esc_attr($string);
    }
    
    static function attributes_html($attributes, $valid_attributes_array_or_tag) {
    
        $event_attributes = array(
            'standard' => array(
                'onclick',
                'ondblclick',
                'onkeydown',
                'onkeypress',
                'onkeyup',
                'onmousedown',
                'onmousemove',
                'onmouseout',
                'onmouseover',
                'onmouseup'
            ),
            'form' => array(
                'onblur',
                'onchange',
                'onfocus',
                'onreset',
                'onselect',
                'onsubmit'
            )
        );
    
        // To do: add on* event attributes
        $valid_attributes_by_tag = array(
            'a' => array(
                'accesskey',
                'charset',
                'class',
                'dir',
                'coords',
                'href',
                'hreflang',
                'id',
                'lang',
                'name',
                'rel',
                'rev',
                'shape',
                'style',
                'tabindex',
                'target',
                'title',
                'xml:lang'
            ),
            'input' => array(
                'accept',
                'access_key',
                'align',
                'alt',
                'autocomplete',
                'checked',
                'class',
                'dir',
                'disabled',
                'id',
                'lang',
                'maxlength',
                'name',
                'placeholder',
                'readonly',
                'required',
                'size',
                'src',
                'style',
                'tabindex',
                'title',
                'type',
                'value',
                'xml:lang',
                $event_attributes['form']
            ),
            'textarea' => array(
                'access_key',
                'class',
                'cols',
                'dir',
                'disabled',
                'id',
                'lang',
                'maxlength',
                'name',
                'readonly',
                'rows',
                'style',
                'tabindex',
                'title',
                'xml:lang',
                $event_attributes['form']
            ),
            'select' => array(
                'class',
                'dir',
                'disabled',
                'id',
                'lang',
                'multiple',
                'name',
                'size',
                'style',
                'tabindex',
                'title',
                'xml:lang',
                $event_attributes['form']
            )
        );
        
        foreach ($valid_attributes_by_tag as $key => $valid_attributes) {
            $valid_attributes = array_merge($event_attributes['standard'], $valid_attributes);
            $valid_attributes = self::array_flatten($valid_attributes);
            $valid_attributes_by_tag[$key] = $valid_attributes;
        }
        
        $valid_attributes = is_array($valid_attributes_array_or_tag) ? $valid_attributes_array_or_tag : $valid_attributes_by_tag[$valid_attributes_array_or_tag];
        
        $attributes = array_intersect_key($attributes, array_flip($valid_attributes));
        
        $attributes_html = '';
        foreach ($attributes as $key => $value) {
            $attributes_html .= ' '.$key.'="'.esc_attr($value).'"';
        }
        return $attributes_html;
    
    }
    
    // Move these into an AdminHelper
    
    public function admin_header_cells($controller) {
        $html = '';
        foreach ($controller->default_columns as $key => $column) {
            $html .= $this->admin_header_cell($column['label']);
        }
        $html .= $this->admin_header_cell('');
        return '<tr>'.$html.'</tr>';
        
    }
    
    public function admin_header_cell($label) {
        return '<th scope="col" class="manage-column">'.$label.'</th>';
    }
    
    public function admin_table_cells($controller, $objects) {
        $html = '';
        foreach ($objects as $object) {
            $html .= '<tr>';
            foreach ($controller->default_columns as $key => $column) {
                $html .= $this->admin_table_cell($controller, $object, $column);
            }
            $html .= $this->admin_actions_cell($controller, $object);
            $html .= '</tr>';
        }
        return $html;
    }
    
    public function admin_table_cell($controller, $object, $column) {
        if (!empty($column['value_method'])) {
            $value = $controller->{$column['value_method']}($object);
        } else {
            $value = $object->$column['key'];
        }
        return '<td>'.$value.'</td>';
    }
    
    public function admin_actions_cell($controller, $object) {
        $links = array();
        $object_name = empty($object->__name) ? 'Item #'.$object->__id : $object->__name;
        $encoded_object_name = $this->esc_attr($object_name);
        $links[] = '<a href="'.MvcRouter::admin_url(array('object' => $object, 'action' => 'edit')).'" title="Edit '.$encoded_object_name.'">Edit</a>';
        $links[] = '<a href="'.MvcRouter::public_url(array('object' => $object)).'" title="View '.$encoded_object_name.'">View</a>';
        $links[] = '<a href="'.MvcRouter::admin_url(array('object' => $object, 'action' => 'delete')).'" title="Delete '.$encoded_object_name.'" onclick="return confirm(&#039;Are you sure you want to delete '.$encoded_object_name.'?&#039;);">Delete</a>';
        $html = implode(' | ', $links);
        return '<td>'.$html.'</td>';
    }
    
    // To do: move this into an MvcUtilities class (?)
    
    static function array_flatten($array) {

        foreach ($array as $key => $value){
            $array[$key] = (array)$value;
        }
        
        return call_user_func_array('array_merge', $array);
    
    }

}

?>
