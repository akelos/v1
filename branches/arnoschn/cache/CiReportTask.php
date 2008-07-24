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
            var_dump($files);
            
        }
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
            $this->setInFile($file);
            $this->log("Report available in:\n\n".$dir.DIRECTORY_SEPARATOR."phpunit2-noframes.html");
            parent::main();
        }
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