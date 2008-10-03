<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+



/**
 * @package ActionController
 * @subpackage Caching
 * @author Arno Schneider
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

require_once(AK_LIB_DIR.DS.'AkCache.php');

/**
 *
 * 
 * Akelos supports three types of caching:
 *
 * - Page Caching
 * - Action Caching
 * - Fragment Caching
 *
 *
 * == Page Caching
 *
 * Page caching is an approach to caching where the entire action output of
 * is stored as in a special format containing the output and the headers
 * which have to be sent for the response.
 * This cache the web server can serve without even starting up the Framework.
 *
 * This can be as much as 100 times faster than going through the process of dynamically
 * generating the content.
 * Unfortunately, this incredible speed-up is only available to stateless pages
 * where all visitors are treated the same.
 *
 * Content management systems -- including weblogs and wikis -- have many pages
 * that are a great fit for this approach, but account-based systems
 * where people log in and manipulate their own data are often less likely candidates.
 *
 * Specifying which actions to cache is done through the <tt>caches</tt> class method:
 *
 *   class WeblogController extends ActionController {
 *     var $caches_page = array('show','new');
 *     function show() {
 *        ....
 *     }
 *
 *     function new() {
 *        ....
 *     }
 *   }
 *
 * This will generate caches such as weblog/show/5 and weblog/new, which match the URLs
 * used to trigger the dynamic generation.
 * This is how the web server is able pick up a cache when it exists by just purely reading
 * the Requested URL. Otherwise it lets the request pass on to the Framework to generate it.
 *
 * Expiration of the cache is handled by deleting the cache, which results in a lazy regeneration
 * approach where the cache is not restored before another hit is made against it.
 *
 * The API for doing so mimics the options from url_for and friends:
 *
 *   class WeblogController extends ActionController {
 *     function update() {
 ....
 *       $this->expirePage(array("action" => "show", "id" => $this->params["list"]["id"]));
 *       $this->redirectTo(array("action => "show", "id" => $this->params["list"]["id"]));
 *     }
 *   }
 *
 * Additionally, you can expire caches using Sweepers that act on changes in the model to determine
 * when a cache is supposed to be expired.
 *
 * == Configuring the cache
 *
 * There are different types of caches:
 *
 * - File based cache using PEAR Cache_Lit
 * - Database Cache using AdoDB
 * - Memcache
 *
 * The config file for the cache can be found in BASE_DIR/config/caching.yml
 *
 * === File Based Cache
 *
 * ==== Setting the cache directory
 *
 * If no cache directory is set, the Cache_Lite will use the AK_TMP_DIR constant value for the
 * cache dir. The cache dir can be set inside the Yaml-Config file as follows:
 *
 * enabled: true
 * handler:
 *   type: 1 #1=PEAR,2=AdoDB,3=Memcache
 *   options:
 *           cacheDir: /tmp
 *
 * === Database Cache
 *
 * The DB cache will use the configured database settings and use the cache table to store caches.
 *
 * === Memcache
 *
 * The MemCache needs to know which memcache servers to talk to. You have to configure a set
 * of servers inside the caching.yml:
 *
 * enabled: true
 * handler:
 *   type: 3 #1=PEAR,2=AdoDB,3=Memcache
 *   options:
 *           servers:
 *                    - memcache1.example.com:9810
 *                    - memcache2.example.com:9810
 *
 * == Action Caching
 *
 * Action caching is similar to page caching by the fact that the entire output
 * of the response is cached, but unlike page caching,
 * every request still goes through the AkActionController.
 *
 * The key benefit of this is that filters are run before the cache is served,
 * which allows for authentication and other restrictions on whether someone is
 * allowed to see the cache.
 *
 * Example:
 *
 *   class ListsController extends ApplicationController {
 *
 *     var $caches_page   = 'public';
 *     var $caches_action = array('show', 'feed');
 *
 *     function __construct(){
 *         $this->beforeFilter(array('authenticate' => array('except' => array('public'))));
 *     }
 *   }
 *
 * In this example, the public action doesn't require authentication, so it's
 * possible to use the faster page caching method.
 *
 * But both the show and feed action are to be shielded behind the authenticate filter,
 * so we need to implement those as action caches.
 *
 * Action caching internally uses the fragment caching and an before/after filter combination
 * to do the job.
 *
 * The fragment cache is named according to both
 * the current host and the path as well as the locale.
 *
 * So a page that is accessed at http://david.somewhere.com/lists/show/1 with an english locale
 * will result in a fragment named
 *
 * cacheId: "en/lists/show/1", cacheGroup: "david.somewhere.com"
 *
 * This allows the cacher to differentiate between "david.somewhere.com/lists/" and
 * "jamis.somewhere.com/lists/" -- which is a helpful way of assisting the
 * subdomain-as-account-key pattern.
 *
 * Different representations of the same resource,
 * e.g. <tt>http://david.somewhere.com/lists</tt> and
 * <tt>http://david.somewhere.com/lists.xml</tt>
 * are treated like separate requests and so are cached separately.
 *
 * Keep in mind when expiring an action cache that
 *
 * <tt>array('action' => 'lists')</tt> is not the same
 * as <tt>array('action' => 'lists', 'format' => 'xml')</tt>.
 *
 * If you use the Filebased Cache, you can set modify the default action cache path
 * by passing a "cache_path" option.
 *
 * This will be passed directly to _setCachesAction method.
 * This is handy for actions with multiple possible routes that should be cached differently.
 *
 * If a block is given, it is called with the current controller instance.
 *
 *   class ListsController extends ApplicationController {
 *
 *     var $caches_page   = 'public';
 *     var $caches_action = array('show'=>array('cache_path'=>'/custom/show/'), 'feed');
 *
 *     function __construct(){
 *         $this->beforeFilter(array('authenticate' => array('except' => array('public'))));
 *     }
 *   }
 *
 * The action cache for the action show will then be stored under the cacheId "en/custom/show".
 *
 * == Fragment Caching
 *
 * Fragment caching is used for caching various blocks within templates without caching the entire action
 * as a whole.
 * This is useful when certain elements of an action change frequently or depend on complicated state
 * while other parts rarely change or can be shared amongst multiple parties.
 * 
 * The caching is doing using the cache helper available in the Action View.
 * A template with caching might look something like:
 *
 *   <b>Hello {name}</b>
 *   <?php if (!$this->cache_helper->begin()) { ?>
 *     All the topics in the system:
 *     <?php $this->renderPartial("topic", $Topic->findAll()); ?>
 *   <?php $this->cache_helper->end();} ?>
 *
 * This cache by default will bind to the action it was called from,
 * so if this code was part of the view for the topics/list action, you would
 * be able to invalidate it using:
 * 
 *    <tt>$this>expireFragment(array("controller" => "topics", "action" => "list"))</tt>.
 *
 * This default behavior is of limited use if you need to cache multiple fragments per action
 * or if the action itself is cached using <tt>caches_action</tt>,
 * so we also have the option to qualify the name of the cached fragment with something like:
 *
 *   <?php if (!$this->cache_helper->begin('cacheFragmentKey')) { ?>
 *
 * Like this you can assure unique fragment caches.
 *
 * The expiration call for this example is:
 *
 *   $this->expireFragment("cacheFragmentKey")
 * 
 */
