<?php

require_once(AK_LIB_DIR.DS.'AkActiveRecord.php');
require_once(AK_APP_DIR.DS.'shared_model.php');
require_once(AK_MODELS_DIR.DS.'source_analyzer.php');
require_once(AK_MODELS_DIR.DS.'source_parser.php');

$SourceAnalyzer = new SourceAnalyzer();
$SourceAnalyzer->storeFilesForIndexing();
$SourceAnalyzer->indexFiles();

?>