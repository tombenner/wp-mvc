<?php

$wordpress_path = getenv('WPMVC_WORDPRESS_PATH');
$wordpress_path = $wordpress_path ? rtrim($wordpress_path, '/').'/' : dirname(__FILE__).'/../../../../';

require_once $wordpress_path.'wp-load.php';

$shell = new MvcShellDispatcher($argv);

echo "\n";

?>