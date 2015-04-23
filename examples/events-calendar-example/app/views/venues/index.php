<?php if (is_page()) { echo 'single'; } ?>
<h2><?php echo MvcInflector::pluralize($model->name); ?></h2>

<?php foreach($objects as $object): ?>

    <?php $this->render_view('_item', array('locals' => array('object' => $object))); ?>

<?php endforeach; ?>

<?php echo $this->pagination(); ?>