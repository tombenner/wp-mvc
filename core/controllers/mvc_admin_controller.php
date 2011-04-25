<?php

class MvcAdminController extends MvcController {

	public $is_admin = true;
	
	public function index() {
		
		$this->set_objects();
	
	}
	
	public function add() {
	
		$this->create_or_save();
	
	}
	
	public function edit() {
		
		$this->verify_id_param();
		$this->create_or_save();
		$this->set_object();
	
	}
	
	public function delete() {
		
		$this->verify_id_param();
		$this->set_object();
		if (!empty($this->object)) {
			$this->model->delete($this->params['id']);
			$this->flash('notice', 'Successfully deleted!');
		} else {
			$this->flash('warning', 'A '.Inflector::humanize($this->model->name).' with ID "'.$this->params['id'].'" couldn\'t be found.');
		}
		$url = Router::admin_url(array('controller' => $this->name, 'action' => 'index'));
		$this->redirect($url);
	
	}
	
	public function verify_id_param() {
	
		if (empty($this->params['id'])) {
			die('No ID specified');
		}
		
	}
	
	public function create_or_save() {
	
		if (!empty($this->params['data'])) {
			if (!empty($this->params['data'][$this->model->name])) {
				$object = $this->params['data'][$this->model->name];
				if (empty($object['id'])) {
					$model = $this->model;
					$model->create($this->params['data']);
					$id = $model->insert_id;
					$url = Router::admin_url(array('controller' => $this->name, 'action' => 'edit', 'id' => $id));
					$this->flash('notice', 'Successfully created!');
					$this->redirect($url);
				} else {
					if ($this->model->save($this->params['data'])) {
						$this->flash('notice', 'Successfully saved!');
						$this->refresh();
					} else {
						$this->flash('error', $this->model->validation_error_html);
					}
				}
			}
		}
		
	}
	
	public function set_objects() {
	
		$this->params['page'] = empty($this->params['page_num']) ? 1 : $this->params['page_num'];
		
		if (!empty($this->params['q'])) {
			if (!empty($this->model->admin_searchable_fields)) {
				$conditions = array();
				foreach($this->model->admin_searchable_fields as $field) {
					$conditions[] = array($field.' LIKE' => '%'.$this->params['q'].'%');
				}
				$this->params['conditions'] = array(
					'OR' => $conditions
				);
			}
			if (!empty($this->model->admin_search_joins)) {
				$this->params['joins'] = $this->model->admin_search_joins;
			}
		}
		
		$collection = $this->model->paginate($this->params);
		
		$this->set('objects', $collection['objects']);
		$this->set_pagination($collection);
	
	}
	
	public function set_pagination($collection) {
	
		$url_params = Router::admin_url_params(array('controller' => $this->name));
		$params = $this->params;
		unset($params['page_num']);
		$params['page'] = $url_params['page'];
		$this->set('pagination', array(
			'base' => get_admin_url().'admin.php%_%',
			'format' => '?page_num=%#%',
			'total' => $collection['total_pages'],
			'current' => $collection['page'],
			'add_args' => $params
		));
		
	}
	
	public function after_action($action) {
	
	}

}

?>