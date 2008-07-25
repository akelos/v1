<?php
require_once('phing/Task.php');
require_once('phing/tasks/ext/phpunit/PHPUnitReportTask.php');
class CiReportTask extends PHPUnitReportTask
{
    var $filesets = array();
    var $files = array();
    var $reportDir;
    
    public function setReportDir($dir)
    {
        $this->reportDir = $dir;
    }
    public function main()
    {
        $files = array();
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $newfiles = $ds->getIncludedFiles();
            foreach($newfiles as $f)
            {
                $files[]=$fs->getDir($this->project).DIRECTORY_SEPARATOR.$f;
            }
            
        }
        $environments = array();
        $summaryFile = $this->reportDir.DIRECTORY_SEPARATOR.'index.html';
        foreach($files as $file)
        {
            $fileName = basename($file);
            list($t,$r,$php,$backend)=split('-',$fileName);
            $backend=str_replace('.xml','',$backend);
            $dir=$this->reportDir.DIRECTORY_SEPARATOR.$php.DIRECTORY_SEPARATOR.$backend;
            $this->setToDir($dir);
            if (!is_dir($dir)) {
                mkdir($dir,0777,true);
            }
            $xml = new SimpleXMLElement(file_get_contents($file));
            $suites=$xml->xpath("/testsuites/testsuite");
            $tests=0;
            $failures=0;
            $errors=0;
            $time=0;
            
            foreach($suites as $suite){
                $attributes = $suite->attributes();
                $tests += (int)$attributes->tests;
                $failures += (int)$attributes->failures;
                $errors += (int)$attributes->errors;
                $time += (int)$attributes->time;
            }
            $environment = array();
            $environment['php']=$php;
            $environment['class']=$failures>0?'failure':$errors>0?'error':'';
            $environment['backend']=$backend;
            $environment['tests']=$tests;
            $environment['failures']=$failures;
            $environment['errors']=$errors;
            $environment['time']=$time;
            $environment['details']=$dir.DIRECTORY_SEPARATOR.'phpunit2-noframes.html';
            $environments[]=$environment;
            $this->setInFile($file);
            
            parent::main();
        }
        ob_start();
        include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'summary.php');
        $contents = ob_get_clean();
        
        file_put_contents($summaryFile, $contents);
        $this->log("Reports available in:\n\n".$summaryFile);
    }
    /**
     * Nested creator, creates a FileSet for this task
     *
     * @access  public
     * @return  object  The created fileset object
     */
    function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    public function getFiles($fileset, $includeEmpty = true) {

        if ($this->files === null) {

            $ds = $fileset->getDirectoryScanner($p);
            $this->files = $ds->getIncludedFiles();

            if ($includeEmpty) {

                // first any empty directories that will not be implicitly added by any of the files
                $implicitDirs = array();
                foreach($this->files as $file) {
                    $implicitDirs[] = dirname($file);
                }

                $incDirs = $ds->getIncludedDirectories();

                // we'll need to add to that list of implicit dirs any directories
                // that contain other *directories* (and not files), since otherwise
                // we get duplicate directories in the resulting tar
                foreach($incDirs as $dir) {
                    foreach($incDirs as $dircheck) {
                        if (!empty($dir) && $dir == dirname($dircheck)) {
                            $implicitDirs[] = $dir;
                        }
                    }
                }

                $implicitDirs = array_unique($implicitDirs);

                // Now add any empty dirs (dirs not covered by the implicit dirs)
                // to the files array.

                foreach($incDirs as $dir) { // we cannot simply use array_diff() since we want to disregard empty/. dirs
                    if ($dir != "" && $dir != "." && !in_array($dir, $implicitDirs)) {
                        // it's an empty dir, so we'll add it.
                        $this->files[] = $dir;
                    }
                }
            } // if $includeEmpty

        } // if ($this->files===null)

        return $this->files;
    }
}


?>