<?php

makelos_setting(array(
    'app_name' => 'Documentation Site',
    'servers' => array(
    'docs.akelos.org' => array('public_key' => './config/pub.key', 'username' => 'bermi'),
    )
));


makelos_task('expects', array(
    'parameters' => 'one,two',
    'run' => array('php' => 'echo "Siiii"')
));

makelos_task('doit', array(
    'run' => array('php' => 'echo "Siiii"')
));

makelos_task('dont', array(
    'run' => array('php' => '$Makelos->run("doit");')
));


?>