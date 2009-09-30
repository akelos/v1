<?php

$file_path = AK_BASE_DIR.DS.'db'.DS.AK_ENVIRONMENT.'_sctucture.sql';

echo "Dumping DB schema to $file_path\n";
$db = Ak::db();

$database_settings = Ak::getSettings('database');
$command = "mysqldump --no-data -u {$database_settings['user']} ".(empty($database_settings['password'])?'':"-p{$database_settings['password']} ")."{$database_settings['database_name']}";

Ak::file_put_contents($file_path, `$command`);

?>