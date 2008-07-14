<?php
class B {
    function callme($param1)
    {
        return $param1;
    }
}
class A {
    function __construct()
    {
        $name = 'test';
        $excluded_params_as_string=true;
          $code = <<<BANNER
  function callme(\$params=array())
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
          callme(array());
    }
    
}

$a=new A();
//$val = $a->callme('test');