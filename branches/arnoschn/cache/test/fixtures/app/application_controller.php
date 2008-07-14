<?php

require_once(AK_LIB_DIR.DS.'AkActionController.php');

class ApplicationController extends AkActionController 
{
    var $layout = false;
    var $perform_caching=true;
    var $cache_store = 'file';
}

?>
