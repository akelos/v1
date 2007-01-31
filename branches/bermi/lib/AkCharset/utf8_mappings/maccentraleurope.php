<?php

/**
*@file maccentraleurope.php
* MacCentralEurope Mapping and Charset implementation.
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
* MacCentralEurope  driver for Charset Class
*
* Charset::maccentraleurope provides functionality to convert
* MacCentralEurope strings, to UTF-8 multibyte format and vice versa.
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
class maccentraleurope extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* MacCentralEurope to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>196,129=>256,130=>257,131=>201,132=>260,133=>214,134=>220,135=>225,136=>261,137=>268,138=>228,139=>269,140=>262,141=>263,142=>233,143=>377,144=>378,145=>270,146=>237,147=>271,148=>274,149=>275,150=>278,151=>243,152=>279,153=>244,154=>246,155=>245,156=>250,157=>282,158=>283,159=>252,160=>8224,161=>176,162=>280,163=>163,164=>167,165=>8226,166=>182,167=>223,168=>174,169=>169,170=>8482,171=>281,172=>168,173=>8800,174=>291,175=>302,176=>303,177=>298,178=>8804,179=>8805,180=>299,181=>310,182=>8706,183=>8721,184=>322,185=>315,186=>316,187=>317,188=>318,189=>313,190=>314,191=>325,192=>326,193=>323,194=>172,195=>8730,196=>324,197=>327,198=>8710,199=>171,200=>187,201=>8230,202=>160,203=>328,204=>336,205=>213,206=>337,207=>332,208=>8211,209=>8212,210=>8220,211=>8221,212=>8216,213=>8217,214=>247,215=>9674,216=>333,217=>340,218=>341,219=>344,220=>8249,221=>8250,222=>345,223=>342,224=>343,225=>352,226=>8218,227=>8222,228=>353,229=>346,230=>347,231=>193,232=>356,233=>357,234=>205,235=>381,236=>382,237=>362,238=>211,239=>212,240=>363,241=>366,242=>218,243=>367,244=>368,245=>369,246=>370,247=>371,248=>221,249=>253,250=>311,251=>379,252=>321,253=>380,254=>290,255=>711);
		

	/**
	*  UTF-8 to MacCentralEurope mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given MacCentralEurope string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string MacCentralEurope string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into MacCentralEurope
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    MacCentralEurope string data
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