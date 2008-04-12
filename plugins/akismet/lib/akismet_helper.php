<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2007, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
* @package ActionView
* @subpackage Helpers
* @author Bermi Ferrer <bermi a.t akelos c.om>
* @copyright Copyright (c) 2002-2007, Akelos Media, S.L. http://www.akelos.org
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
*/

!defined('AK_AKISMET_API_KEY') && define('AK_AKISMET_API_KEY', false);
!defined('AK_AKISMET_SITE_URL') && define('AK_AKISMET_SITE_URL', 'http://'.AK_HOST);

/**
* Uses Akismet API to detect spam in comments
*
* You'll have an akismet_helper in your controller with these methods:
* 
* * isSpam($comment_content, $options)
* * reportSpam($comment_content, $options)
* * reportHam($comment_content, $options)
*
* where $options can be:
* 
* blog or site_url  (required. Defaults to http://AK_HOST)
*   Your application "home page".. 
* user_ip (required. Defaults to AK_REMOTE_IP)
*   IP address of the comment submitter.
* user_agent (required. Defaults to $_SERVER['HTTP_USER_AGENT'].)
*   User agent information.
* referrer (note spelling. Defaults to $_SERVER['HTTP_REFERER'].)
*   The content of the HTTP_REFERER header should be sent here.
* permalink (Defaults to AK_URL.)
*   The permanent location of the entry the comment was submitted to.
* comment_type (Defaults to comment)
*   May be blank, comment, trackback, pingback, or a made up value like "registration".
* comment_author or author
*   Submitted name with the comment
* comment_author_email or email
*   Submitted email address
* comment_author_url or author_url
*   Commenter URL.
*/
class AkismetHelper extends AkActionViewHelper
{
    var $_api_key = AK_AKISMET_API_KEY;
    var $_site_url = AK_AKISMET_SITE_URL;

    var $option_aliases = array(
    '_site_url' => 'blog',
    'author' => 'comment_author',
    'email' => 'comment_author_email',
    'author_url' => 'comment_author_url',
    'content' => 'comment_content'
    );

    var $required_fields = array('blog', 'user_ip', 'user_agent');

    function isSpam($content, $options = array())
    {
        $options['comment_content'] =& $content;
        return $this->_performRequest('comment-check', $this->_getDefaultedOptions($options)) == 'true';
    }

    function reportSpam($content, $options = array())
    {
        $options['comment_content'] =& $content;
        $this->_performRequest('submit-spam', $this->_getDefaultedOptions($options), false);
    }

    function reportHam($content, $options = array())
    {
        $options['comment_content'] =& $content;
        $this->_performRequest('submit-ham', $this->_getDefaultedOptions($options), false);
    }

    function verifyKey()
    {
        return Ak::url_get_contents('http://rest.akismet.com/1.1/verify-key', array('method'=>'POST', 'params'=>array('key'=>$this->_api_key, 'blog' => $this->_site_url))) == 'valid';
    }

    function _getDefaultedOptions($options = array(), $called_by_commenter = true)
    {
        $default_options = array(
        'blog' => $this->_site_url,
        'user_ip' => defined('AK_REMOTE_IP') && $called_by_commenter ? AK_REMOTE_IP : false,
        'user_agent' => $called_by_commenter && !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']: false,
        'referrer' => $called_by_commenter && !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
        'permalink' => $called_by_commenter && defined('AK_URL') ? AK_URL : '',
        'comment_type' =>'comment',
        );

        return array_merge($default_options, $options);
    }

    function _mapOptionAliases(&$options)
    {
        foreach ($this->option_aliases as $alias => $option){
            if(isset($options[$alias])){
                $options[$option] = $options[$alias];
                unset($options[$alias]);
            }
        }
    }

    function _ensuredRequiredOptionsAreSet($options)
    {
        foreach ($this->required_fields as $field){
            !empty($options[$field]) or trigger_error($this->t('You need to provide a valid %field.', array('%field'=>$field)), E_USER_NOTICE);
        }
    }

    function _performRequest($method, $options)
    {
        $this->_mapOptionAliases($options);
        $this->_ensuredRequiredOptionsAreSet($options);
        return Ak::url_get_contents("http://$this->_api_key.rest.akismet.com/1.1/$method",
        array('method'=>'POST', 'params' => $options));
    }
}

?>