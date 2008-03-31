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
* You can find some legal fonts collected by Fontsmack.com on the Akelos repository at:
* http://svn.akelos.org/extras/fonts/sifr
*/
class SifrHelper extends AkActionViewHelper
{
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
        if ($this->usesSifr()){
            $ath =& $this->_controller->asset_tag_helper;
            $result = $ath->stylesheet_link_tag('sIFR-screen', array('media' => 'screen'))."\n".
            $ath->stylesheet_link_tag('sIFR-print', array('media' => 'print'))."\n".
            $ath->javascript_include_tag("sifr")."\n";

            // Add in the tweaks.  These CSS items are temporary, and are never shown to the user.
            // They simply help with the layout during size calculations.  I've found 'tweak_size' to be
            // more useful here.
            $result .= "<style type=\"text/css\" media=\"screen\">\n";
            foreach ($this->getSifr() as $k => $v){
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
        if ($this->usesSifr()){
            $result = "<script type=\"text/javascript\">\n//<![CDATA[\nif(typeof sIFR == \"function\"){\n";
            foreach ($this->getSifr() as $k => $v){
                $url = $this->_computePublicPath($v['font'], 'fonts', 'swf');
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
        $this->setSifr($selector, $options);
    }

    function _computePublicPath($source, $dir = '', $ext = '')
    {
        $source = $source[0] != '/' && !strstr($source,':') ? "/$dir/$source" : $source;
        $source = !strstr($source,'.') ? "$source.$ext" : $source;

        if(AK_DEV_MODE && !strstr($source, '//') && strstr($source, '/fonts/')){
            $this->_verifyFontExistence($source);
        }

        $source = !preg_match('/^[-a-z]+:\/\//',$source) ? AK_ASSET_URL_PREFIX.$source : $source;
        $source = strstr($source,':') ? $source : $this->_controller->asset_host.$source;
        $source = substr($source,0,2) == '//' ? substr($source,1) : $source;

        return $source;
    }


    function setSifr($selector, $options = array())
    {
        static $_cache = array();
        if($selector === false){
            return $_cache;
        }
        $_cache[$selector] = $options;
    }

    function getSifr()
    {
        return $this->setSifr(false);
    }

    function usesSifr()
    {
        return count($this->getSifr()) > 0;
    }

    function _verifyFontExistence($font)
    {
        $font_name = str_replace('/fonts/', '',$font);
        if(!file_exists(AK_PUBLIC_DIR.DS.$font) && !file_exists(AK_PUBLIC_DIR.DS.$font.'.error.txt')){
            $remote_font = @Ak::url_get_contents('http://svn.akelos.org/extras/fonts/sifr/'.$font_name, array('timeout'=>30));
            if(empty($remote_font)){
                Ak::file_put_contents(AK_PUBLIC_DIR.DS.$font.'.error.txt', 'Could not download '.'http://svn.akelos.org/extras/sifr_fonts/'.$font_name);
            }else{
                Ak::file_put_contents(AK_PUBLIC_DIR.DS.$font, $remote_font);
            }
        }
    }
}

?>
