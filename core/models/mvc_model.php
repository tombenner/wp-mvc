<?php

class MvcModel {

    public $name = null;
    public $table = null;
    public $database = null;
    public $primary_key = 'id';
    public $belongs_to = null;
    public $has_many = null;
    public $has_and_belongs_to_many = null;
    public $associations = null;
    public $properties = null;
    public $validation_error = null;
    public $validation_error_html = null;
    public $validation_mode = 'single';
    public $schema = null;
    public $wp_post = null;
    private $data_validator = null;
    protected $db_adapter = null;
    private $wp_post_adapter = null;
    protected static $describe_cache = array();

    function __construct() {
        
        global $wpdb;
        
        $this->name = preg_replace('/Model$/', '', get_class($this));
        
        $this->check_for_obsolete_functionality();
        
        $table = empty($this->table) ? $wpdb->prefix.MvcInflector::tableize($this->name) : self::process_table_name($this->table);
        
        $defaults = array(
            'model_name' => $this->name,
            'table' => $table,
            'table_reference' => empty($this->database) ? '`'.$table.'`' : '`'.$this->database.'`.`'.$table.'`',
            'selects' => empty($this->selects) ? array('`'.$this->name.'`.*') : $this->selects,
            'order' => empty($this->order) ? null : $this->order,
            'joins' => empty($this->joins) ? null : $this->joins,
            'conditions' => empty($this->conditions) ? null : $this->conditions,
            'limit' => empty($this->limit) ? null : $this->limit,
            'includes' => empty($this->includes) ? null : $this->includes,
            'group' => empty($this->group) ? null : $this->group,
            'per_page' => empty($this->per_page) ? 10 : $this->per_page,
            'validate' => empty($this->validate) ? null : $this->validate
        );
        
        foreach ($defaults as $key => $value) {
            $this->{$key} = $value;
        }
        
        $this->db_adapter = new MvcDatabaseAdapter();
        $this->db_adapter->set_defaults($defaults);
        
        $this->data_validator = new MvcDataValidator();
        
        $this->init_schema();
        
        if ($this->wp_post) {
            $this->wp_post_adapter = new MvcPostAdapter();
            $this->wp_post_adapter->verify_settings($this);
            if (empty($this->belongs_to)) {
                $this->belongs_to = array();
            }
            $association = array(
                'Post' => array(
                    'class' => 'MvcPost'
                )
            );
            $this->belongs_to = array_merge($association, $this->belongs_to);
        }
        
        $this->init_associations();
        $this->init_properties();
    
    }
    
    public function new_object($data) {
        $object = new MvcModelObject($this);
        foreach ($data as $field => $value) {
            $object->$field = $value;
        }
        $object = $this->process_objects($object);
        return $object;
    }
    
    public function create($data) {
        $data = $this->object_to_array($data);
        if (empty($data[$this->name])) {
            $data = array($this->name => $data);
        }
        $model_data = $data[$this->name];
        if (method_exists($this, 'before_save')) {
            if (!$this->before_save($model_data)) {
                return false;
            }
        }
        $valid = $this->validate_data($model_data);
        if ($valid !== true) {
            return false;
        }
        $id = $this->insert($model_data);
        $this->update_associations($id, $model_data);
        if (method_exists($this, 'after_create') || method_exists($this, 'after_save')) {
            $object = $this->find_by_id($id);
            if (method_exists($this, 'after_create')) {
                $this->after_create($object);
            }
            if (method_exists($this, 'after_save')) {
                $this->after_save($object);
            }
        }
        return $id;
    }
    
