<?php echo "<?php\n"; ?>

class <?php echo $name ?> extends MvcModel {

	var $display_field = 'name';
	
	var $admin_columns = array(
		'id',
		'name'
	);
	
}

<?php echo '?>'; ?>