<?php

class CiTestsTask extends Task
{
    var $_akelosPath;
    var $_testPath = 'ci-tests';
    var $_apacheUser = 'www-data';
    var $_apacheGroup = 'www-data';
    var $_testDir;
    var $_args;
    public function setAkelosPath($path)
    {
        $this->_akelosPath = $path;
    }
    public function setArgs($args)
    {
        $this->_args = $args;
    }
    
    public function setTestDir($dir)
    {
        $this->_testDir = $dir;
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
        chdir($this->_testDir);
        $this->_execute('rm -Rf ' .$this->_testDir.'/test-results*.xml');
        //$this->_execute('chown -R ' .$this->_apacheUser.' ' .$this->_akelosPath.DS.$this->_testPath);
        $this->_execute('/usr/bin/env php  '.$this->_testDir.'/script/extras/xinc-ci_tests.php '.$this->_args,true);
    }
    
    private function _installTestApp()
    {
        !defined('DS')?define('DS',DIRECTORY_SEPARATOR):null;
        $this->_execute($this->_akelosPath.'/akelos -d '.$this->_testDir.' -deps --force',true);
        $this->_execute("touch ".$this->_testDir.DS.'log'.DS."testing.log", true);
        $this->_execute("chmod 777 ".$this->_testDir.DS.'log'.DS."testing.log", true);
        $this->_execute("cp -Rf ".$this->_akelosPath.DS.'test'."   ".$this->_testDir.DS, true);
        $this->_execute("chmod -Rf 777 ".$this->_testDir.DS.'tmp', true);
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
            $this->_execute('cp '.(isset($dest[1])?$dest[1]:'').' '.$this->_akelosPath.DIRECTORY_SEPARATOR.$file.' '.$this->_testDir.DIRECTORY_SEPARATOR.$dest[0].DIRECTORY_SEPARATOR.basename($file));
        }

    }
}


?>