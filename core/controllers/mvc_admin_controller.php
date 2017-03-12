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
            $this->flash('notice', __('Successfully deleted!', 'wpmvc'));
        } else {
            $this->flash('warning', 'A '.MvcInflector::humanize($this->model->name).' with ID "'.$this->params['id'].'" couldn\'t be found.');
        }
        $url = MvcRouter::admin_url(array('controller' => $this->name, 'action' => 'index'));
        $this->redirect($url);
    }
    
    public function verify_id_param() {
        if (empty($this->params['id'])) {
            die('No ID specified');
        }
    }
    
    public function create_or_save() {
        if (!empty($this->params['data'][$this->model->name])) {
            $object = $this->params['data'][$this->model->name];
            if (empty($object['id'])) {
                if($this->model->create($this->params['data'])) {
                    $id = $this->model->insert_id;
                    $url = MvcRouter::admin_url(array('controller' => $this->name, 'action' => 'edit', 'id' => $id));
                    $this->flash('notice', __('Successfully created!', 'wpmvc'));
                    $this->redirect($url);
                } else {
                    $this->flash('error', $this->model->validation_error_html);
                    $this->set_object();
                }
            } else {
                if ($this->model->save($this->params['data'])) {
                    $this->flash('notice', __('Successfully saved!', 'wpvmc'));
                    $this->refresh();
                } else {
                    $this->flash('error', $this->model->validation_error_html);
                }
            }
        }
    }
    
    public function create() {
        if (!empty($this->params['data'][$this->model->name])) {
            $id = $this->model->create($this->params['data']);
            $url = MvcRouter::admin_url(array('controller' => $this->name, 'action' => 'edit', 'id' => $id));
            $this->flash('notice', __('Successfully created!', 'wpmvc'));
            $this->redirect($url);
        }
    }
    
    public function save() {
        if (!empty($this->params['data'][$this->model->name])) {
            if ($this->model->save($this->params['data'])) {
                $this->flash('notice', __('Successfully saved!', 'wpvmc'));
                $this->refresh();
            } else {
                $this->flash('error', $this->model->validation_error_html);
            }
        }
    }
    
    public function set_objects() {
        $this->init_default_columns();
        $this->process_params_for_search();
        $collection = $this->model->paginate($this->params);
        $this->set('objects', $collection['objects']);
        $this->set_pagination($collection);
    
    }
    
    public function set_pagination($collection) {
        $url_params = MvcRouter::admin_url_params(array('controller' => $this->name));
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
    
    protected function process_params_for_search() {
        $this->params['page'] = empty($this->params['page_num']) ? 1 : $this->params['page_num'];
        if (!empty($this->params['q']) && !empty($this->default_searchable_fields)) {
            $this->params['conditions'] = $this->model->get_keyword_conditions($this->default_searchable_fields, $this->params['q']);
            if (!empty($this->default_search_joins)) {
                $this->params['joins'] = $this->default_search_joins;
                $this->params['group'] = $this->model->name.'.'.$this->model->primary_key;
            }
        }
    }
    
    protected function init_default_columns() {
        if (empty($this->default_columns)) {
            MvcError::fatal('No columns defined for this view.  Please define them in the controller, like this:
                <pre>
                    class '.MvcInflector::camelize($this->name).'Controller extends MvcAdminController {
                        var $default_columns = array(\'id\', \'name\');
                    }
                </pre>');
        }
        $admin_columns = array();
        foreach ($this->default_columns as $key => $value) {
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
        $this->default_columns = $admin_columns;
    }

}

?>
