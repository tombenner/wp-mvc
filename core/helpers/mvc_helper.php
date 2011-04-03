<?php

class MvcHelper {
	
	public function render_view($path) {
	
		require_once MVC_PLUGIN_PATH.'app/views/'.$path.'.php';
	
	}
	
	public function esc_attr($string) {
		return esc_attr($string);
	}
	
	public function attributes_html($attributes, $valid_attributes_array_or_tag) {
	
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
				'alt',
				'checked',
				'class',
				'dir',
				'disabled',
				'id',
				'lang',
				'maxlength',
				'name',
				'readonly',
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
		
		foreach($valid_attributes_by_tag as $key => $valid_attributes) {
			$valid_attributes = array_merge($event_attributes['standard'], $valid_attributes);
			$valid_attributes = self::array_flatten($valid_attributes);
			$valid_attributes_by_tag[$key] = $valid_attributes;
		}
		
		$valid_attributes = is_array($valid_attributes_array_or_tag) ? $valid_attributes_array_or_tag : $valid_attributes_by_tag[$valid_attributes_array_or_tag];
		
		$attributes = array_intersect_key($attributes, array_flip($valid_attributes));
		
		$attributes_html = '';
		foreach($attributes as $key => $value) {
			$attributes_html .= ' '.$key.'="'.esc_attr($value).'"';
		}
		return $attributes_html;
	
	}
	
	// Move these into an AdminHelper
	
	public function admin_header_cells($model) {
		$html = '';
		foreach($model->admin_columns as $key => $column) {
			$html .= $this->admin_header_cell($column['label']);
		}
		$html .= $this->admin_header_cell('');
		return '<tr>'.$html.'</tr>';
		
	}
	
	public function admin_header_cell($label) {
		return '<th scope="col" class="manage-column">'.$label.'</th>';
	}
	
	public function admin_table_cells($model, $objects) {
		$html = '';
		foreach($objects as $object) {
			$html .= '<tr>';
			foreach($model->admin_columns as $key => $column) {
				$html .= $this->admin_table_cell($model, $object, $column);
			}
			$html .= $this->admin_actions_cell($model, $object);
			$html .= '</tr>';
		}
		return $html;
	}
	
	public function admin_table_cell($model, $object, $column) {
		if (!empty($column['value_method'])) {
			$value = $model->{$column['value_method']}($object);
		} else {
			$value = $object->$column['key'];
		}
		return '<td>'.$value.'</td>';
	}
	
	public function admin_actions_cell($model, $object) {
		$links = array();
		$object_name = empty($object->__name) ? 'Item #'.$object->__id : $object->__name;
		$encoded_object_name = $this->esc_attr($object_name);
		$controller = Inflector::tableize($model->name);
		$links[] = '<a href="'.Router::admin_url(array('controller' => $controller, 'action' => 'edit', 'id' => $object->__id)).'" title="Edit '.$encoded_object_name.'">Edit</a>';
		$links[] = '<a href="'.Router::public_url(array('controller' => $controller, 'action' => 'show', 'id' => $object->__id)).'" title="View '.$encoded_object_name.'">View</a>';
		$links[] = '<a href="'.Router::admin_url(array('controller' => $controller, 'action' => 'delete', 'id' => $object->__id)).'" title="Delete '.$encoded_object_name.'" onclick="return confirm(&#039;Are you sure you want to delete '.$encoded_object_name.'?&#039;);">Delete</a>';
		$html = implode(' | ', $links);
		return '<td>'.$html.'</td>';
	}
	
	// To do: move this into an MvcUtilities class (?)
	
	private function array_flatten($array) {

		foreach($array as $key => $value){
			$array[$key] = (array)$value;
		}
		
		return call_user_func_array('array_merge', $array);
	
	}

}

?>