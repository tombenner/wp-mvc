<h2>Edit <?php echo $name_titleized; ?></h2>
<?php
echo '
<?php echo $this->form->create($model->name, array(\'is_admin\' => $this->is_admin)); ?>
<?php echo $this->form->open_admin_table(); ?>
<?php echo $this->form->input(\'name\'); ?>
<?php echo $this->form->close_admin_table(); ?>
<?php echo $this->form->end(\'Update\'); ?>';
?>
