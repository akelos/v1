<?php

makelos_task('db:recode', array(
    'description' => 'Recodes MySQL database columns and tables to use utf8_ci'
));

makelos_task('db:backup', array(
    'description' => 'Dumps a backup of the database schema into db/{ENVIROMENT}.sql'
));

makelos_task('db:structure:dump', array(
    'description' => 'Dumps current database schema stucture into db/{ENVIROMENT}_structure.sql'
));

?>