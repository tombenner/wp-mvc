<h2><?php echo MvcInflector::titleize($this->action); ?> <?php echo MvcInflector::titleize($model->name); ?></h2>

<?php echo $this->form->create($model->name); ?>
<?php echo $this->form->input('name'); ?>
<?php echo $this->form->input('url', array('label' => 'URL', 'style' => 'width: 300px;')); ?>
<?php echo $this->form->input('address1', array('label' => 'Address 1')); ?>
<?php echo $this->form->input('address2', array('label' => 'Address 2')); ?>
<?php echo $this->form->input('city'); ?>
<?php echo $this->form->input('state', array('style' => 'width: 40px;')); ?>
<?php echo $this->form->input('zip', array('style' => 'width: 80px;')); ?>
<?php echo $this->form->input('description'); ?>
<?php echo $this->form->end('Add'); ?>