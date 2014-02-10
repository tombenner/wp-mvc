<?php echo "<?php\n"; ?>

class Admin<?php echo $name_pluralized ?>Controller extends MvcAdminController {
	
var $default_columns = array('id', 'name');

public function add() {
//you can here assign variables if there is drop-down select field in view
parent::add();
	}

public function edit() {
//you can here assign variables if there is drop-down select field in view
parent::edit();
	}	
}

//passing variable to view
public function assign_var($varname,$model,$fields=array('id'))
	{
		$this->load_model($model);
		$data = $this->{$model}->find(array('selects' => $fields));
		$this->set($varname, $data);
	}

}

<?php echo '?>'; ?>
