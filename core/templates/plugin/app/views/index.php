<h2><?php echo $name_titleized_pluralized; ?></h2>
<?php
echo '
<?php foreach ($objects as $object): ?>

    <?php $this->render_view(\'_item\', array(\'locals\' => array(\'object\' => $object))); ?>

<?php endforeach; ?>

<?php echo $this->pagination(); ?>';