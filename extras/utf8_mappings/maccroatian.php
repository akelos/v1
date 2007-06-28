<?php

/**
*@file maccroatian.php
* MacCroatian Mapping and Charset implementation.
*
*/

//
// +----------------------------------------------------------------------+
// | Akelos PHP Application Framework                                     |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2005, Akelos Media, S.L.  http://www.akelos.org/  |
// | Released under the GNU Lesser General Public License                 |
// +----------------------------------------------------------------------+
// | You should have received the following files along with this library |
// | - COPYRIGHT (Additional copyright notice)                            |
// | - DISCLAIMER (Disclaimer of warranty)                                |
// | - README (Important information regarding this library)              |
// +----------------------------------------------------------------------+
//





/**
* MacCroatian  driver for Charset Class
*
* Charset::maccroatian provides functionality to convert
* MacCroatian strings, to UTF-8 multibyte format and vice versa.
*
* @package AKELOS
* @subpackage Localize
* @author Bermi Ferrer Martinez <bermi@akelos.org>
* @copyright Copyright (c) 2002-2005, Akelos Media, S.L. http://www.akelos.org
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
* @link http://www.unicode.org/Public/MAPPINGS/ Original Mapping taken from Unicode.org
* @since 0.1
* @version $Revision 0.1 $
*/
class maccroatian extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* MacCroatian to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>196,129=>197,130=>199,131=>201,132=>209,133=>214,134=>220,135=>225,136=>224,137=>226,138=>228,139=>227,140=>229,141=>231,142=>233,143=>232,144=>234,145=>235,146=>237,147=>236,148=>238,149=>239,150=>241,151=>243,152=>242,153=>244,154=>246,155=>245,156=>250,157=>249,158=>251,159=>252,160=>8224,161=>176,162=>162,163=>163,164=>167,165=>8226,166=>182,167=>223,168=>174,169=>352,170=>8482,171=>180,172=>168,173=>8800,174=>381,175=>216,176=>8734,177=>177,178=>8804,179=>8805,180=>8710,181=>181,182=>8706,183=>8721,184=>8719,185=>353,186=>8747,187=>170,188=>186,189=>8486,190=>382,191=>248,192=>191,193=>161,194=>172,195=>8730,196=>402,197=>8776,198=>262,199=>171,200=>268,201=>8230,202=>160,203=>192,204=>195,205=>213,206=>338,207=>339,208=>272,209=>8212,210=>8220,211=>8221,212=>8216,213=>8217,214=>247,215=>9674,217=>169,218=>8260,219=>164,220=>8249,221=>8250,222=>198,223=>187,224=>8211,225=>183,226=>8218,227=>8222,228=>8240,229=>194,230=>263,231=>193,232=>269,233=>200,234=>205,235=>206,236=>207,237=>204,238=>211,239=>212,240=>273,241=>210,242=>218,243=>219,244=>217,245=>305,246=>710,247=>732,248=>175,249=>960,250=>203,251=>730,252=>184,253=>202,254=>230,255=>711);
		

	/**
	*  UTF-8 to MacCroatian mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given MacCroatian string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string MacCroatian string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into MacCroatian
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    MacCroatian string data
	*/
	function _Utf8StringDecode($string)
	{
		$this->_LoadInverseMap();
		return parent::_Utf8StringDecode($string, $this->_fromUtfMap);
	}// -- end of &Utf8StringDecode -- //
		
		
	// ---- Private methods ---- //
		
	/**
	* Flips $this->_toUtfMap to $this->_fromUtfMap
	*
	* @access private
	* @return	null
	*/
	function _LoadInverseMap()
	{
		static $loaded;
		if(!isset($loaded)){
			$loaded = true;
			$this->_fromUtfMap = array_flip($this->_toUtfMap);
		}
	}// -- end of _LoadInverseMap -- //
	
}

?>