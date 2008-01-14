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

@ini_set("include_path",(ini_get("include_path").PATH_SEPARATOR.dirname(__FILE__).DS.'PEAR'));

/**
* Returns the difference between two texts as HTML 
*/
class DiffHelper extends AkActionViewHelper
{
    function _prepareText($text)
    {
        return str_replace(array("\r\n", "\r"), array("\n","\n"), $text);
    }

    function diff($from_text, $to_text, $options = array())
    {
        $default_options = array(
            'insert_class' => '',
            'delete_class' => '',
        );
        $options = array_merge($default_options, $options);
    
        require_once('PEAR'.DS.'Text'.DS.'Diff.php');
        require_once('PEAR'.DS.'Text'.DS.'Diff'.DS.'Renderer.php');
        require_once('PEAR'.DS.'Text'.DS.'Diff'.DS.'Renderer'.DS.'inline.php');

        $from_text  =    explode("\n", $this->_prepareText($from_text)."\n");
        $to_text    =    explode("\n", $this->_prepareText($to_text)."\n");
        array_pop($to_text);
        array_pop($from_text);

        $Renderer =& new Text_Diff_Renderer_inline();
        $Renderer->_ins_prefix = empty($options['insert_class']) ? '<ins>' : '<ins class="'.$options['insert_class'].'">';
        $Renderer->_del_prefix = empty($options['delete_class']) ? '<del>' : '<del class="'.$options['delete_class'].'">';
        return trim($Renderer->render(new Text_Diff($from_text, $to_text)),"\n");

    }
}


?>