class AkCacheHandler extends AkObject
{
    var $cache_strip = '<!--CACHE-SKIP-START-->.*?<!--CACHE-SKIP-END-->';
    
    /**
     * @var AkCache
     */
    var $_cache_store = false;

    var $_perform_caching = true;

    var $_page_cache_extension = '.html';

    var $_controller;

    /**
     * ########### Start: Page Caching ###########
     */

    var $_lastCacheGroup;

    var $_lastCacheId;

    var $_include_get_parameters = array();

    var $_caches_page = array();
    var $_caches_action = array();

    var $_additional_headers = array();

    var $_header_separator = '@#@';

    /**
     * ########### End: Page Caching ###########
     */

    /**
     * Max key size on memcache is 250 chars,
     * to support memcache, we need to md5() the keysize in case it becomes too long
     *
     * @var int
     */
    var $_max_cache_id_length = 240;
    /*
     * @var int
     */
    var $_max_url_length = 120;


    /**
     * Sweeper
     */
    var $observe = array();

    var $_Sweepers = array();

    var $_settings = array();
    var $_rendered_action_cache = false;

    var $_caching_type = null;

    /**
     * Reads configuration options from AkActionController and the configured
     * constants
     *
     * AkCache::lookupStore(true) - to detect which cache shall be used
     * $perform_caching - to detect whether caching shall be enabled or not
     *
     * @param AkActionController $parent
     */
    function init(&$parent, $settings = null)
    {
        $this->_caching_type = null;
        $this->_action_cache_path = null;
        $this->_action_cache_host = null;
        if ($parent != null) {
            $this->_controller = &$parent;

            $this->_configure($settings);

        } else {
            /**
             * We are in pagecache rendering mode
             */
            $this->_loadSettings($settings);

        }
    }
    function _getPublicLocales()
    {
        static $public_locales;
        if(empty($public_locales)){
            $public_locales = defined('AK_PUBLIC_LOCALES') ?
            Ak::toArray(AK_PUBLIC_LOCALES) :
            array_keys($this->_getAvailableLocales());
        }
        return $public_locales;
    }
    
