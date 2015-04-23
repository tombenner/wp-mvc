<h2><?php echo $object->speaker_names; ?></h2>

<h3><?php echo date('F jS, Y', strtotime($object->date)).' at '.$this->html->venue_link($object->venue); ?></h3>

<?php $this->render_view('speakers/_info', array('collection' => $object->speakers, 'as' => 'object')); ?>

<p>
    <?php echo $this->html->link('&#8592; All Events', array('controller' => 'events')); ?>
</p>