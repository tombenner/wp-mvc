<h2><?php echo $object->__name; ?></h2>

<?php $this->render_view('_info', array('locals' => array('object' => $object, 'show_name' => false))); ?>

<p>
    <?php echo $this->html->link('&#8592; All Speakers', array('controller' => 'speakers')); ?>
</p>