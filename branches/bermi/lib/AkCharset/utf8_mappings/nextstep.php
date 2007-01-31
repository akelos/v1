<?php

/**
*@file nextstep.php
* NEXTSTEP Mapping and Charset implementation.
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
* NEXTSTEP  driver for Charset Class
*
* Charset::nextstep provides functionality to convert
* NEXTSTEP strings, to UTF-8 multibyte format and vice versa.
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
class nextstep extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* NEXTSTEP to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>160,129=>192,130=>193,131=>194,132=>195,133=>196,134=>197,135=>199,136=>200,137=>201,138=>202,139=>203,140=>204,141=>205,142=>206,143=>207,144=>208,145=>209,146=>210,147=>211,148=>212,149=>213,150=>214,151=>217,152=>218,153=>219,154=>220,155=>221,156=>222,157=>181,158=>215,159=>247,160=>169,161=>161,162=>162,163=>163,164=>8260,165=>165,166=>402,167=>167,168=>164,169=>8217,170=>8220,171=>171,172=>8249,173=>8250,174=>64257,175=>64258,176=>174,177=>8211,178=>8224,179=>8225,180=>183,181=>166,182=>182,183=>8226,184=>8218,185=>8222,186=>8221,187=>187,188=>8230,189=>8240,190=>172,191=>191,192=>185,193=>715,194=>180,195=>710,196=>732,197=>175,198=>728,199=>729,200=>168,201=>178,202=>730,203=>184,204=>179,205=>733,206=>731,207=>711,208=>8212,209=>177,210=>188,211=>189,212=>190,213=>224,214=>225,215=>226,216=>227,217=>228,218=>229,219=>231,220=>232,221=>233,222=>234,223=>235,224=>236,225=>198,226=>237,227=>170,228=>238,229=>239,230=>240,231=>241,232=>321,233=>216,234=>338,235=>186,236=>242,237=>243,238=>244,239=>245,240=>246,241=>230,242=>249,243=>250,244=>251,245=>305,246=>252,247=>253,248=>322,249=>248,250=>339,251=>223,252=>254,253=>255);
		

	/**
	*  UTF-8 to NEXTSTEP mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given NEXTSTEP string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string NEXTSTEP string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into NEXTSTEP
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    NEXTSTEP string data
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