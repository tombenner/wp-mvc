<div>
    <?php
        $title = $object->name;
        if(isset($show_date) && $show_date) {
            $title .= ' on '.date('F jS, Y', strtotime($object->date));
        }
        echo $this->html->event_link($object, array('text' => $title));
    ?>
</div>