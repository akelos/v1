<?php

// You can find more about routes on /lib/AkRouters.php and /test/test_AkRouter.php

$Map->connect('/', array('controller' => 'page', 'action' => 'index'));
$Map->connect('/:artist/:album/tags',array('controller'=>'tags'));
$Map->connect('/admin/logs/:type',array('module'=>'admin','controller'=>'logs','action'=>'list','type'=>'all'));
$Map->connect('/admin/:controller/:action/:id',array('module'=>'admin','action'=>COMPULSORY));
$Map->connect('/:controller/:action/:id', array('controller' => 'page', 'action' => 'index'),array('id'=>'\d{1,}'));

?>
