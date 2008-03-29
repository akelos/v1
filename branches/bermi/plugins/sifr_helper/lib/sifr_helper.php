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
* @author Rob Morris
* @author Bermi Ferrer
* @copyright Rob Morris
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
*/


/**
* SifrHelper is an Akelos version of the Ruby on Rails plugin that allows simple, drop-in use of the sIFR 2.0
* font-replacement technique in your Akelos views.  Use it to bring any TrueType font to your website in a cross-platform,
* browser neutral manner.
*
* == What is sIFR?  
* The full story can be found here: http://www.mikeindustries.com/sifr
*
* The quick version is that sIFR is a drop-in, accessible, gracefully degrading technique for 
* richer typography (aka more fonts) in your web designs.  
* Using flash swf files containing a given font definition, a set of Javascript 
* functions cleverly overlay a Flash object over specified
* text elements, injecting the text on the way, and rescaling to fit as best as possible.
*
* Simply put, sIFR lets you use TrueType fonts on any platform - without rendered images or other nastiness.
*/
class SifrHelper extends AkActionViewHelper
{
    var $sifr = array();
    var $options = array();
    /**
    * Includes all needed CSS and Javascript libraries, and adds dynamically generated tweaks as needed.  
    *
    * *Usage*: Add the following line within the <head> section of your layout (or view, if you only need 
    * sIFR-ized fonts on a single page):
    *   
    *   <%= sifr_head_generator -%>
    */
    function sifr_head_generator()
    {
        if (!empty($this->sifr)){
            $ath =& $this->_controller->asset_tag_helper;
            $result = $ath->stylesheet_link_tag('sIFR-screen', array('media' => 'screen'))."\n".
            $ath->stylesheet_link_tag('sIFR-print', array('media' => 'print'))."\n".
            $ath->javascript_include_tag("sifr")."\n";

            // Add in the tweaks.  These CSS items are temporary, and are never shown to the user.
            // They simply help with the layout during size calculations.  I've found 'tweak_size' to be
            // more useful here.
            $result .= "<style type=\"text/css\" media=\"screen\">\n";
            foreach ($this->sifr as $k => $v){
                $result .= ".sIFR-hasFlash $k {\n".
                "  visibility: hidden;\n".
                (empty($v['tweak_size'])?'':"  font-size: {$v['tweak_size']}px;\n").
                (empty($v['tweak_spacing'])?'':" letter-spacing: {$v['tweak_spacing']}px;\n")."}\n";
            }
            return $result."</style>\n";
        }
    }

    /**
     * Generates the replacement calls to the sIFR Javascript library.
     * *Usage*: Add the following line at the end of the <body> section of your layout (or view):
     *
     *  <%= sifr_body_generator -%>
     */
    function sifr_body_generator()
    {
        if (!empty($this->sifr)){
            $result = "<script type=\"text/javascript\">\n//<![CDATA[\nif(typeof sIFR == \"function\"){\n";
            foreach ($this->sifr as $k => $v){
                $url = $this->_compute_public_path($v['font'], 'fonts', 'swf');
                $result .= "sIFR.replaceElement(named({\n".
                "sSelector:\"$k\",\n" .
                "sFlashSrc:\"$url\",\n".
                "sColor:\"".(empty($v['color'])?'#000000':$v['color'])."\",\n".
                "sBgColor:\"".(empty($v['bgcolor'])?'#ffffff':$v['bgcolor'])."\",\n".
                "nPaddingTop:\"".(empty($v['padding_top'])?'0':$v['padding_top'])."\",\n".
                "nPaddingBottom:\"".(empty($v['padding_bottom'])?'0':$v['padding_bottom'])."\",\n".
                "sFlashVars:\"".(empty($v['centered'])?'':'textalign=center')."\",\n".
                "sLinkColor:\"".(empty($v['linkcolor'])?(empty($v['color'])?'#000000':$v['color']):$v['linkcolor'])."\",\n".
                "sHoverColor:\"".(empty($v['hovercolor'])?(empty($v['linkcolor'])?(empty($v['color'])?'#000000':$v['color']):$v['linkcolor']):$v['hovercolor'])."\"\n".
                "}));\n";
            }
            return $result."};\n//]]>\n</script>\n";
        }
    }

    /**
    * Replaces a given CSS id, class or other selector with the specified font.
    *
    * *Usage*: Add a call in your view for each CSS element & font pair you want sIFR-ized, like so:
    *
    *   <%= sifr_replace('.some_class', 'my_font', :color => '#ff0000') %>
    *
    * *Arguments*:
    *
    * [selector] Any valid CSS selector
    * [font] Font file to be used, so for a file named trebuchet_bold.swf, the font would be 'trebuchet_bold'
    *   Note that you can also pass a full url (eg '/special/my_font.swf') for this param, which will ignore any
    *   automatic path generation.
    * [options] An optional array of options, with the following possible keys:
    * * color - color for the text, in the web-standard '#RRGGBB' hex encoding format, defaults to #000000
    * * bgcolor - background color for text, defaults to #FFFFFF
    * * linkcolor - color for anchor text, defaults to :color or #000000 if none specified
    * * hovercolor - color for anchor text on hover, defaults to :linkcolor or #000000 if none specified
    * * centered - set to true to center text
    * * padding_top - top padding in pixels, defaults to 0
    * * padding_bottom - bottom padding in pixels, defaults to 0
    * * tweak_size - helps when adjusting sIFR text to match normal styled text size and layout
    * * tweak_spacing - helps when adjusting sIFR text, this one modifies line-spacing
    */
    function sifr_replace($selector, $font, $options = array())
    {
        $options['font'] = $font;
        $this->sifr[$selector] = $options;
    }

    function _compute_public_path($source, $dir = '', $ext = '')
    {
        $source = $source[0] != '/' && !strstr($source,':') ? "/$dir/$source" : $source;
        $source = !strstr($source,'.') ? "$source.$ext" : $source;
        $source = !preg_match('/^[-a-z]+:\/\//',$source) ? AK_ASSET_URL_PREFIX.$source : $source;
        $source = strstr($source,':') ? $source : $this->_controller->asset_host.$source;
        $source = substr($source,0,2) == '//' ? substr($source,1) : $source;

        return $source;
    }

}

?>
