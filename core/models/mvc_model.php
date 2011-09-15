<?php

class MvcModel {

	public $table = null;
	public $primary_key = 'id';
	public $belongs_to = null;
	public $has_many = null;
	public $has_and_belongs_to_many = null;
	public $associations = null;
	public $admin_pages = null;
	public $validation_error = null;
	public $validation_error_html = null;
	private $data_validator = null;
	private $db_adapter = null;
	
	function __construct() {
		
		global $wpdb;
		
		$this->name = preg_replace('/Model$/', '', get_class($this));
		
		$table = empty($this->table) ? $wpdb->prefix.MvcInflector::tableize($this->name) : $this->process_table_name($this->table);
		
		$defaults = array(
			'model_name' => $this->name,
			'table' => $table,
			'table_reference' => empty($this->database) ? '`'.$table.'`' : '`'.$this->database.'`.`'.$table.'`',
			'selects' => empty($this->selects) ? array('`'.$this->name.'`.*') : $this->default_selects,
			'order' => empty($this->order) ? null : $this->order,
			'joins' => empty($this->joins) ? null : $this->joins,
			'conditions' => empty($this->conditions) ? null : $this->conditions,
			'limit' => empty($this->limit) ? null : $this->limit,
			'includes' => empty($this->includes) ? null : $this->includes,
			'per_page' => empty($this->per_page) ? 10 : $this->per_page,
			'validate' => empty($this->validate) ? null : $this->validate
		);
		
		foreach($defaults as $key => $value) {
			$this->{$key} = $value;
		}
		
		$this->db_adapter = new MvcDatabaseAdapter();
		$this->db_adapter->set_defaults($defaults);
		
		$this->data_validator = new MvcDataValidator();
		
		$this->init_admin_pages();
		$this->init_admin_columns();
		$this->init_associations();
		$this->init_schema();
	
	}
	
	public function new_object($data) {
		$object = false;
		foreach($data as $field => $value) {
			$object->{$field} = $value;
		}
		$object = $this->process_objects($object);
		return $object;
	}
	
	public function create($data) {
		if (empty($data[$this->name])) {
			return false;
		}
		$model_data = $data[$this->name];
		if (method_exists($this, 'before_save')) {
			if (!$this->before_save($model_data)) {
				return false;
			}
		}
		$id = $this->insert($model_data);
		$this->update_associations($id, $model_data);
		if (method_exists($this, 'after_save')) {
			$object = $this->find_by_id($id);
			$this->after_save($object);
		}
		return $id;
	}
	
	public function save($data) {
		if (empty($data[$this->name])) {
			return false;
		}
		if (!empty($data[$this->name]['id'])) {
			$model_data = $data[$this->name];
			$id = $model_data['id'];
			unset($model_data['id']);
			$valid = $this->validate_data($model_data);
			if ($valid !== true) {
				$this->validation_error = $valid;
				$this->validation_error_html = $this->validation_error->get_html();
				$this->invalid_data = $model_data;
				return false;
			}
			if (method_exists($this, 'before_save')) {
				if (!$this->before_save($model_data)) {
					return false;
				}
			}
			$this->update($id, $model_data);
			$this->update_associations($id, $model_data);
		} else {
			$id = $this->create($data);
		}
		if (method_exists($this, 'after_save')) {
			$object = $this->find_by_id($id);
			$this->after_save($object);
		}
		return true;
	}
	
	public function insert($data) {
		$insert_id = $this->db_adapter->insert($data);
		$this->insert_id = $insert_id;
		return $insert_id;
	}
	
	public function update($id, $data) {
		$options = array(
			'conditions' => array($this->name.'.'.$this->primary_key => $id)
		);
		$this->db_adapter->update_all($data, $options);
	}
	
	public function update_all($data, $options=array()) {
		$this->db_adapter->update_all($data, $options);
	}
	
	public function delete($id) {
		$options = array(
			'conditions' => array($this->primary_key => $id)
		);
		$this->db_adapter->delete_all($options);
	}
	
	public function delete_all($options=array()) {
		$this->db_adapter->delete_all($options);
	}
	
	public function find($options=array()) {
		$options = $this->process_find_options($options);
		$objects = $this->db_adapter->get_results($options);
		$objects = $this->process_objects($objects, $options);
		return $objects;
	}
	
	public function find_by_id($id, $options=array()) {
		$options = $this->process_find_options($options);
		$options['conditions'] = array($this->name.'.'.$this->primary_key => $id);
		$object = $this->db_adapter->get_results($options);
		$object = isset($object[0]) ? $object[0] : null;
		$object = $this->process_objects($object, $options);
		return $object;
	}
	