    function _getAvailableLocales()
    {
        static $available_locales;

        if(empty($available_locales)){
            $available_locales = array();
            $d = dir(AK_CONFIG_DIR.DS.'locales');
            while (false !== ($entry = $d->read())) {
                if (preg_match('/\\.php$/', $entry)){
                    $locale = str_replace('.php','',$entry);
                    $available_locales[$locale] = array($locale);
                }
            }
            $d->close();
        }

        return $available_locales;
    }
    function _startSession()
    {
        if(isset($_COOKIE[AK_SESSION_NAME])&& !isset($_SESSION)){
            require_once(AK_LIB_DIR.DS.'AkSession.php');
            $SessionHandler = &AkSession::initHandler();
            @session_start();
        }
    }
    
    function _getDefaultLanguageForUser()
    {
        $this->_startSession();
        if (isset($_SESSION['lang'])) {
            return $_SESSION['lang'];
        } else {
            $langs = $this->_getPublicLocales();
            $browser_languages = $this->_getBrowserLanguages();
            // First run for full locale (en_us, en_uk)
            foreach ($browser_languages as $browser_language){
                if(in_array($browser_language,$langs)){
                    return $browser_language;
                }
            }
    
            // Second run for only language code (en, es)
            foreach ($browser_languages as $browser_language){
                if($pos = strpos($browser_language,'_')){
                    $browser_language = substr($browser_language,0, $pos);
                    if(in_array($browser_language,$langs)){
                        return $browser_language;
                    }
                }
            }
            return Ak::lang();
        }
    }
    function _getDefaultLocale()
    {
        return Ak::lang();
    }
    function _getBrowserLanguages()
    {
        $browser_accepted_languages = str_replace('-','_', strtolower(preg_replace('/q=[0-9\.]+,*/','',@$_SERVER['HTTP_ACCEPT_LANGUAGE'])));
        $browser_languages = (array_diff(split(';|,',$browser_accepted_languages.','), array('')));
        if(empty($browser_languages)){
            return array($this->_getDefaultLocale());
        }
        return array_unique($browser_languages);
    }
    
    function _loadSettings($settings = null)
    {
        if ($settings == null) {
            $this->_settings = Ak::getSettings('caching', false);
        } else if (is_array($settings)) {
            $this->_settings = $settings;
        } else {
            return;
        }
        $this->_setCacheStore($this->_settings);
    }
    function _configure($settings)
    {
        $configuration_object = &$this->_controller;
        $configuration_options = array('caches_page'=>'_setCachesPage',
                                       'cachesPage'=>'_setCachesPage',
                                       'caches_action'=>'_setCachesAction',
                                       'cachesAction'=>'_setCachesAction',
                                       'cache_sweeper'=>'_setCacheSweeper',
                                       'cacheSweeper'=>'_setCacheSweeper',
                                       'page_cache_extension'=>'_setPageCacheExtension');
        /**
         * Load the configured cache store,
         */
        $this->_loadSettings($settings);

        if (isset($this->_controller->page_cache_extension)) {
            $this->_page_cache_extension = $this->_controller->page_cache_extension;
        }

        if (@$this->_settings['enabled'] == true) {
            $this->_perform_caching = true;
        }

        foreach ($configuration_options as $option => $callback) {
            if (isset($configuration_object->$option)) {
                if (is_array($callback)) {
                    call_user_func_array($callback,$configuration_object->$option);
                } else {
                    $this->$callback($configuration_object->$option);
                }
            }
        }
    }
    function _setPageCacheExtension($extension)
    {
        $this->_page_cache_extension = $extension;
    }
    function &getController()
    {
        $return=&$this->_controller;
        return $return;
    }

    function &getCacheStore()
    {
        return $this->_cache_store;
    }

