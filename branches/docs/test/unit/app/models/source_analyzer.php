<?php

define('AK_ENABLE_AKELOS_ARGS', false);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');
require_once(AK_LIB_DIR.DS.'AkActiveRecord.php');
require_once(AK_APP_DIR.DS.'shared_model.php');
require_once(AK_MODELS_DIR.DS.'source_analyzer.php');
require_once(AK_MODELS_DIR.DS.'source_parser.php');

class SourceAnalyzerTest extends  UnitTestCase
{
    function test_setup()
    {
        require_once(AK_APP_DIR.DS.'installers'.DS.'api_installer.php');
        $installer = new ApiInstaller();
        $installer->uninstall();
        $installer->install();

    }
    
    function test_should_index_files()
    {
        $SourceAnalyzer = new SourceAnalyzer();
        $files = $SourceAnalyzer->getSourceFileDetails();

        $this->assertTrue(count($files) > 10);

        foreach ($files as $k => $v){
            $this->assertEqual($k, md5_file($SourceAnalyzer->base_dir.DS.$v));
        }
    }

    function test_should_store_files_in_the_database_without_duplicates()
    {
        $FileInstance =& new File();
        $this->assertFalse($FileInstance->find());

        $SourceAnalyzer = new SourceAnalyzer();
        $file_count = count($SourceAnalyzer->getSourceFileDetails());

        $SourceAnalyzer = new SourceAnalyzer();
        $SourceAnalyzer->storeFilesForIndexing();

        $this->assertEqual(count($FileInstance->find()), $file_count);

        $SourceAnalyzer = new SourceAnalyzer();
        $SourceAnalyzer->storeFilesForIndexing();

        $this->assertEqual(count($FileInstance->find()), $file_count);
    }


    function test_should_update_components()
    {
        $SourceAnalyzer = new SourceAnalyzer();
        $FileInstance =& new File();
        $this->assertTrue($Files =& $FileInstance->find());
        foreach (array_keys($Files) as $k){
            $details = $SourceAnalyzer->getFileDetails($Files[$k]->body);

            $this->assertFalse($Files[$k]->has_been_analyzed);
        }
        unset($Files);


        $SourceAnalyzer->indexFiles();

        $this->assertTrue($Files =& $FileInstance->find());

        $SourceAnalyzer = new SourceAnalyzer();
        $ComponentInstance =& new Component();

        foreach (array_keys($Files) as $k){
            $details = $SourceAnalyzer->getFileDetails($Files[$k]->body);

            $this->assertTrue($Files[$k]->has_been_analyzed);

            $this->assertTrue($Package =& $ComponentInstance->findFirstBy('name', $details['package']));
            $this->assertTrue($Subpackage =& $ComponentInstance->findFirstBy('name', $details['subpackage']));

            if(!empty($Package->parent_id)){
                $this->assertTrue(in_array($Subpackage->id, $Package->collect($Package->tree->getChildren(),'id','id')));
            }
        }
    }


    function __test_should_extract_javadoc_blocks()
    {

        $SourceAnalyzer = new SourceAnalyzer();
        include_once(AK_TEST_DIR.DS.'fixtures'.DS.'data'.DS.'parsed.php');

        $SourceAnalyzer->importCategories($parsed['categories']);

        unset($parsed['categories']);


        Ak::import('akelos_class');
        $ClassInstance =& new AkelosClass();
        foreach ($parsed as $class_name => $details){
            if(!$Class =& $ClassInstance->findFirstBy('name', $class_name)){
                echo "<pre>".print_r($details,true)."</pre>";
                $Class =& new AkelosClass(array('name'=>$class_name));
                $this->assertTrue($Class->save());
            }
        }
        //      print_r($parsed);

    }



    function _test_should_extract_javadoc_blocks()
    {
        $ar = file_get_contents(AK_LIB_DIR.DS.'AkActiveRecord.php');
        $SourceParser =& new SourceParser($ar);
        //print_r($SourceParser->parse());
        $SourceParser->parse();
        die();
    }

}


ak_test('SourceAnalyzerTest',true);

?>
