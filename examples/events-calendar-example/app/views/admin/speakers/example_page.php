<h2><?php echo MvcInflector::titleize($this->action); ?></h2>

<p>This is just an example of a custom action and view.</p>

<p>Here are all of the speakers, ordered by last name:</p>

<?php foreach($speakers as $object): ?>
    <?php $this->render_view('speakers/_item', array('locals' => array('object' => $object))); ?>
<?php endforeach; ?>