    /**
     * ########################################################################
     * #
     * #               The following methods have to be callable
     * #               from AkActionController
     * #
     * ########################################################################
     */
    /**
     * Is the Caching module configured and ready for usage?
     *
     * @return boolean
     */
    function cacheConfigured()
    {
        return $this->_cache_store && $this->_perform_caching;
    }
    /*
     * ########################################################################
     * #
     * #               From AkActionControllerCachingPages
     * #
     * ########################################################################
     */
    function expirePage($path = null, $language=null)
    {
        if (!$this->_perform_caching || !$this->_cache_store) return;
        
        if ($language == null && is_array($path) && !isset($path['lang'])) {
            $language = '*';
        }
        
        if ((is_array($path) && isset($path['lang']) && $path['lang'] == '*') || $language == '*') {
            $langs = $this->_getPublicLocales();
            $res = true;
            $mpath = $path;
            unset($mpath['lang']);
            foreach ($langs as $lang) {
                $res = $this->expirePage($mpath, $lang) || $res;
            }
            return $res;
        }
        $cacheId = $this->_buildCacheId($path, $language);
        $notNormalizedCacheId = $this->_buildCacheId($path, $language, false);
        $cacheGroup = $this->_buildCacheGroup();
        $notGzippedRes=$this->_cache_store->remove($cacheId,$cacheGroup);
        $gZippedCacheId = $this->_scopeWithGzip($cacheId);
        $gzippedRes=$this->_cache_store->remove($gZippedCacheId,$cacheGroup);

        if ($notNormalizedCacheId != $cacheId) {
            $notNormalizedNotGzippedRes=$this->_cache_store->remove($notNormalizedCacheId,$cacheGroup);
            $notNormalizedGZippedCacheId = $this->_scopeWithGzip($notNormalizedCacheId);
            $notNormalizedGzippedRes=$this->_cache_store->remove($notNormalizedGZippedCacheId,$cacheGroup);
        }


        return ($notGzippedRes || $gzippedRes);
    }
    function cachePage($content, $path = null, $language = null, $gzipped=false, $sendETag = false, $orgStrlen = null)
    {
        static $ETag;
        
        if (!($this->_cachingAllowed() && $this->_perform_caching)) return;

        $cacheId = $this->_buildCacheId($path, $language);
        $skipEtagSending = false;
        if ($orgStrlen != strlen($content)) $skipEtagSending = true;
        $notNormalizedCacheId = $this->_buildCacheId($path, $language,false);
        

        $removeHeaders = array();
        $addHeaders = array();
        if ($gzipped) {
            $cacheId = $this->_scopeWithGzip($cacheId);
            $notNormalizedCacheId = $this->_scopeWithGzip($notNormalizedCacheId);
            $addHeaders = array('Content-Encoding'=>'gzip');
        } else {
            $removeHeaders = array('content-encoding');
        }

        $cacheGroup = $this->_buildCacheGroup();

        if ($sendETag && !headers_sent()) {
            $ETag = Ak::uuid();
            $etagHeader = 'ETag: '.$ETag;
            $this->_controller->Response->addSentHeader($etagHeader);
            if(!$skipEtagSending) {
                header($etagHeader);
            }
        }
        //$addHeaders['ETag'] = $ETag;



        $content = $this->_modifyCacheContent($content,$addHeaders, $removeHeaders);
        $res = $this->_cache_store->save($content,$cacheId,$cacheGroup);
        if ($notNormalizedCacheId != $cacheId) {
            // Store the not normalized cacheid
            $this->_cache_store->save($content,$notNormalizedCacheId,$cacheGroup);
        }
        return $res;

    }
    
    function _stripCacheSkipSections($content)
    {
        if (isset($this->_controller->cache_strip)) {
            $cache_strip = is_array($this->_controller->cache_strip)?$this->_controller->cache_strip:array($this->_controller->cache_strip);
        } else {
            $cache_strip = array();
        }
        $cache_strip = array_merge(array($this->cache_strip), $cache_strip);

        foreach ($cache_strip as $strip) {
            $content = @preg_replace('/('.$strip.')/sm','',$content);
            if ($content===false) {
                trigger_error(Ak::t('AkCacheHandler: cache_strip expression: %expr is not working as expected', array('%expr'=>$strip)), E_USER_ERROR);
                return false;
            }
        }
         return $content;
    }
    