	public function paginate($options=array()) {
		$options = $this->process_find_options($options);
		$options['page'] = empty($options['page']) ? 1 : intval($options['page']);
		$options['per_page'] = empty($options['per_page']) ? $this->per_page : intval($options['per_page']);
		$objects = $this->db_adapter->get_results($options);
		$objects = $this->process_objects($objects, $options);
		$total_count = $this->get_total_count($options);
		$response = array(
			'objects' => $objects,
			'total_pages' => ceil($total_count/$options['per_page']),
			'page' => $options['page']
		);
		return $response;
	
	}
	
	public function get_keyword_conditions($fields, $keywords) {
		$conditions = array();
		if (is_string($keywords)) {
			$keywords = preg_split('/[\s]+/', $keywords);
		}
		$formatted_fields = array();
		foreach($fields as $field) {
			if (strpos($field, '.') === false) {
				$field = $this->name.'.'.$field;
			}
			$formatted_fields[] = $field;
		}
		if (count($formatted_fields) == 1) {
			$field_reference = $formatted_fields[0];
		} else {
			$field_reference = 'CONCAT('.implode(', ', $formatted_fields).')';
		}
		foreach($keywords as $keyword) {
			$conditions[] = array($field_reference.' LIKE' => '%'.$keyword.'%');
		}
		return $conditions;
	}
	
	protected function get_total_count($options=array()) {
		$clauses = $this->db_adapter->get_sql_select_clauses($options);
		$clauses['select'] = 'SELECT COUNT(*) AS count';
		unset($clauses['limit']);
		$sql = implode(' ', $clauses);
		$result = $this->db_adapter->get_var($sql);
		return $result;
	}
	
	private function process_find_options($options) {
		if (!empty($options['joins'])) {
			if (is_string($options['joins'])) {
				$options['joins'] = array($options['joins']);
			}
			foreach($options['joins'] as $key => $join) {
				if (is_string($join)) {
					$join_model_name = $join;
					if (!empty($this->associations[$join_model_name])) {
						$association = $this->associations[$join_model_name];
						
						$join_model = new $join_model_name();
						
						switch ($association['type']) {
							case 'belongs_to':
								$join = array(
									'table' => $join_model->table,
									'on' => $join_model_name.'.'.$join_model->primary_key.' = '.$this->name.'.'.$association['foreign_key'],
									'alias' => $join_model_name
								);
								break;
								
							case 'has_many':
								// To do: test this
								$join = array(
									'table' => $this->table,
									'on' => $join_model_name.'.'.$association['foreign_key'].' = '.$this->name.'.'.$this->primary_key,
									'alias' => $join_model_name
								);
								break;
							
							case 'has_and_belongs_to_many':
								$join_table_alias = $join_model_name.$this->name;
								// The join for the HABTM join table
								$join = array(
									'table' => $this->process_table_name($association['join_table']),
									'on' => $join_table_alias.'.'.$association['foreign_key'].' = '.$this->name.'.'.$this->primary_key,
									'alias' => $join_table_alias
								);
								// The join for the association model's table
								$second_join = array(
									'table' => $join_model->table,
									'on' => $join_table_alias.'.'.$association['association_foreign_key'].' = '.$join_model_name.'.'.$join_model->primary_key,
									'alias' => $join_model_name
								);
								$options['joins'][] = $second_join;
								break;
						}
					}
					$options['joins'][$key] = $join;
				}
			}
		}
		return $options;
	}
	
	private function update_associations($id, $model_data) {
		if (!empty($this->associations)) {
			foreach($this->associations as $association) {
				switch($association['type']) {
					case 'has_many':
						$this->update_has_many_associations($id, $association, $model_data);
						break;
					case 'has_and_belongs_to_many':
						$this->update_has_and_belongs_to_many_associations($id, $association, $model_data);
						break;
				}
			}
		}
	}
	
	private function update_has_many_associations($object_id, $association, $model_data) {
		$association_name = $association['name'];
		if (!empty($model_data[$association_name])) {
			if (isset($model_data[$association_name]['ids'])) {
				if (!empty($model_data[$association_name]['ids'])) {
					// To do: Implement this by first emptying the foreign key values of associated records
					// that currently equal $object_id, then loop through 'ids', setting those foreign key
					// values.
				}
			}
		}
	}
	