    public function save($data) {
        $data = $this->object_to_array($data);
        if (empty($data[$this->name])) {
            $data = array($this->name => $data);
        }
        if (!empty($data[$this->name][$this->primary_key])) {
            $model_data = $data[$this->name];
            $id = $model_data[$this->primary_key];
            unset($model_data[$this->primary_key]);
            $valid = $this->validate_data($model_data);
            if ($valid !== true) {
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
            if (!$id = $this->create($data)) {
                return false;
            }
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
        if ($this->has_post()) {
            $data[$this->primary_key] = $insert_id;
            $this->save_post($data);
        }
        return $insert_id;
    }
    
    public function update($id, $data, $update_options=array()) {
        $options = array(
            'conditions' => array($this->name.'.'.$this->primary_key => $id)
        );
        $this->db_adapter->update_all($data, $options, $update_options);
        if ($this->has_post() && !(isset($update_options['bypass_save_post']) && $update_options['bypass_save_post'])) {
            $object = $this->find_by_id($id);
            $this->save_post($object);
        }
    }
    
    public function update_all($data, $options=array(), $update_options=array()) {
        $this->db_adapter->update_all($data, $options);
        if ($this->has_post() && !(isset($update_options['bypass_save_post']) && $update_options['bypass_save_post'])) {
            $objects = $this->find($options);
            foreach ($objects as $object) {
                $this->save_post($object);
            }
        }
    }
    
    public function delete($id) {
        $options = array(
            'conditions' => array($this->primary_key => $id)
        );
        $this->delete_all($options);
    }
    
    public function delete_all($options=array()) {
        $has_post = $this->has_post();
        $before_delete_method_exists = method_exists($this, 'before_delete');
        $objects = null;
        if ($has_post || $before_delete_method_exists) {
            $objects = $this->find($options);
            foreach ($objects as $object) {
                if ($has_post) {
                    wp_delete_post($object->post_id, true);
                }
                if ($before_delete_method_exists) {
                    $this->before_delete($object);
                }
            }
        }
        $this->delete_dependent_associations($objects, $options);
        $this->db_adapter->delete_all($options);
    }
    
    protected function delete_dependent_associations($objects, $options=array()) {
        if (empty($objects)) {
            $objects = $this->find($options);
        }
        if (!empty($objects)) {
            foreach ($this->associations as $association) {
                if ($association['dependent']) {
                    if ($association['type'] == 'has_many') {
                        $model = MvcModelRegistry::get_model($association['class']);
                        foreach ($objects as $object) {
                            $options = array(
                                'conditions' => array($association['foreign_key'] => $object->{$this->primary_key})
                            );
                            $model->delete_all($options);
                        }
                    }
                }
            }
        }
    }
    
    public function save_post($object) {
        $this->wp_post_adapter->save_post($this, $object);
    }
    
    public function has_post() {
        return $this->wp_post_adapter ? true : false;
    }
    
    public function find($options=array()) {
        $options = $this->process_find_options($options);
        $objects = $this->db_adapter->get_results($options);
        $objects = $this->process_objects($objects, $options);
        return $objects;
    }
    
    public function find_one($options=array()) {
        $options['limit'] = 1;
        $options = $this->process_find_options($options);
        $object = $this->db_adapter->get_results($options);
        $object = isset($object[0]) ? $object[0] : null;
        $object = $this->process_objects($object, $options);
        return $object;
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
            'total_objects' => $total_count,
            'total_pages' => ceil($total_count/$options['per_page']),
            'page' => $options['page']
        );
        return $response;
    }
    
    public function count($options=array()) {
        $clauses = $this->db_adapter->get_sql_select_clauses($options);
        $clauses['select'] = 'SELECT COUNT(*)';
        $sql = implode(' ', $clauses);
        $result = $this->db_adapter->get_var($sql);
        return $result;
    }
    
    public function max($column, $options=array()) {
        $clauses = $this->db_adapter->get_sql_select_clauses($options);
        $clauses['select'] = 'SELECT MAX('.$this->db_adapter->escape($column).')';
        $sql = implode(' ', $clauses);
        $result = $this->db_adapter->get_var($sql);
        return $result;
    }
    
    public function min($column, $options=array()) {
        $clauses = $this->db_adapter->get_sql_select_clauses($options);
        $clauses['select'] = 'SELECT MIN('.$this->db_adapter->escape($column).')';
        $sql = implode(' ', $clauses);
        $result = $this->db_adapter->get_var($sql);
        return $result;
    }
    
    public function sum($column, $options=array()) {
        $clauses = $this->db_adapter->get_sql_select_clauses($options);
        $clauses['select'] = 'SELECT SUM('.$this->db_adapter->escape($column).')';
        $sql = implode(' ', $clauses);
        $result = $this->db_adapter->get_var($sql);
        return $result;
    }
    
    public function average($column, $options=array()) {
        $clauses = $this->db_adapter->get_sql_select_clauses($options);
        $clauses['select'] = 'SELECT AVERAGE('.$this->db_adapter->escape($column).')';
        $sql = implode(' ', $clauses);
        $result = $this->db_adapter->get_var($sql);
        return $result;
    }
    
    public function get_keyword_conditions($fields, $keywords) {
        $conditions = array();
        if (is_string($keywords)) {
            $keywords = preg_split('/[\s]+/', $keywords);
        }
        $formatted_fields = array();
        foreach ($fields as $field) {
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
        foreach ($keywords as $keyword) {
            $conditions[] = array($field_reference.' LIKE' => '%'.$keyword.'%');
        }
        return $conditions;
    }
    
    protected function get_total_count($options=array()) {
        $clauses = $this->db_adapter->get_sql_select_clauses($options);
        unset($clauses['limit']);
        $sql = implode(' ', $clauses);
        $sql = 'SELECT COUNT(*) FROM('.$sql.') AS count';
        $result = $this->db_adapter->get_var($sql);
        return $result;
    }

    protected function process_find_options($options) {
        if (!empty($options['joins'])) {
            if (is_string($options['joins'])) {
                $options['joins'] = array($options['joins']);
            }
            foreach ($options['joins'] as $key => $join) {
                $join_extra_on = '';
                if (is_string($join)) {
					$join_name = $join;
					$join_model_name = $join;
					$join_type = 'JOIN';
				} else {
					$join_name = $key;
					$join_model_name = isset($join['class']) ? $join['class'] : $key;
					$join_type  = isset($join['type']) ? $join['type'] : 'JOIN';
                    if(isset($join['extra_on'])){
                        $join_extra_on_clauses = $this->db_adapter->get_where_sql_clauses($join['extra_on']);
                        $join_extra_on = ' AND (' . implode(' AND ', $join_extra_on_clauses) . ')';
                    }

				}

				if (!empty($this->associations[$join_name])) {
					$association = $this->associations[$join_name];

					$join_model = new $join_model_name();

					switch ($association['type']) {
						case 'belongs_to':
							$join = array(
								'table' => $join_model->table,
								'on' => '('.$join_name.'.'.$join_model->primary_key.' = '.$this->name.'.'.$association['foreign_key'].$join_extra_on.')',
								'alias' => $join_name,
								'type' => $join_type
							);

							break;

						case 'has_many':
							$join = array(
								'table' => $join_model->table,
								'on' => '('.$join_name.'.'.$association['foreign_key'].' = '.$this->name.'.'.$this->primary_key.$join_extra_on.')',
								'alias' => $join_name,
								'type' => $join_type
							);

							break;

						case 'has_and_belongs_to_many':
							$join_table_alias = $join_model_name.$this->name;
							// The join for the HABTM join table
							$join = array(
								'table' => self::process_table_name($association['join_table']),
								'on' => '('.$join_table_alias.'.'.$association['foreign_key'].' = '.$this->name.'.'.$this->primary_key.')',
								'alias' => $join_table_alias,
								'type' => $join_type
							);
							// The join for the association model's table
							$second_join = array(
								'table' => $join_model->table,
								'on' => '('.$join_table_alias.'.'.$association['association_foreign_key'].' = '.$join_model_name.'.'.$join_model->primary_key.$join_extra_on.')',
								'alias' => $join_model_name,
								'type' => $join_type
							);
							$options['joins'][] = $second_join;
							break;
					}

                    $options['joins'][$key] = $join;
                }
            }
        }
        return $options;
    }
    
    private function update_associations($id, $model_data) {
        if (!empty($this->associations)) {
            foreach ($this->associations as $association) {
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
                    'table_reference' => self::process_table_name($association['join_table']),
                    'conditions' => array($association['foreign_key'] => $object_id)
                ));
                if (!empty($model_data[$association_name]['ids'])) {
                    foreach ($model_data[$association_name]['ids'] as $association_id) {
                        if (!empty($association_id)) {
                            $this->db_adapter->insert(
                                array(
                                    $association['foreign_key'] => $object_id,
                                    $association['association_foreign_key'] => $association_id,
                                ),
                                array(
                                    'table_reference' => self::process_table_name($association['join_table'])
                                )
                            );
                        }
                    }
                }
            }
            //AR: improvement to add fields to join table
            else if(is_array ($model_data[$association_name])){
                $this->db_adapter->delete_all(array(
                    'table_reference' => self::process_table_name($association['join_table']),
                    'conditions' => array($association['foreign_key'] => $object_id)
                ));
                foreach ($model_data[$association_name] as $obj) {
                    if(empty($obj['id']))
                        continue;
                    
                    $fields = array(
                        $association['foreign_key'] => $object_id,
                    );
                    //insert fields
                    foreach ($obj as $field => $value) {
                        $field = $field == 'id' ? $association['association_foreign_key'] : $field;
                        $fields[$field] = $value;
                    }
                    
                    $this->db_adapter->insert(
                        $fields,
                        array(
                            'table_reference' => self::process_table_name($association['join_table'])
                        )
                    );
                }
            }
        }
    }

