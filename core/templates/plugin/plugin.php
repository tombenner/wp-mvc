<?php echo "<?php\n"; ?>
/*
Plugin Name: <?php echo $name_titleized."\n"; ?>
Plugin URI: 
Description: 
Author: 
Version: 
Author URI: 
*/

register_activation_hook(__FILE__, '<?php echo $name_underscored; ?>_activate');
register_deactivation_hook(__FILE__, '<?php echo $name_underscored; ?>_deactivate');

function <?php echo $name_underscored; ?>_activate() {
    require_once dirname(__FILE__).'/<?php echo $name_underscored; ?>_loader.php';
    $loader = new <?php echo $name_camelized; ?>Loader();
    $loader->activate();
}

function <?php echo $name_underscored; ?>_deactivate() {
    require_once dirname(__FILE__).'/<?php echo $name_underscored; ?>_loader.php';
    $loader = new <?php echo $name_camelized; ?>Loader();
    $loader->deactivate();
}

<?php echo '?>'; ?>