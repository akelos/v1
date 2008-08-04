<?php
require_once AK_APP_DIR.DS.'application_controller.php';

class TemplatePathsController extends AkActionController  
{

    function index()
    {
        
    }
    
    function my_layout_picker()
    {
        return 'picked_from_method';
    }
    
}

?>