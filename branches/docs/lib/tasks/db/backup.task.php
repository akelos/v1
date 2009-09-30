<?php

$default_options = array(
'mysqldump' => trim(`which mysqldump`),
'dest' => AK_BASE_DIR.DS.'db'.DS.AK_ENVIRONMENT.'.sql',
'gzip' => trim(`which gzip`),
'compress' => false,
);

$options = array_merge($default_options, $options);

if(empty($options['mysqldump'])){
    die("Could not find mysqldump binary.\nPlease try setting the path with ./makelos db:backup mysqldump=/path/to/mysqldump\n");
}

echo "\nPerforming backup of DB schema to {$options['dest']}\n";
$db = Ak::db();

$database_settings = Ak::getSettings('database');
$command = "{$options['mysqldump']} --hex-blob --add-drop-table --single-transaction --skip-comments -u {$database_settings['user']} ".(empty($database_settings['password'])?'':"-p{$database_settings['password']} ")."{$database_settings['database_name']}";

Ak::file_put_contents($options['dest'], ' '); // touch to create dirs if needed
echo "Running: $command > {$options['dest']}\n";

`$command > {$options['dest']};`;
echo `du -h {$options['dest']}`;

if($options['compress'] && !empty($options['gzip'])){
    echo "\nCompressing backup {$options['gzip']} -f {$options['dest']};\n";
    `{$options['gzip']} -f {$options['dest']};`;
    echo `du -h {$options['dest']}.gz`;
}

?>