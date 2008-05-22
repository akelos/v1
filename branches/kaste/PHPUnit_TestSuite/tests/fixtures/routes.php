<?php

// You can find more about routes on /lib/AkRouters.php and /test/test_AkRouter.php

$Map->connect('/:controller/:action/:id', array('controller' => 'page', 'action' => 'index', 'id'=>OPTIONAL),array('id'=>'/\d{1,}/'));
$Map->connect('/', array('controller' => 'page', 'action' => 'index'));
$Map->connect('/:artist/:album/tags',array('controller'=>'tags'));
$Map->connect('/admin/logs/:controller/:action/:id',array('module'=>'admin/logs'));
$Map->connect('/:module/:controller/:action/:id',array('action'=>COMPULSORY));

?>
