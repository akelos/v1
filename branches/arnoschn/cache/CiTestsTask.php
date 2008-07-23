<?php

class CiTestsTask extends Task
{
    var $_akelosPath;
    var $_testPath = 'ci-tests';
    var $_apacheUser = 'www-data';
    var $_apacheGroup = 'www-data';
    public function setAkelosPath($path)
    {
        $this->_akelosPath = $path;
    }
    
    public function setApacheGroup($group)
    {
        $this->_apacheGroup = $group;
    }
    public function setApacheUser($user)
    {
        $this->_apacheUser = $user;
    }
    public function main()
    {
        $this->_installTestApp();
        $this->_copyFiles();
        $this->_runTests();
    }
    
    private function _runTests()
    {
        chdir($this->_akelosPath.DS.$this->_testPath);
        $this->_execute('chgrp -R ' .$this->_apacheGroup.' ' .$this->_akelosPath.DS.$this->_testPath);
        $this->_execute('chown -R ' .$this->_apacheUser.' ' .$this->_akelosPath.DS.$this->_testPath);
        $this->_execute('export AK_FRAMEWORK_DIR="'.$this->_akelosPath.'";/usr/bin/env php  '.$this->_akelosPath.DS.$this->_testPath.'/script/extras/xinc-ci_tests.php',true);
    }
    
    private function _installTestApp()
    {
        !defined('DS')?define('DS',DIRECTORY_SEPARATOR):null;
        //passthru('rm -Rf '.$this->_testPath);
        $excludeTestPath = basename($this->_testPath).' nanoweb';
        //mkdir($this->_testPath,0777,true);
        $this->_execute($this->_akelosPath.'/akelos -d '.$this->_testPath.' --force -e '.$excludeTestPath,true);
        //$this->_execute('rm -Rf '.$this->_testPath.DS.'app'.DS.'* ',true);
        //$this->_execute('rm -Rf '.$this->_testPath.DS.'public'.DS.'* ',true);
        //$this->_execute("cp -Rf ".$this->_akelosPath.DS.'test'.DS.'fixtures'.DS.'app'."   ".$this->_testPath.DS, true);
        //$this->_execute("cp -Rf ".$this->_akelosPath.DS.'test'.DS.'fixtures'.DS.'public'."   ".$this->_testPath.DS, true);
        $this->_execute("touch ".$this->_testPath.DS.'log'.DS."testing.log", true);
        $this->_execute("chmod 777 ".$this->_testPath.DS.'log'.DS."testing.log", true);
        $this->_execute("cp -Rf ".$this->_akelosPath.DS.'test'."   ".$this->_testPath.DS, true);
        $this->_execute("cp  ".$this->_akelosPath.DS.'test'.DS.'.nwaccess'."   ".$this->_testPath.DS, true);
        $this->_execute("find ".$this->_testPath.DS."/ -type d  -name '.svn' -exec sh -c 'exec rm -Rf \"$@\"' find-copy {} +",true);
        //$this->_execute("cp -Rf ".$this->_akelosPath.DS.'tmp'.DS.'installer_versions'."   ".$this->_testPath.DS.'test'.DS.'tmp', true);
        //$this->_execute("cp -Rf ".$this->_akelosPath.DS.'tmp'.DS.'installer_versions'."   ".$this->_testPath.DS.'tmp', true);
        //$this->_execute("cp -Rf ".$this->_akelosPath.DS.'test'.DS.'suites'."   ".$this->_testPath.DS.'test'.DS, true);
    }
    private function _execute($cmd,$execute=true)
    {
        if (!$execute) {
            echo 'DRY RUN: '.$cmd."\n";
        } else {
            passthru($cmd);
        }
    }
    private function _copyFiles()
    {
        $files = array('script/extras/xinc-ci_tests.php'=>array('script/extras','-f'),
                       'script/extras/ci-config.yaml'=>array('config','-f'),
                       'script/extras/caching.yml'=>array('config','-f'),
                       'script/extras/sessions.yml'=>array('config','-f'),
                       'script/extras/fix_htaccess.php'=>array('config','-f'),
                       'script/extras/mysql-testing.php'=>array('config','-f'),
                       'script/extras/postgres-testing.php'=>array('config','-f'),
                       'script/extras/sqlite-testing.php'=>array('config','-f'),
                       'script/extras/routes.php'=>array('config','-f'),
                       'script/extras/database.sqlite'=>array('config'),
                       );
        
        foreach ($files as $file=>$dest) {
            $this->_execute('cp '.(isset($dest[1])?$dest[1]:'').' '.$this->_akelosPath.DIRECTORY_SEPARATOR.$file.' '.$this->_testPath.DIRECTORY_SEPARATOR.$dest[0].DIRECTORY_SEPARATOR.basename($file));
        }

    }
}


?>