    function _modifyCacheContent($content,$addHeaders = array(), $removeHeaders = array())
    {
        $headers = $this->_controller->Response->_headers_sent;
        $finalHeaders = array();
        foreach ($headers as $header) {
            $parts = split(': ',$header);
            $type = $parts[0];
            if (!in_array(strtolower($type),$removeHeaders)) {
                if (isset($addHeaders[$type])) {
                    $finalHeaders[] = $type.($addHeaders[$type]!==true?': '.$addHeaders[$type]:'');
                    unset($addHeaders[$type]);
                } else {
                    $finalHeaders[] = $header;
                }
            }
        }
        foreach ($addHeaders as $type=>$val) {
            $finalHeaders[] = $type.($val!==true?': '.$val:'');
        }
        $timestamp = time();
        $headerString = serialize($finalHeaders);
        $content = $timestamp.$this->_header_separator.$headerString . $this->_header_separator . $content;
        return $content;
    }

    function _setCacheSweeper($options)
    {
        $default_options = array('only'=>array(),
                                 'except'=>array());
        if (is_string($options)) {
            $options = Ak::toArray($options);
        }
        Ak::parseOptions($options, $default_options,array(),true);

        foreach ($options as $sweeper => $params) {
            if (is_int($sweeper)) {
                $sweeper = $params;
                $params = array();
            }
            $this->_initSweeper($sweeper, $params);
        }
    }

    function _initSweeper($sweeper, $options = array())
    {
        if (!empty($only) && !in_array($this->_controller->getActionName(), $options['only'])) return;
        if (!empty($except) && !in_array($this->_controller->getActionName(), $options['except'])) return;

        $sweeper_class = AkInflector::classify($sweeper);

        if (!class_exists($sweeper_class)) {
            $filePath = AK_APP_DIR . DS . 'sweepers' . DS . $sweeper.'.php';
            if (file_exists($filePath)) {
                require_once($filePath);
                if (!class_exists($sweeper_class)) {
                    trigger_error('Cache Sweeper "' . $sweeper_class . '" does not exist in: ' . $filePath, E_USER_ERROR);
                }
            } else if (AK_ENVIRONMENT == 'development') {
                trigger_error('Cache Sweeper file does not exist: ' . $filePath, E_USER_ERROR);
            }
        }
        $this->_Sweepers[] = &new $sweeper_class(&$this);
    }

    function _setCachesPage($options)
    {
        if (!$this->_perform_caching) return;
        if (is_string($options)) {
            $options = Ak::toArray($options);
        }
        $default_options = array('include_get_parameters'=>array(),
                                 'headers'=> array('X-Cached-By'=>'Akelos'));
        Ak::parseOptions($options, $default_options,array(),true);
        $this->_caches_page = &$options;

        $actionName = $this->_controller->getActionName();
        if ($this->_caching_type == null && isset($this->_caches_page[$actionName])) {
            $this->_caching_type = 'page';
            $this->_include_get_parameters = $this->_caches_page[$actionName]['include_get_parameters'];
            $this->_additional_headers = $this->_caches_page[$actionName]['headers'];

            $this->_controller->prependBeforeFilter(array(&$this,'beforePageCache'));
            $this->_controller->appendAfterFilter(array(&$this,'afterPageCache'));
        }
    }

    function beforePageCache()
    {
        ob_start();
        return true;
    }

    function _scopeWithGzip($cacheId)
    {
        $cacheId = 'gzip' . DS . $cacheId;
        return $cacheId;
    }
    function afterPageCache()
    {
        $encodings = $this->_getAcceptedEncodings();
        $xgzip = false;
        $gzip = false;
        $this->_controller->Response->addHeader('Cache-Control','private, max-age=0, must-revalidate');

        if (($gzip=in_array('gzip',$encodings)) || ($xgzip=in_array('x-gzip',$encodings))) {
            $this->_controller->Response->addHeader('Content-Encoding',$xgzip?'x-gzip':'gzip');
            $gzip = $gzip || $xgzip;
            $this->_controller->handleResponse();
            $contents = ob_get_clean();
            /**
             *  Caching unzipped content
             */
            $this->cachePage($this->_stripCacheSkipSections($contents),array(),null,false,true, strlen($contents));
            $contents = $this->_gzipCache($contents);
            echo $contents;
        } else {
            $this->_controller->handleResponse();
            $contents = ob_get_clean();
            /**
             *  Caching gzipped content
             */
            $gzippedContents = $this->_gzipCache($this->_stripCacheSkipSections($contents));
            $this->cachePage($gzippedContents,array(),null,true,true, strlen($contents));
            echo $contents;
        }
        $this->cachePage($this->_stripCacheSkipSections($contents),array(),null,$gzip, false, strlen($contents));
        return true;
    }