	private function update_has_and_belongs_to_many_associations($object_id, $association, $model_data) {
		$association_name = $association['name'];
		if (!empty($model_data[$association_name])) {
			if (isset($model_data[$association_name]['ids'])) {
				$this->db_adapter->delete_all(array(
					'table_reference' => $this->process_table_name($association['join_table']),
					'conditions' => array($association['foreign_key'] => $object_id)
				));
				if (!empty($model_data[$association_name]['ids'])) {
					foreach($model_data[$association_name]['ids'] as $association_id) {
						if (!empty($association_id)) {
							$this->db_adapter->insert(
								array(
									$association['foreign_key'] => $object_id,
									$association['association_foreign_key'] => $association_id,
								),
								array(
									'table_reference' => $this->process_table_name($association['join_table'])
								)
							);
						}
					}
				}
			}
		}
	}
	
	private function validate_data($data) {
		$rules = $this->validate;
		if (!empty($rules)) {
			foreach($rules as $field => $rule) {
				if (isset($data[$field])) {
					$valid = $this->data_validator->validate($field, $data[$field], $rule);
					if ($valid !== true) {
						return $valid;
					}
				}
			}
		}
		return true;
	}
	
	private function init_admin_pages() {
		$titleized = MvcInflector::titleize($this->name);
		$default_pages = array(
			'add' => array(
				'label' => 'Add New'
			),
			'delete' => array(
				'label' => 'Delete '.$titleized,
				'in_menu' => false
			),
			'edit' => array(
				'label' => 'Edit '.$titleized,
				'in_menu' => false
			)
		);
		if (!isset($this->admin_pages)) {
			$this->admin_pages = $default_pages;
		}
		$admin_pages = array();
		foreach($this->admin_pages as $key => $value) {
			if (is_int($key)) {
				$key = $value;
				$value = array();
			}
			$defaults = array(
				'action' => $key,
				'in_menu' => true,
				'label' => MvcInflector::titleize($key),
				'capability' => 'administrator'
			);
			if (isset($default_pages[$key])) {
				$value = array_merge($default_pages[$key], $value);
			}
			$value = array_merge($defaults, $value);
			$admin_pages[$key] = $value;
		}
		$this->admin_pages = $admin_pages;
	}
	
	private function init_admin_columns() {
		$admin_columns = array();
		foreach($this->admin_columns as $key => $value) {
			if (is_array($value)) {
				if (!isset($value['label'])) {
					$value['label'] = MvcInflector::titleize($key);
				}
			} else if (is_integer($key)) {
				$key = $value;
				if ($value == 'id') {
					$value = array('label' => 'ID');
				} else {
					$value = array('label' => MvcInflector::titleize($value));
				}
			} else {
				$value = array('label' => $value);
			}
			$value['key'] = $key;
			$admin_columns[$key] = $value;
		}
		$this->admin_columns = $admin_columns;
	}
	
	private function process_objects($objects, $options=array()) {
		if (!is_array($objects) && !is_object($objects)) {
			return null;
		}
		$single_object = false;
		if (is_object($objects)) {
			$objects = array($objects);
			$single_object = true;
		}
		
		$includes = array_key_exists('includes', $options) ? $options['includes'] : $this->includes;
		
		if (is_string($includes)) {
			$includes = array($includes);
		}
		
		if (!empty($includes)) {
			// Instantiate associated models, so that they don't need to be instantiated multiple times in the subsequent for loop
			$models = array();
			foreach($includes as $key => $include) {
				$model_name = is_string($include) ? $include : $key;
				$models[$model_name] = new $model_name();
			}
		}
		
		$recursive = isset($options['recursive']) ? $options['recursive'] - 1 : 2;
		
		foreach($objects as $key => $object) {
		
			if (!empty($this->primary_key)) {
				$object->__id = $object->{$this->primary_key};
			}
			
			if (!empty($includes) && $recursive != 0) {
				foreach($includes as $include_key => $include) {
					if (is_string($include)) {
						$model_name = $include;
						$association = $this->associations[$model_name];
					} else {
						$model_name = $include_key;
						$association = $include;
						if (!empty($this->associations[$model_name])) {
							if (!empty($association['selects'])) {
								if (is_string($association['selects'])) {
									$association['selects'] = array($association['selects']);
								}
								$association['fields'] = $association['selects'];
							}
							$association = array_merge($this->associations[$model_name], $association);
						}
					}
					if (empty($association['fields'])) {
						$association['fields'] = array($association['name'].'.*');
					}
					$model = $models[$model_name];
					switch ($association['type']) {
						case 'belongs_to':
							$associated_object = $model->find_by_id($object->{$association['foreign_key']}, array(
								'recursive' => $recursive
							));
							$object->{MvcInflector::underscore($model_name)} = $associated_object;
							break;
							
						case 'has_many':
							$associated_objects = $model->find(array(
								'selects' => $association['fields'],
								'conditions' => array($association['foreign_key'] => $object->__id),
								'recursive' => $recursive
							));
							$object->{MvcInflector::tableize($model_name)} = $associated_objects;
							break;
						
						case 'has_and_belongs_to_many':
							$join_alias = 'JoinTable';
							$associated_objects = $model->find(array(
								'selects' => $association['fields'],
								'joins' => array(
									'table' => $this->process_table_name($association['join_table']),
									'on' => $join_alias.'.'.$association['association_foreign_key'].' = '.$model_name.'.'.$model->primary_key,
									'alias' => $join_alias
								),
								'conditions' => array($join_alias.'.'.$association['foreign_key'] => $object->__id),
								'recursive' => $recursive
							));
							$object->{MvcInflector::tableize($model_name)} = $associated_objects;
							break;
					}
				}
			}
			
			if (method_exists($this, 'after_find')) {
				$this->after_find($object);
			}
			
			// Set this after after_find, in case after_find sets this field
			if (!empty($this->display_field)) {
				$object->__name = empty($object->{$this->display_field}) ? null : $object->{$this->display_field};
			}
			
			$objects[$key] = $object;
		}
		if ($single_object) {
			return $objects[0];
		}
		return $objects;
	}
	
