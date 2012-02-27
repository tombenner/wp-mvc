<?php

class MvcModelObject {
	
	public $__model_name = null;
	private $__settings = null;
	
	function __construct($model) {
		$this->__settings = array();
		$this->__settings['properties'] = $model->properties;
		$this->__settings['model'] = array(
			'name' => $model->name,
			'primary_key' => $model->primary_key
		);
		$this->__model_name = $model->name;
	}
	
	public function __get($property_name) {
		if (!empty($this->__settings['properties'][$property_name])) {
			$property = $this->__settings['properties'][$property_name];
			if ($property['type'] == 'association') {
				$objects = $this->get_associated_objects($property['association']);
				$this->$property_name = $objects;
				return $this->$property_name;
			}
		}
		MvcError::warning('Undefined property: MvcModelObject::'.$property_name.'.');
	}
	
	private function get_associated_objects($association) {
		$model_name = $association['class'];
		$model = MvcModelRegistry::get_model($model_name);
		switch ($association['type']) {
			case 'belongs_to':
				$associated_object = $model->find_by_id($this->{$association['foreign_key']}, array(
					'recursive' => 0
				));
				return $associated_object;
				
			case 'has_many':
				$associated_objects = $model->find(array(
					'selects' => $association['fields'],
					'conditions' => array($association['foreign_key'] => $this->__id),
					'recursive' => 0
				));
				return $associated_objects;
			
			case 'has_and_belongs_to_many':
				$join_alias = 'JoinTable';
				$associated_objects = $model->find(array(
					'selects' => $association['fields'],
					'joins' => array(
						'table' => $this->process_table_name($association['join_table']),
						'on' => $join_alias.'.'.$association['association_foreign_key'].' = '.$model_name.'.'.$model->primary_key,
						'alias' => $join_alias
					),
					'conditions' => array($join_alias.'.'.$association['foreign_key'] => $this->__id),
					'recursive' => 0
				));
				return $associated_objects;
		}
	}
	
}

?>