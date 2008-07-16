<?php

#$Map->generate_helper_functions = true;

$Map->people('/people',array('controller'=>'person','action'=>'index'),array(),array('method'=>'get'));

//RESTful Service
$Map->person('/person/:id',array('controller'=>'person','action'=>'show',  'id'=>COMPULSORY),array(),array('method'=>'get'));
$Map->person('/person',    array('controller'=>'person','action'=>'create'),                 array(),array('method'=>'post'));
$Map->person('/person/:id',array('controller'=>'person','action'=>'update','id'=>COMPULSORY),array(),array('method'=>'put'));
$Map->person('/person/:id',array('controller'=>'person','action'=>'delete','id'=>COMPULSORY),array(),array('method'=>'delete'));

//Html-Views
$Map->edit_person('/person/edit/:id',array('controller'=>'person','action'=>'edit'),         array(),array('method'=>'get'));

//Browser-Version
#$Map->person('/person/:id',array('controller'=>'person','action'=>'show',  'id'=>COMPULSORY),array(),array('method'=>'get'));
#$Map->person('/person',    array('controller'=>'person','action'=>'create'),                 array(),array('method'=>'post'));
$Map->update_person('/person/update/:id',array('controller'=>'person','action'=>'update','id'=>COMPULSORY),array(),array('method'=>'post'));
$Map->delete_person('/person/delete/:id',array('controller'=>'person','action'=>'delete','id'=>COMPULSORY),array(),array('method'=>'post'));



#$Map->connect('/:controller/:action/:id', array('controller' => 'page', 'action' => 'index'));
#$Map->connect('/', array('controller' => 'page', 'action' => 'index'));

?>