    protected function validate_data($data) {
        if($this->validation_mode == 'all'){
            return $this->validate_all_data($data);
        }
        if(!is_null($this->validation_error)){
            return $this->validation_error;
        }
        $rules = $this->validate;
        if (!empty($rules)) {
            foreach ($rules as $field => $rule) {
                if (isset($data[$field])) {
                    $valid = $this->data_validator->validate($field, $data[$field], $rule);
                    if ($valid !== true) {
                        $this->validation_error = $valid;
                        $this->validation_error_html = $this->validation_error->get_html();
                        $this->invalid_data = $data;
                        return $valid;
                    }
                }
            }
        }
        return true;
    }

    protected function validate_all_data($data)
    {
        $rules = $this->validate;
        if (!empty($rules)) {
            $error_arr = array();
            foreach ($rules as $field => $rule) {
                if (isset($data[$field])) {
                    $valid = $this->data_validator->validate($field, $data[$field], $rule);
                    if ($valid !== true) {
                        $error_arr[] = $valid;
                    }
                }
            }
            if (!empty($error_arr) || !is_null($this->validation_error)) {
                $this->validation_error = is_null($this->validation_error) ? $error_arr : array_merge($this->validation_error,$error_arr);
                $this->validation_error_html = '';
				if ( is_array($this->validation_error) && !empty($this->validation_error) ) {
					foreach ( $this->validation_error as $error ) {
						$this->validation_error_html .= $error->get_html();
					}
				}
                $this->invalid_data = $data;
                return $error_arr;
            }

        }
        return true;
    }

