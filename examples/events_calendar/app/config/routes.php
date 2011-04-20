<?php

Router::public_connect('{:controller}', array('action' => 'index'));
Router::public_connect('{:controller}/{:id:[\d]+}', array('action' => 'show'));
Router::public_connect('{:controller}/{:action}/{:id:[\d]+}');

?>