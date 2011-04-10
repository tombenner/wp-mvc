<?php

$wp_root = dirname(__FILE__).'/../../../../';

require_once $wp_root.'wp-load.php';

$shell = new MvcShellDispatcher($argv);

echo "\n";

?>