    protected function invalidate( $data, $field, $message ){
        if($this->validation_mode == 'all'){
            $this->validation_error[] = new MvcDataValidationError($field, $message);
            $this->validation_error_html = '';
        }else{
            $this->validation_error = new MvcDataValidationError($field, $message);
            $this->validation_error_html = $this->validation_error->get_html();
        }

        $this->invalid_data = $data;
    }
    
    protected function process_objects($objects, $options=array()) {
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
        
        $recursive = isset($options['recursive']) ? $options['recursive'] - 1 : 2;
        
        foreach ($objects as $key => $object) {
        
            if (get_class($object) != 'MvcModelObject') {
                $array = get_object_vars($object);
                $object = $this->new_object($array);
            }
            
            if ( (!empty($this->primary_key)) && (!empty($object->{$this->primary_key})) ) {
                $object->__id = $object->{$this->primary_key};
            }
            
            if (!empty($includes) && $recursive != 0) {
                foreach ($includes as $include_key => $include) {
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
                    $model = MvcModelRegistry::get_model($association['class']);
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
                                    'table' => self::process_table_name($association['join_table']),
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
    
    public static function process_table_name($table_name) {
        global $wpdb;
        $table_name = str_replace('{prefix}', $wpdb->prefix, $table_name);
        return $table_name;
    }
    
    protected function init_schema() {
        if ( isset( self::$describe_cache[ $this->table ] ) ) {
            $results = self::$describe_cache[ $this->table ];
        } else {
            $results = $this->db_adapter->get_results(
                "DESCRIBE {$this->table_reference}"
            );
            self::$describe_cache[ $this->table ] = $results;
        }
        
        $schema = array();
        
        foreach ($results as $result) {
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
        if (!is_array($this->associations)) {
            $this->associations = array();
        }
        if (!empty($this->belongs_to)) {
            foreach ($this->belongs_to as $key => $value) {
                $config = null;
                if (is_string($value)) {
                    $association_name = $value;
                    $config = array(
                        'type' => 'belongs_to',
                        'name' => $association_name,
                        'class' => $association_name,
                        'foreign_key' => MvcInflector::underscore($association_name).'_id',
                        'includes' => null,
                        'dependent' => false
                    );
                } else if (is_string($key) && is_array($value)) {
                    $association_name = $key;
                    $config = array(
                        'type' => 'belongs_to',
                        'name' => empty($value['name']) ? $association_name : $value['name'],
                        'class' => empty($value['class']) ? $association_name : $value['class'],
                        'foreign_key' => empty($value['foreign_key']) ? MvcInflector::underscore($association_name).'_id' : $value['foreign_key'],
                        'includes' => isset($value['fields']) ? $value['fields'] : null,
                        'dependent' => isset($value['dependent']) ? $value['dependent'] : false
                    );
                }
                if (!empty($config)) {
                    $this->associations[$association_name] = $config;
                }
            }
        }
        if (!empty($this->has_many)) {
            foreach ($this->has_many as $key => $value) {
                $config = null;
                if (is_string($value)) {
                    $association_name = $value;
                    $config = array(
                        'type' => 'has_many',
                        'name' => $association_name,
                        'class' => $association_name,
                        'foreign_key' => MvcInflector::underscore($this->name).'_id',
                        'fields' => null,
                        'includes' => null,
                        'dependent' => false
                    );
                } else if (is_string($key) && is_array($value)) {
                    $association_name = $key;
                    $config = array(
                        'type' => 'has_many',
                        'name' => empty($value['name']) ? $association_name : $value['name'],
                        'class' => empty($value['class']) ? $association_name : $value['class'],
                        'foreign_key' => empty($value['foreign_key']) ? MvcInflector::underscore($this->name).'_id' : $value['foreign_key'],
                        'fields' => isset($value['fields']) ? $value['fields'] : null,
                        'includes' => null,
                        'dependent' => isset($value['dependent']) ? $value['dependent'] : false
                    );
                }
                if (!empty($config)) {
                    $this->associations[$association_name] = $config;
                }
            }
        }
        if (!empty($this->has_and_belongs_to_many)) {
            foreach ($this->has_and_belongs_to_many as $key => $value) {
                if (is_string($key) && is_array($value)) {
                    $association_name = $key;
                    if (isset($value['fields'])) {
                        foreach ($value['fields'] as $key => $field) {
                            if (strpos($field,'.') !== false) {
                                //AR: contains '.', dont prepand a table
                                $value['fields'][$key] = $field;
                            }
                            else{
                                $value['fields'][$key] = $association_name.'.'.$field;
                            }
                            
                        }
                    }
                    $config = array(
                        'type' => 'has_and_belongs_to_many',
                        'name' => $association_name,
                        'class' => $association_name,
                        'foreign_key' => isset($value['foreign_key']) ? $value['foreign_key'] : MvcInflector::underscore($this->name).'_id',
                        'association_foreign_key' => isset($value['association_foreign_key']) ? $value['association_foreign_key'] : MvcInflector::underscore($association_name).'_id',
                        'join_table' => self::process_table_name($value['join_table']),
                        'fields' => isset($value['fields']) ? $value['fields'] : null,
                        'includes' => isset($value['includes']) ? $value['includes'] : null,
                        'dependent' => isset($value['dependent']) ? $value['dependent'] : false
                    );
                    $this->associations[$association_name] = $config;
                }
            }
        }
    }
    
    protected function init_properties() {
        $this->properties = array();
        foreach ($this->associations as $association_name => $association) {
            $property_name = null;
            if ($association['type'] == 'belongs_to') {
                $property_name = MvcInflector::underscore($association_name);
            } else if (in_array($association['type'], array('has_many', 'has_and_belongs_to_many'))) {
                $property_name = MvcInflector::tableize($association_name);
            }
            if ($property_name) {
                $this->properties[$property_name] = array(
                    'type' => 'association',
                    'association' => $association
                );
            }
        }
    }
    
    protected function check_for_obsolete_functionality() {
        $obsolete_attributes = array(
            'admin_pages' => array('should be defined as \'AdminPages\' in MvcConfiguration', 'http://wpmvc.org/documentation/1.2/66/adminpages/'),
            'admin_columns' => array('should be defined as \'default_columns\' in the admin controller', 'http://wpmvc.org/documentation/1.2/16/default_columns/'),
            'admin_searchable_fields' => array('should be defined as \'default_searchable_fields\' in the admin controller', 'http://wpmvc.org/documentation/1.2/18/default_searchable_fields/'),
            'admin_search_joins' => array('should be defined as \'default_searchable_joins\' in the admin controller', 'http://wpmvc.org/documentation/1.2/17/default_search_joins/'),
            'public_searchable_fields' => array('should be defined as \'default_searchable_fields\' in the public controller', 'http://wpmvc.org/documentation/1.2/18/default_searchable_fields/'),
            'public_search_joins' => array('should be defined as \'default_searchable_joins\' in the public controller', 'http://wpmvc.org/documentation/1.2/17/default_search_joins/'),
            'hide_menu' => array('should be defined as \'in_menu\' in \'AdminPages\' in MvcConfiguration', 'http://wpmvc.org/documentation/1.2/66/adminpages/')
        );
        foreach ($obsolete_attributes as $attribute => $value) {
            if (isset($this->$attribute)) {
                $message = $value[0];
                $url = $value[1];
                $message = 'The \''.$attribute.'\' attribute (in the '.$this->name.' model) '.$message.' as of WP MVC 1.2';
                $message .= ' (<a href="'.$url.'">read more</a>).';
                MvcError::fatal($message);
            }
        }
    }
    
    protected function object_to_array($data) {
        if (is_object($data)) {
            return get_object_vars($data);
        }
        return $data;
    }
    
    public function __call($method, $args) {
        if (substr($method, 0, 8) == 'find_by_') {
            $attribute = substr($method, 8);
            if (isset($this->schema[$attribute])) {
                $object = $this->find(array('conditions' => array($attribute => $args[0])));
                return $object;
            } else {
                MvcError::fatal('Undefined attribute: '.$attribute.' for class: '.get_class($this).' when calling: '.$method.'.');
            }
        }
        if (substr($method, 0, 12) == 'find_one_by_') {
            $attribute = substr($method, 12);
            if (isset($this->schema[$attribute])) {
                $object = $this->find_one(array('conditions' => array($attribute => $args[0])));
                return $object;
            } else {
                MvcError::fatal('Undefined attribute: '.$attribute.' for class: '.get_class($this).' when calling: '.$method.'.');
            }
        }
        MvcError::fatal('Undefined method: '.get_class($this).'::'.$method.'.');
    }

}
