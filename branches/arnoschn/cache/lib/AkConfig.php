<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// WARNING THIS CODE IS EXPERIMENTAL

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package ActiveSupport
 * @subpackage Experimental
 * @author Arno Schneider
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

class AkConfig
{
    function _generateCacheFileName($namespace, $environment = AK_ENVIRONMENT)
    {
        $namespace = Ak::sanitize_include($namespace, 'high');
        $cacheFile = AK_CONFIG_DIR.DS.'cache'.DS.$environment.DS.$namespace.'.php';
        return $cacheFile;
    }
    function _useReadCache($environment = AK_ENVIRONMENT)
    {
        switch ($environment) {
            case 'development':
            case 'testing':
                return false;
                break;
            default:
                return true;
            
        }
    }
    
    function _useWriteCache($environment = AK_ENVIRONMENT)
    {
        switch ($environment) {
            case 'development':
            case 'testing':
                return true;
                break;
            default:
                return true;
            
        }
    }
    
    function _checkCacheValidity($namespace,$environment)
    {
    	$cacheFilename = $this->_generateCacheFileName($namespace,$environment);
    	$configFilename = $this->_generateConfigFileName($namespace,$environment);

    	$cacheMtime = file_exists($cacheFilename) ? filemtime($cacheFilename): 1;
    	$configMtime = file_exists($cacheFilename) ? filemtime($configFilename) : 2;
    	return $cacheMtime == $configMtime;
    }
    
    function _setCacheValidity($namespace, $environment)
    {
    	$cacheFilename = $this->_generateCacheFileName($namespace,$environment);
    	$configFilename = $this->_generateConfigFileName($namespace,$environment);
    	touch($cacheFilename,filemtime($configFilename));
    }
    
    function _readCache($namespace, $environment = AK_ENVIRONMENT, $force = false)
    {
        if (!$force && !$this->_useReadCache($environment)) return false;
        $cacheFileName = $this->_generateCacheFileName($namespace,$environment);
        if ($this->_checkCacheValidity($namespace, $environment)) {
            $config = include $cacheFileName;
        } else {
            $config = false;
        }
        return $config;
    }
    function _writeCache($config, $namespace, $environment = AK_ENVIRONMENT, $force = false)
    {
        if (!$force && !$this->_useWriteCache($environment)) return false;
        
        $var_export = var_export($config,true);
        $cache = <<<CACHE
<?php
/**
 * Auto-generated config cache from $namespace in environment $environment
 */
\$config = $var_export;
return \$config;
?>
CACHE;
        $cacheFileName = $this->_generateCacheFileName($namespace,$environment);
        $cacheDir = dirname($cacheFileName);
        
        if (!file_exists($cacheDir)) {
            $oldumask = umask();
            umask(0);
            $res = @mkdir($cacheDir,0777,true);
            if (!$res) {
                trigger_error(Ak::t('Could not create config cache dir %dir',array('%dir'=>$cacheDir)),E_USER_ERROR);
            }
            umask($oldumask);
        }
        $fh = fopen($cacheFileName,'w+');
        if ($fh) {
            fputs($fh,$cache);
            fclose($fh);
            @chmod($cacheFileName,0777);
        } else {
            trigger_error(Ak::t('Could not create config cache file %file',array('%file'=>$cacheFileName)),E_USER_ERROR);
        }
        $this->_setCacheValidity($namespace,$environment);
    }
    function _generateConfigFileName($namespace,$environment = AK_ENVIRONMENT)
    {
        $namespace = Ak::sanitize_include($namespace, 'high');
        $yaml_file_name = AK_CONFIG_DIR.DS.$namespace.'.yml';
        return $yaml_file_name;
    }
    
    function _merge($default,$env)
    {
        if (is_array($default)) {
            foreach($default as $key=>$value) {
                if (!is_array($value)) {
                    $env[$key] = isset($env[$key])?$env[$key]:$value;
                } else {
                    $env[$key] = $this->_merge($value,isset($env[$key])?$env[$key]:array());
                }
            }
        } else {
            $env = empty($env)?$default:$env;
        }
        return $env;
    }
    
    function _readConfig($namespace, $environment = AK_ENVIRONMENT, $raise_error_if_config_file_not_found = true)
    {
        $yaml_file_name = $this->_generateConfigFileName($namespace, $environment);
        if (!is_file($yaml_file_name)){
            if($raise_error_if_config_file_not_found){
                die(Ak::t('Could not find %namespace settings file in %path.', array('%namespace'=>$namespace, '%path'=>$yaml_file_name))."\n");
            }
            return false;
        }
        require_once(AK_VENDOR_DIR.DS.'TextParsers'.DS.'spyc.php');
        $content = file_get_contents($yaml_file_name);
        $content = $this->_parseSettingsConstants($content);
        $config = Spyc::YAMLLoad($content);
        
        if (!is_array($config)) return false;
        
        $default = isset($config['default'])?$config['default']:array();
        
        
        $configs = array();
        
        unset($config['default']);
        $environments = array_keys($config);
        $default_environments = array('testing','development','production');
        $environments = array_merge($default_environments, $environments);
        foreach($environments as $env) {
            
            $envConfig = $this->_merge($default, isset($config[$env])?$config[$env]:array());
            $this->_writeCache($envConfig,$namespace,$env,$this->_useWriteCache($environment));
            $configs[$env] = $envConfig;
        }
        
        return isset($configs[$environment])?$configs[$environment]:$default;
        
    }
    
    function _parseSettingsConstants($settingsStr)
    {
        return preg_replace_callback('/\$\{(AK_.*?)\}/',array('AkConfig','_getConstant'),$settingsStr);
    }
    
    function _getConstant($name)
    {
        return defined($name[1])?constant($name[1]):'';
    }
    
    function &get($namespace, $environment = AK_ENVIRONMENT, $raise_error_if_config_file_not_found = true, $uncached = false)
    {
        static $configs = array();
        if (!$uncached && isset($_configs[$namespace]) && isset($_configs[$namespace][$environment])) {
            return $_configs[$namespace][$environment];
        }
        if ($uncached || !($config = $this->_readCache($namespace, $environment))) {
            $config = $this->_readConfig($namespace, $environment,$raise_error_if_config_file_not_found);
        }
        if (!isset($_configs[$namespace])) {
            $_configs[$namespace] = array($environment=>$config);
        } else {
            $_configs[$namespace][$environment] = $config;
        }
        return $_configs[$namespace][$environment];
    }
}
?>