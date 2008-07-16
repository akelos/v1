<?php

class AkRouterHelper
{

    private static $defined_functions = array();
    
    static function getDefinedFunctions()
    {
        return self::$defined_functions;    
    }
    
    static function generateHelperFunctionsFor($name,AkRoute $Route)
    {
        $names_array_as_string = var_export($Route->getNamesOfDynamicSegments(),true);
        $names_array_as_string = str_replace(array("\n","  "),'',$names_array_as_string);
        
        self::generateFunction($name,'url',$names_array_as_string);
        self::generateFunction($name,'path',$names_array_as_string,"'only_path'=>true");
    }
    
    private static function generateFunction($route_name,$function_suffix,$excluded_params_as_string,$additional_parameters='')
    {
        $function_name = $route_name.'_'.$function_suffix;
        if (function_exists($function_name)) return;

        $additional_parameters ? $additional_parameters .= ',' : null;
        
        $code = <<<BANNER
function $function_name(\$params=array())
{
    \$url_writer = AkUrlWriter::getInstance();
    \$my_params = array(
        'use_named_route'=>'$route_name',
        $additional_parameters
        'skip_old_parameters_except'=>$excluded_params_as_string
    );
    \$params = array_merge(\$my_params,\$params);
    return \$url_writer->urlFor(\$params);    
}

BANNER;
        #echo $code;
        eval($code);
        self::$defined_functions[] = $function_name;
        return $code;
    }
    
}

?>