<?php

class AkRouterHelper
{

    function generateHelperFunctionsFor($name,AkRoute $Route)
    {
        $names_array_as_string = var_export($Route->getNamesOfDynamicSegments(),true);
        $names_array_as_string = str_replace(array("\n","  "),'',$names_array_as_string);
        self::generateUrlFunction($name,$names_array_as_string);
        self::generatePathFunction($name,$names_array_as_string);
    }
    
    private static function generateUrlFunction($name,$excluded_params_as_string)
    {
        $function_name = $name.'_url';
        if (function_exists($function_name)) return;
        $code = <<<BANNER
function $function_name(\$params=array())
{
    \$url_writer = AkUrlWriter::getInstance();
    \$my_params = array(
        'use_named_route'=>'$name',
        'skip_old_parameters_except'=>$excluded_params_as_string
    );
    \$params = array_merge(\$my_params,\$params);
    return \$url_writer->urlFor(\$params);    
}

BANNER;
        #echo $code;
        eval($code);
        return $code;
        
    }
    
    private static function generatePathFunction($name,$excluded_params_as_string)
    {
        $function_name = $name.'_path';
        if (function_exists($function_name)) return;
        $code = <<<BANNER
function $function_name(\$params=array())
{
    \$url_writer = AkUrlWriter::getInstance();
    \$my_params = array(
        'use_named_route'=>'$name',
        'only_path'=>true,
        'skip_old_parameters_except'=>$excluded_params_as_string
    );
    \$params = array_merge(\$my_params,\$params);
    return \$url_writer->urlFor(\$params);    
}

BANNER;
        #echo $code;
        eval($code);
        return $code;
    }
}

?>