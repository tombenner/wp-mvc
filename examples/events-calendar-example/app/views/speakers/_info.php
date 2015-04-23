<div class="speaker-info">
    <?php
        $show_name = isset($show_name) ? $show_name : true;
        if ($show_name) {
            echo '<h3>'.$this->html->speaker_link($object).'</h3>';
        }
        if (!empty($object->url)) {
            echo $this->html->link('Website', $object->url, array('target' => '_blank'));
        }
        if (!empty($object->description)) {
            echo '<p>'.$object->description.'</p>';
        }
    ?>
</div>