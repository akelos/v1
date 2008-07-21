<?php

class CiTestsTask extends Task
{
    var $_akelosPath;
    var $_testPath;
    
    public function setAkelosPath($path)
    {
        $this->_akelosPath = $path;
    }
    
    public function setTestPath($path)
    {
        $this->_testPath = $path;
    }
    public function main()
    {
        $this->_installTestApp();
        $this->_copyFiles();
        $this->_runTests();
    }
    
    private function _runTests()
    {
        chdir($this->_testPath.DIRECTORY_SEPARATOR.'test');
        exec('/usr/bin/env php  '.$this->_testPath.'/script/extras/xinc-ci_tests.php');
    }
    
    private function _installTestApp()
    {
        passthru($this->_akelosPath.'/akelos -d '.$this->_testPath.' -deps --force');
    }
    private function _copyFiles()
    {
        $files = array('script/extras/xinc-ci_tests.php'=>'script/extras',
                       'script/extras/ci-config.yaml'=>'config',
                       'script/extras/caching.yml'=>'config',
                       'script/extras/sessions.yml'=>'config',
                       'script/extras/fix_htaccess.php'=>'config',
                       'script/extras/mysql-testing.php'=>'config',
                       'script/extras/postgres-testing.php'=>'config',
                       'script/extras/sqlite-testing.php'=>'config',
                       'script/extras/routes.php'=>'config',);
        
        foreach ($files as $file=>$dest) {
            copy($this->_akelosPath.DIRECTORY_SEPARATOR.$file,$this->_testPath.DIRECTORY_SEPARATOR.$dest.DIRECTORY_SEPARATOR.basename($file));
        }

    }
}


?>