<h2>Edit <?php echo $name_titleized; ?></h2>
<?php
echo '
<?php echo $this->form->create($model->name); ?>
foreach($fields as $f)
{
if($f->Key=='PRI')
continue;
echo '
<?php echo $this->form->input(\''.$f->Field.'\'); ?>
';
}
<?php echo $this->form->end(\'Update\'); ?>';
?>
