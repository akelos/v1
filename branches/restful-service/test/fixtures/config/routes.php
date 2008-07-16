<?php

$Map->connect('/:controller/:action/:id', array('controller' => 'page', 'action' => 'index'));
$Map->connect('/', array('controller' => 'page', 'action' => 'index'));

?>