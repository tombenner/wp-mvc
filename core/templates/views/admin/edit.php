<h2>Edit <?php echo $name_titleized; ?></h2>
<?php
echo '
<?php echo $this->form->create($model->name); ?>
<?php echo $this->form->input(\'name\'); ?>
<?php echo $this->form->end(\'Update\'); ?>';
?>