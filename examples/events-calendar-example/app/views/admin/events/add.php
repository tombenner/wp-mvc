<h2><?php echo MvcInflector::titleize($this->action); ?> <?php echo MvcInflector::titleize($model->name); ?></h2>

<?php echo $this->form->create($model->name); ?>
<?php echo $this->form->belongs_to_dropdown('Venue', $venues, array('style' => 'width: 200px;', 'empty' => true)); ?>
<?php echo $this->form->input('date', array('label' => 'Date (YYYY-MM-DD)')); ?>
<?php echo $this->form->input('time', array('label' => 'Time (24-hour clock)')); ?>
<?php echo $this->form->input('description'); ?>
<?php echo $this->form->has_many_dropdown('Speaker', $speakers, array('style' => 'width: 200px;', 'empty' => true)); ?>
<?php echo $this->form->end('Add'); ?>