	protected function process_table_name($table_name) {
		global $wpdb;
		$table_name = str_replace('{prefix}', $wpdb->prefix, $table_name);
		return $table_name;
	}
	
	protected function init_schema() {
		$sql = '
			DESCRIBE
				'.$this->table;
		$results = $this->db_adapter->get_results($sql);
		
		$schema = array();
		
		foreach($results as $result) {
			$field = $result->Field;
			$type_schema = explode('(', $result->Type);
			$type = $type_schema[0];
			$length = isset($type_schema[1]) ? rtrim($type_schema[1], ')') : null; 
			$column = array();
			$column['field'] = $field;
			$column['key'] = $result->Key ? $result->Key : null;
			$column['type'] = $type;
			$column['length'] = $length;
			$column['null'] = $result->Null == 'YES' ? true : false;
			$column['default'] = $result->Default;
			$schema[$field] = $column;
		}
		
		$this->schema = $schema;
		$this->db_adapter->schema = $schema;
	}
	
	protected function init_associations() {
		if (!empty($this->belongs_to)) {
			foreach($this->belongs_to as $key => $value) {
				$config = null;
				if (is_string($value)) {
					$association = $value;
					$config = array(
						'type' => 'belongs_to',
						'name' => $association,
						'class' => $association,
						'foreign_key' => MvcInflector::underscore($association).'_id',
						'includes' => null
					);
				} else if (is_string($key) && is_array($value)) {
					$association = $key;
					$config = array(
						'type' => 'belongs_to',
						'name' => empty($value['name']) ? $association : $value['name'],
						'class' => empty($value['class']) ? $association : $value['class'],
						'foreign_key' => empty($value['foreign_key']) ? MvcInflector::underscore($association).'_id' : $value['foreign_key'],
						'includes' => null
					);
				}
				if (!empty($config)) {
					if (!is_array($this->associations)) {
						$this->associations = array();
					}
					$this->associations[$association] = $config;
				}
			}
		}
		if (!empty($this->has_many)) {
			foreach($this->has_many as $association) {
				if (is_string($association)) {
					if (!is_array($this->associations)) {
						$this->associations = array();
					}
					$config = array(
						'type' => 'has_many',
						'name' => $association,
						'class' => $association,
						'foreign_key' => MvcInflector::underscore($this->name).'_id',
						'includes' => null
					);
					$this->associations[$association] = $config;
				}
			}
		}
		if (!empty($this->has_and_belongs_to_many)) {
			foreach($this->has_and_belongs_to_many as $association_name => $association) {
				if (!is_array($this->associations)) {
					$this->associations = array();
				}
				if (isset($association['fields'])) {
					foreach($association['fields'] as $key => $field) {
						$association['fields'][$key] = $association_name.'.'.$field;
					}
				}
				$config = array(
					'type' => 'has_and_belongs_to_many',
					'name' => $association_name,
					'class' => $association_name,
					'foreign_key' => isset($association['foreign_key']) ? $association['foreign_key'] : MvcInflector::underscore($this->name).'_id',
					'association_foreign_key' => isset($association['association_foreign_key']) ? $association['association_foreign_key'] : MvcInflector::underscore($association_name).'_id',
					'join_table' => $this->process_table_name($association['join_table']),
					'fields' => isset($association['fields']) ? $association['fields'] : null,
					'includes' => isset($association['includes']) ? $association['includes'] : null
				);
				$this->associations[$association_name] = $config;
			}
		}
	}

}

?>