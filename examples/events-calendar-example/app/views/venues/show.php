<h2><?php echo $object->__name; ?></h2>

<?php
    echo '<div>'.$object->address1.'</div>';
    if (!empty($object->address2)) {
        echo '<div>'.$object->address2.'</div>';
    }
    echo '<div>'.$object->city.', '.$object->state.', '.$object->zip.'</div>';
?>

<h3>Events</h3>

<?php $this->render_view('events/_item', array('collection' => $object->events, 'locals' => array('show_date' => true))); ?>

<p>
    <?php echo $this->html->link('&#8592; All Venues', array('controller' => 'venues')); ?>
</p>