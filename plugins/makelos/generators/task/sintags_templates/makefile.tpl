<?php echo '<?php'; ?>

<?php foreach($tasks as $task): ?>

makelos_task('<?php echo $task; ?>', array(
    'description' => 'Describe <?php echo $task; ?> here',
    
    /* You can also run schell scripts or inline php code 
    'run' => array(
        'command' => '/url/local/bin/command_name',
        'php' => <<<PHP
                    \$Makelos->displayAvailableTasks();
PHP
    )
    */
));

<?php endforeach; ?>

?>