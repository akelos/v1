<?php echo '<?php'; ?>

<?php foreach($tasks as $task): ?>

// Task code can be found at lib/tasks/<?php echo str_replace(':', '/', $task); ?>.task.php
makelos_task('<?php echo $task; ?>', array(
    'description' => 'Describe <?php echo $task; ?> here',
    // 
    /* 
        // You can create dynamic autocompletion by creating 
        // a lib/tasks/<?php echo str_replace(':', '/', $task); ?>.autocompletion.php
        // file wich should return one option per line
        'autocompletion' => 'ENVIRONMENT=production --server -d',
        'with_defaults' => array(
            'default_var' => 'value', 
            'background' => true, // 'daemon' => true,
            
            ), 
        
        // You can also run schell scripts or inline php code 
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