    function _gzipCache($cache)
    {
        $pre ="\x1f\x8b\x08\x00\x00\x00\x00\x00";
        $gzip_size = strlen($cache);
        $gzip_crc = crc32($cache);
        $gzip_contents = gzcompress($cache, 9);
        $gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);
        $gzip_contents = $pre.$gzip_contents;
        $gzip_contents.=pack('V', $gzip_crc);
        $gzip_contents.=pack('V', $gzip_size);
        return $gzip_contents;
    }

    function _buildCacheId($path, $forcedLanguage = null, $normalize = true)
    {
        if ($path === null) {
            $path = @$_REQUEST['ak'];

        } else if (is_array($path)) {
            unset($path['lang']);
            $path = $this->_pathFor($path, $normalize);
        } else if (is_string($path)) {
            ;
        }
        $path = ltrim($path,'/');
        if (preg_match('|^[a-z]{2,2}/.*$|', $path)) {
            $parts = split('/',$path);
            $forcedLanguage = array_shift($parts);
            $path = implode('/',$parts);
        }
        $cacheId = preg_replace('|'.DS.'+|','/',$path);
        $cacheId = rtrim($cacheId,'/');
        $parts = split('/',$cacheId);
        $hasExtension = preg_match('/.+\..{3,4}/',$parts[count($parts)-1]);
        if (!$hasExtension) {
            $cacheId.= $this->_page_cache_extension;
        }
        
        $getParameters = $_GET;
        unset($getParameters['ak']);
        if (is_array($this->_include_get_parameters) && !empty($this->_include_get_parameters) && !empty($getParameters)) {
            $cacheableGetParameters = array();
            foreach ($this->_include_get_parameters as $include_get) {
                if (isset($getParameters[$include_get])) {
                    $cacheableGetParameters[] = $include_get .DS.$getParameters[$include_get];
                }
            }
            $cacheIdGetPart = implode(DS,$cacheableGetParameters);
            $cacheId .= DS . $cacheIdGetPart;
        }
        $cacheId=strlen($cacheId)>$this->_max_url_length?md5($cacheId):$cacheId;
        $cacheId = ($forcedLanguage!=null?$forcedLanguage:$this->_getDefaultLanguageForUser()).DS. $cacheId;
        //var_dump($cacheId);
        $this->_lastCacheId = preg_replace('|'.DS.'+|','/',$cacheId);
        return $this->_lastCacheId;
    }
    function _getAcceptedEncodings()
    {
        $encodings = isset($_SERVER['HTTP_ACCEPT_ENCODING'])?$_SERVER['HTTP_ACCEPT_ENCODING']:'';
        $encodings = preg_split('/\s*,\s*/',$encodings);
        return $encodings;
    }
    function &getCachedPage($path = null,$forcedLanguage = null)
    {
        $false = false;
        if (!$this->_cachingAllowed()) return $false;
        $false = false;
        if ($this->_cache_store!=false) {
            if ($path === null) {
                $path = @$_REQUEST['ak'];
            }
            $cacheId = $this->_buildCacheId($path, $forcedLanguage);
            $encodings = $this->_getAcceptedEncodings();
            if (($gzip=in_array('gzip',$encodings)) || ($xgzip=in_array('x-gzip',$encodings))) {
                $cacheId = $this->_scopeWithGzip($cacheId);
            }
            $cacheGroup = $this->_buildCacheGroup();
            $cache = $this->_cache_store->get($cacheId, $cacheGroup);
            if ($cache != false) {
                require_once(AK_LIB_DIR.DS.'AkCache'.DS.'AkCachedPage.php');
                $page = &new AkCachedPage($cache, $this->_header_separator, array('use_if_modified_since'=>true,
                                                                                'headers'=>array('X-Cached-By: Akelos')));
                return $page;
            } else {

                return $false;
            }
        } else {
            return $false;
        }
    }

    function _buildCacheGroup()
    {
        $this->_lastCacheGroup = $this->_convertGroup(isset($_SERVER['AK_HOST'])?$_SERVER['AK_HOST']:AK_HOST);
        return $this->_lastCacheGroup;
    }

    function _cachingAllowed()
    {
        if (isset($this->_controller)) {
            return $this->_controller->Request->isGet() && $this->_controller->Response->getStatus()==200;
        } else {
            return empty($_POST) && empty($_ENV['HTTP_RAW_POST_DATA']) && (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD'])=='get');
        }
    }

    function _convertGroup($group)
    {
        if ($group == '127.0.0.1') return 'localhost';
        else return $group;
    }
    /*
     * ########################################################################
     * #
     * #               From AkActionControllerCachingFragments
     * #
     * ########################################################################
     */
    function fragmentCacheKey($options, $parameters = array())
    {
        if (isset($parameters['namespace']) && $parameters['namespace'] == 'actions') {
            $options = $this->_actionPath($options);
        } else {
            if (is_array($options)) {
                $options = $this->_pathFor($options);
            } else if ($options==null) {
                $options = $this->_pathFor($this->_controller->params);
            }
        }
        
        $key = AkCache::expandCacheKey($options, isset($parameters['namespace'])?$parameters['namespace']:'fragments');

        return $key;
    }
    function _cacheTplRendered($key)
    {
        static $_cached;
        if (empty($_cached)) {
            $_cached = array();
        }
        if (isset($_cached[$key])) {
            return true;
        } else {
            $_cached[$key] = true;
            return false;
        }

    }
    function cacheTplFragmentStart($key = array(), $options = array())
    {
        if (!$this->cacheConfigured()) {
            return false;
        }
        $read = $this->readFragment($key, $options);
        if ($read !== false) {
            echo $read;
            $this->_cacheTplRendered($key);
            return true;
        } else {
            ob_start();
            return false;
        }
    }

    function cacheTplFragmentEnd($key = array(), $options = array())
    {
        if (!$this->_cacheTplRendered($key)) {
            $contents = ob_get_clean();
            $this->writeFragment($key, $contents, $options);
        }
    }

    function writeFragment($key, $content, $options = array())
    {
        if (!$this->cacheConfigured()) return;
        $key = $this->fragmentCachekey($key, $options);

        return $this->_cache_store->save($content, $key, isset($options['host'])?
        $options['host']:$this->_buildCacheGroup());
    }

    function readFragment($key, $options = array())
    {
        if (!$this->cacheConfigured()) return false;

        $key = $this->fragmentCachekey($key, $options);

        return $this->_cache_store->get($key, isset($options['host'])?
        $options['host']:$this->_buildCacheGroup());
    }

    function expireFragment($key, $options = array())
    {
        if (!$this->cacheConfigured()) return;
        
        if (is_array($key) && isset($key['lang']) && $key['lang'] == '*') {
            $langs = AkLocaleManager::getPublicLocales();
            $res = true;
            foreach ($langs as $lang) {
                $key['lang'] = $lang;
                $res = $this->expireFragment($key, $options);
            }
            return $res;
        }
        $key = $this->fragmentCachekey($key, $options);

        return $this->_cache_store->remove($key, isset($options['host'])?
        $options['host']:$this->_buildCacheGroup());
    }
    /*
     * ########################################################################
     * #
     * #               From AkActionControllerCachingActions
     * #
     * ########################################################################
     */
    function beforeActionCache()
    {
        if (!empty($this->_action_include_get_parameters)) {
            $getParameters = array();
            foreach ($this->_action_include_get_parameters as $includeGet) {
                if (isset($_GET[$includeGet])) {
                    $getParameters[] = $includeGet.'='.$_GET[$includeGet];
                }
            }
            $getString = implode(DS,$getParameters);
        } else {
            $getString = '';
        }
        if (empty($this->_action_cache_path)) {
            $path = $this->_pathFor().(!empty($getString)?DS.$getString:'');
            $this->_action_cache_path = $path;
        }
        $options = array();
        if (!empty($this->_action_cache_host)) {
            $options['host'] = $this->_action_cache_host;
        }
        $options['namespace'] = 'actions';
        if (($content = $this->readFragment($this->_action_cache_path, $options))!==false) {
            $this->_controller->renderText($content);
            $this->_rendered_action_cache = true;
            $this->_controller->performed_render = true;
            $format = $this->_controller->Request->getFormat();
            $this->_controller->Response->addHeader('X-Cached-By','Akelos-Action-Cache');
            $this->_controller->Response->setContentTypeForFormat($format);
        } else {
            ob_start();
            $this->_rendered_action_cache = false;
        }
        return true;
    }

    function afterActionCache()
    {
        if (!$this->_cachingAllowed() || $this->_rendered_action_cache === true) return;

        $this->_controller->handleResponse();
        $contents = ob_get_flush();
        $contents = $this->_stripCacheSkipSections($contents);
        $options = array();
        if (!empty($this->_action_cache_host)) {
            $options['host'] = $this->_action_cache_host;
        }
        $options['namespace'] = 'actions';
        $this->writeFragment($this->_action_cache_path , $contents, $options);
        return true;
    }
    function getCacheType()
    {
        return $this->_caching_type;
    }
    function _setCachesAction($options)
    {
        if (!$this->_perform_caching) return;
        if (is_string($options)) {
            $options = Ak::toArray($options);
        }

        $default_options = array('include_get_parameters'=>array(),
                                 'cache_path'=>'');
        Ak::parseOptions($options, $default_options,array(),true);
        $this->_caches_action = $options;

        $actionName = $this->_controller->getActionName();

        if ($this->_caching_type == null && isset($this->_caches_action[$actionName])) {
            $this->_caching_type = 'action';
            $this->_action_include_get_parameters = $this->_caches_action[$actionName]['include_get_parameters'];
            $path = $this->_caches_action[$actionName]['cache_path'];
            $parts = parse_url($path);
            if (isset($parts['host'])) {
                $this->_action_cache_host = $parts['host'];
                $this->_action_cache_path = $parts['path'];
            } else {
                $this->_action_cache_path = $path;
            }

            if (!isset($this->_action_cache_host)) {
                $this->_action_cache_host = $this->_controller->Request->getHost();
            }
            $this->_action_cache_path = $this->_actionPath($this->_action_cache_path);
            $this->_controller->prependBeforeFilter(array(&$this,'beforeActionCache'));
            $this->_controller->appendAfterFilter(array(&$this,'afterActionCache'));
        }

    }

    function _actionPath($options)
    {
        $extension = $this->_controller->Request->getFormat();//$this->_extractExtension($this->_controller->Request->getPath());
        if (is_array($options)) {
            $path = $this->_pathFor($options);
        } else if ($options == null || empty($options)) {
            $path = $this->_pathFor();
        } else {
            $path = $options;
        }
        $path = $this->_normalize($path);
        $path = $this->_addExtension($path, $extension);
        return $path;
    }

    function expireAction($options, $params = array())
    {
        if (is_array($options) && !isset($options['lang'])) {
            $options['lang'] = '*';
        }
        $params['namespace'] = 'actions';
        return $this->expireFragment($options, $params);
    }
    function _normalize($path)
    {
        $path = $path == '/' ? '/index':$path;
        return $path;
    }
    function _addExtension($path, $extension)
    {
        if (!empty($extension) && substr($path,-strlen($extension))!==$extension) {
            $path = $path.'.'.$extension;
        }
        return $path;
    }

    function _extractExtension($file_path)
    {
        preg_match('/^[^\.]+\.(.+)$/',$file_path, $matches);
        return isset($matches[1])?$matches[1]:null;
    }
    function _pathFor($options = array(), $normalize = true)
    {
        $options = empty($options)?$this->_controller->params:$options;
        $options['controller'] = !isset($options['controller']) ? (isset($this->_controller->params['controller']) ? 
                                                                         $this->_controller->params['controller']:null):
                                                                 $options['controller'];
        $options['action'] = !isset($options['action']) ? (isset($this->_controller->params['action']) ? 
                                                                 $this->_controller->params['action']:null):
                                                          $options['action'];
        $options['id'] = !isset($options['id']) ? (isset($this->_controller->params['id']) ? 
                                                         $this->_controller->params['id']:null):
                                                  $options['id'];
        
        $options['skip_relative_url_root']=true;
        $url = $this->_controller->urlFor($options);
        $parts = parse_url($url);
        $path = $parts['path'];
        if ($normalize && (!isset($options['action']) || (isset($options['action']) && $options['action']==AK_DEFAULT_ACTION && !strstr($path,'/'.AK_DEFAULT_ACTION)))) {
            $path = rtrim($path,'/');
            $parts = preg_split('/\/+/',$path);
            $parts[] = AK_DEFAULT_ACTION;
            $path = implode('/', $parts);
        }
        $path = rtrim($path,'/');

        return $path;
    }

    /**
     * ########################################################################
     * #
     * #               END OF AkActionController callable methods
     * #
     * #########################################################################
     */

    /**
     * Looks up the cache store from the option array
     *
     * @param array $options
     */
    function _setCacheStore($options=array())
    {
        $this->_cache_store = AkCache::lookupStore($options);
    }

    /**
     * @access protected
     */
    function _cache($key, $options = null)
    {
        $return = false;
        if ($this->cacheConfigured()) {
            $return = $this->_cache_store->fetch(AkCache::expandCacheKey($key, $this->_controller), $options);
        }
        return $return;
    }


}