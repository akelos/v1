<?php
require_once(AK_LIB_DIR.DS.'AkUnitTest.php');

class AkTestApplication extends AkUnitTest
{
    var $Dispatcher;
    var $_response;
    var $_cacheHeaders = array();
    
    function assertWantedText($text, $message = '%s')
    {
        $this->assertWantedPattern('/'.preg_quote($text).'/', $message);
    }

    /**
     * Asserts only if the whole response matches $text
     */
    function assertTextMatch($text, $message = '%s')
    {
        $this->assertWantedPattern('/^'.preg_quote($text).'$/', $message);
    }
    
    function assertText($text, $message = '%s') {
            return $this->assert(
                    new TextExpectation($text),
                    strip_tags($text),
                    $message);
    }
    function assertNoText($text, $message = '%s') {
        return $this->assert(
                new NoTextExpectation($text),
                strip_tags($text),
                $message);
    }
    function assertHeader($header, $content = null)
    {
        if (is_array($this->_cacheHeaders)) {
            foreach ($this->_cacheHeaders as $ch) {
                $parts = split(': ', $ch);
                if ($parts[0] == $header) {
                    if ($content != null) {
                        $this->assertEqual($content, $parts[1]);
                        return;
                    } else {
                        $this->assertTrue(true);
                        return;
                    }
                }
            }
        }
        if ($this->Dispatcher) {
            $value = $this->Dispatcher->Response->getHeader($header);
            $this->assertTrue($value!=false,'Header "'.$header.'" not found');
            if ($content != null) {
                $this->assertEqual($value, $content);
            }
        } else {
            $this->assertTrue(false,'Header "'.$header.'" not found');
        }
    }
    function &getController()
    {
        if (isset($this->Dispatcher)) {
            $controller = &$this->Dispatcher->Controller;
            return $controller;
        } else {
            $false = false;
            return $false;
        }
    }
    function _setConstants($constants = array())
    {
        foreach ($constants as $constant=>$value) {
            !defined($constant)?define($constant,$value):null;
        }
    }
    function setIp($ip)
    {
        $_SERVER['HTTP_CLIENT_IP'] = $ip;
        $_SERVER['REMOTE_ADDR'] = $ip;
    }
    
    function assertResponse($code)
    {
        $this->assertHeader('Status',$code);
    }

    function setForwaredForIp($ip)
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = $ip;
    }
    function addIfModifiedSince($gmtDateString)
    {
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $gmtDateString;
    }
    function setXmlHttpRequest()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH']='xmlhttprequest';
    }
    function &getHeader($name)
    {
        if ($this->Dispatcher) {
            $sentHeader = $this->Dispatcher->Response->getHeader($name);
        } else {
            $sentHeader=false;
        }
        if (!$sentHeader) {
            if (is_array($this->_cacheHeaders)) {
                foreach ($this->_cacheHeaders as $ch) {
                    $parts = split(': ', $ch);
                    if ($parts[0] == $name) {
                        $return=@$parts[1];
                        return $return;
                    }
                }
            }
        }
        return $sentHeader;
    }

    
    function _init($url, $constants = array())
    {
        
        $this->_setConstants($constants);
        $parts = parse_url($url);
        $_REQUEST['ak'] = isset($parts['path'])?$parts['path']:'/';
        $_SERVER['AK_HOST']= isset($parts['host'])?$parts['host']:'localhost';
        if (defined('AK_PAGE_CACHE_ENABLED') && AK_PAGE_CACHE_ENABLED) {
    
            require_once(AK_LIB_DIR . DS . 'AkActionController'.DS.'AkCacheHandler.php');
            $null = null;
            $pageCache = &Ak::singleton('AkCacheHandler',$null);
            
            $pageCache->init($null, 'file');
            $options = array('cacheDir'=>dirname(__FILE__).'/../../tmp/cache/',
                                   'use_if_modified_since'=>true,
                                   'headers'=>array('X-Cached-By: Akelos'));
            if (isset($_GET['allow_get'])) {
                $options['include_get_parameters'] = split(',',$_GET['allow_get']);
            }
            
            if (isset($_GET['use_if_modified_since'])) {
                $options['use_if_modified_since'] = true;
            }
            if ($cachedPage = $pageCache->getCachedPage()) {
                ob_start();
                $headers = $cachedPage->render(false,false,true);
                $this->_response = ob_get_clean();
                if (is_array($headers)) {
                    $this->_cacheHeaders = $headers;
                }
                return true;
            }
        }
        require_once('AkTestDispatcher.php');
        $this->Dispatcher =& new AkTestDispatcher();
    }
    function get($url,$constants = array())
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        $rendered = $this->_init($url, $constants);
        if (!$rendered) {
            $res = $this->Dispatcher->get($url);
            $this->_response = ob_get_clean();
        } else {
            $res=true;
        }
        return $res;
    }
    function post($url, $data = null, $constants = array())
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        ob_start();
        
        $rendered = $this->_init($url, $constants);
        if (!$rendered) {
            $res = $this->Dispatcher->post($url, $data);
            $this->_response = ob_get_clean();
        } else {
            $res=true;
        }
        return $res;
    }
    
    function put($url,$data = null, $constants = array())
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        ob_start();
        $rendered = $this->_init($url, $constants);
        if (!$rendered) {
            $res = $this->Dispatcher->put($url,$data);
            $this->_response = ob_get_clean();
        } else {
            $res = true;
        }
        return $res;
    }
    function assertWantedPattern($pattern, $message = '%s') {
        return $this->assertPattern($pattern, $message);
    }
    function assertPattern($pattern, $message = '%s') {
        return $this->assert(
                new PatternExpectation($pattern),
                $this->_response,
                $message);
    }
    function delete($url, $constants = array())
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        ob_start();
        $rendered = $this->_init($url, $constants);
        if (!$rendered) {
            $res = $this->Dispatcher->delete($url);
            $this->_response = ob_get_clean();
        } else {
            $res= true;
        }
        return $res;
    }
}