#!/usr/bin/env sh

akelos_base_path="/Users/bermi/Projects/akelos_framework";
php_bin=/Applications/MAMP/bin/php5/bin/php;

clear;

$akelos_base_path/trunk/akelos -d $akelos_base_path/tests -deps --force;

cp $akelos_base_path/branches/bermi/ci_files/ci-config.yaml         $akelos_base_path/tests/config/ci-config.yaml;
cp $akelos_base_path/branches/bermi/ci_files/fix_htaccess.php       $akelos_base_path/tests/config/fix_htaccess.php;
cp $akelos_base_path/branches/bermi/ci_files/mysql-testing.php      $akelos_base_path/tests/config/mysql-testing.php;
cp $akelos_base_path/branches/bermi/ci_files/postgres-testing.php   $akelos_base_path/tests/config/postgres-testing.php;
cp $akelos_base_path/branches/bermi/ci_files/routes.php             $akelos_base_path/tests/config/routes.php;
cp $akelos_base_path/branches/bermi/ci_files/sqlite-testing.php     $akelos_base_path/tests/config/sqlite-testing.php;


cd $akelos_base_path/tests/test;
$php_bin  ../script/extras/ci_tests.php;

