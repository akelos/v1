<?php

class RouterRegexesInvestigation extends PHPUnit_Regexes_TestCase 
{
    
    function testInvestigatesCompulsoryRegex()
    {
        # $this->connect('/:controller/:action',array('controller'=>'page','action'=>COMPULSORY));
        $pattern = '/^((\/)?([^\/]*\/{1}){1}([^\/]+){1}\/?){1}$/';
        $pattern = '/^(                    # generated pattern, against "\/blog\/"
                        (\/)?              # does not match 
                        ([^\/]*\/{1}){1}   # matches (falsely) "\/" 
                        ([^\/]+){1}\/?     # matches "blog\/"
                    ){1}$/x';

        $pattern = '/^(?:                  # begin, rewritten pattern
                        \/{1}              # first slash
                        ([^\/]*)\/         # implicit optional (with default)
                        ([^\/]+)\/         # explicit compulsory without requirements
                    ){1}$/x';              # end, eXtended-flag
        $url = '/blog/index/';
        $matches = $this->patternMatches($pattern,$url);
        #var_dump($res,$matches);
        $url = '/blog/';
        $this->patternDoesNotMatch($pattern,$url);
    }
    
    function testInvestigateSimpleTripleOptional()
    {
        $GLOBALS['__Kaste'] = true;
        # $this->connect('/:controller/:action/:id',array('controller'=>'page'));
        #unset($GLOBALS['__Kaste']);
        $pattern = '/^((\/)?([^\/]*\/?){1}([^\/]*\/?){1}([^\/]*\/?)?){1}$/';
        $pattern = '/^(
                        (\/)?              # first slash
                        ([^\/]*\/?){1}     # implicit optional
                        ([^\/]*\/?){1}     # implicit optional
                        ([^\/]*\/?)?       # implicit optional, last arg
                    ){1}$/x';

        $pattern = '/^(?:
                        \/{1}              # first slash
                        ([^\/]*)\/?     # implicit optional
                        ([^\/]*)\/?     # implicit optional
                        ([^\/]*)\/?       # implicit optional, last arg
                    )$/x';
        $matches = $this->patternMatches($pattern,'/blog/show/123/');
        $this->patternMatches($pattern,'/blog/show/');
        $this->patternMatches($pattern,'/blog/');
        $this->patternMatches($pattern,'/');
    }
    
    function testInvestigatesTripleOptionalWithRequirement()
    {
        # $this->connect('/:controller/:action/:id',array('controller'=>'page'),array('id'=>'/[0-9]+/'));
        $pattern = '/^((\/)?([^\/]*\/?){1}([^\/]*\/?){1}((([0-9]+){1})?\/?)?){1}$/';
        
        // enhancement
        $pattern = '/^(?:
                        \/
                        (?: ([^\/]*) \/)?   
                        (?: ([a-z]*) \/)?
                        (?: ([0-9]+)?\/)?   
                    ){1}$/x';
        $matches = $this->patternMatches($pattern,'/blog/show/123/');
        $matches = $this->patternMatches($pattern,'/blog/123/');
        #var_dump($matches);
        $this->patternMatches($pattern,'/blog/show/');
        $matches = $this->patternMatches($pattern,'/blog/');
        $matches = $this->patternMatches($pattern,'/');
    }
    
    function testInvestigatesCompulsoryWithRequirement()
    {
        # $this->connect('/:controller/:action/:id',array('controller'=>'page','id'=>COMPULSORY),array('id'=>'/[0-9]+/'));
        ## BUG
        $pattern = '/^((\/)?([^\/]*\/{1}){1}([^\/]*\/{1}){1}(([0-9]+){1}|(1){1}){1}\/?){1}$/';
        $pattern = '/^(
                        (\/)?
                        ([^\/]*\/{1}){1}                 # optionals silently interpreted as compulsories 
                        ([^\/]*\/{1}){1}
                        (([0-9]+){1}|(1){1}){1}\/?       # we have a or statement herein
                    ){1}$/x';
        
        #rewritten
        $pattern = '/^(
                        \/
                        ([^\/]*)\/
                        ([^\/]*)\/
                        ([0-9]+|index)\/
                    ){1}$/x';
        $matches = $this->patternMatches($pattern,'/blog/show/123/');
        #var_dump($matches);
        $this->patternMatches($pattern,'/blog/show/index/');
        $matches = $this->patternDoesNotMatch($pattern,'/123/');
        $this->patternDoesNotMatch($pattern,'/blog/show/herb/');
        $this->patternDoesNotMatch($pattern,'/blog/show/');
        $this->patternDoesNotMatch($pattern,'/blog/');
        $this->patternDoesNotMatch($pattern,'/');
        
        # $this->connect('/:controller/:action/:id',array('controller'=>'page','action'=>OPTIONAL,'id'=>COMPULSORY),array('id'=>'/[0-9]+/'));
        ## BUG
        $pattern = '/^((\/)?([^\/]*\/{1}){1}([^\/]*\/{1}){1}(([0-9]+){1}|(1){1}){1}\/?){1}$/';
        
        #rewritten, enhanced
        $pattern = '/^(?:
                        \/
                        ([^\/]*)\/         # silently => required
                        (?:                # explicit => optional
                            ([^\/]*)\/
                        )?          
                        ([0-9]+)\/         # explicit required
                    ){1}$/x';
        $matches = $this->patternMatches($pattern,'/blog/show/123/');
        #var_dump($matches);
        $matches = $this->patternMatches($pattern,'/blog/123/');
        #$this->patternMatches($pattern,'/blog/123/');

        # $this->connect('/:controller/:action/:id',array('controller'=>'page','action'=>COMPULSORY));
        ## BUG 
        $pattern = '/^((\/)?([^\/]*\/?){1}([^\/]+){1}\/{1}([^\/]*\/{1})?){1}$/';
        $pattern = '/^(?:
                        \/
                        ([^\/]*)\/      # implicit optional, compulsory following => actual silently required
                        ([^\/]+)\/      # compulsory
                        ([^\/]*)\/?     # optional, compulsory not following 
                    ){1}$/x';
        $matches = $this->patternMatches($pattern,'/blog/show/123/');
        $this->patternMatches($pattern,'/blog/show/');
        $this->patternDoesNotMatch($pattern,'/blog/');
        $this->patternDoesNotMatch($pattern,'/');
    }
    
    function testInvestigateArrayMatchers()
    {
        $GLOBALS['__Kaste'] = true;
        # $this->connect('/control/*options/',array('controller'=>'page'));
        $pattern = '/^((\/)?(control(?=(\/|$))){1}\/+([^\/]*\/?)+){1}$/'; 
        $pattern = '/^(?:
                        \/
                        (control)\/
                        ((?:[^\/]*\/?)+)\/
                    ){1}$/x'; 
        $this->given($pattern)->against('/control/this/and/that/')->matches('control','this/and/that');
        
        # $this->connect('/:controller/*options',array('controller'=>'page'));
        $pattern = '/^((\/)?([^\/]*\/?)+([^\/]*\/?)+){1}$/';
        $pattern = '/^(?:
                        \/
                        ([^\/]*)\/
                        ((?:[^\/]*\/?)+ )\/
                    ){1}$/x';
        
        $this->given($pattern)->against('/help/here/and/there/')->matches('help','here/and/there');

        # $this->connect('/*options/help',array('controller'=>'page'));
        #BUG
        $pattern = '/^((\/)?([^\/]*\/?){1}(help(?=(\/|$))){1}\/?){1}$/';
        $pattern = '/^(?:
                        \/
                        ((?:[^\/]*\/?)+) \/
                        (help)\/
                    ){1}$/x';
        $this->given($pattern)->against('/blue/help/')->matches('blue','help');
        $this->given($pattern)->against('/blue/green/help/')->matches('blue/green','help');

        $pattern = '/^(?:
                        \/
                        ((?:[^\/]*\/?)+) \/
                        ([^\/]*)\/
                    ){1}$/x';
        $this->given($pattern)->against('/blue/help/')->matches('blue','help');
        $this->given($pattern)->against('/blue/green/help/')->matches('blue/green','help');
    }
    
}

?>