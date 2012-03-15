<?php

$wordpress_path = getenv('WPMVC_WORDPRESS_PATH');
$wordpress_path = $wordpress_path ? rtrim($wordpress_path, '/').'/' : dirname(__FILE__).'/../../../../';

$_SERVER = array(
    "HTTP_HOST" => "localhost",
    "SERVER_NAME" => "localhost",
    "REQUEST_URI" => "/",
    "REQUEST_METHOD" => "GET"
);

require_once $wordpress_path.'wp-load.php';

$shell = new MvcShellDispatcher($argv);

echo "\n";

?>