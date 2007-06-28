<?php

/**
*@file macgreek.php
* MacGreek Mapping and Charset implementation.
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
* MacGreek  driver for Charset Class
*
* Charset::macgreek provides functionality to convert
* MacGreek strings, to UTF-8 multibyte format and vice versa.
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
class macgreek extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* MacGreek to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>196,129=>185,130=>178,131=>201,132=>179,133=>214,134=>220,135=>901,136=>224,137=>226,138=>228,139=>900,140=>168,141=>231,142=>233,143=>232,144=>234,145=>235,146=>163,147=>8482,148=>238,149=>239,150=>8226,151=>189,152=>8240,153=>244,154=>246,155=>166,156=>173,157=>249,158=>251,159=>252,160=>8224,161=>915,162=>916,163=>920,164=>923,165=>926,166=>928,167=>223,168=>174,169=>169,170=>931,171=>938,172=>167,173=>8800,174=>176,175=>903,176=>913,177=>177,178=>8804,179=>8805,180=>165,181=>914,182=>917,183=>918,184=>919,185=>921,186=>922,187=>924,188=>934,189=>939,190=>936,191=>937,192=>940,193=>925,194=>172,195=>927,196=>929,197=>8776,198=>932,199=>171,200=>187,201=>8230,202=>160,203=>933,204=>935,205=>902,206=>904,207=>339,208=>8211,209=>8213,210=>8220,211=>8221,212=>8216,213=>8217,214=>247,215=>905,216=>906,217=>908,218=>910,219=>941,220=>942,221=>943,222=>972,223=>911,224=>973,225=>945,226=>946,227=>968,228=>948,229=>949,230=>966,231=>947,232=>951,233=>953,234=>958,235=>954,236=>955,237=>956,238=>957,239=>959,240=>960,241=>974,242=>961,243=>963,244=>964,245=>952,246=>969,247=>962,248=>967,249=>965,250=>950,251=>970,252=>971,253=>912,254=>944);
		

	/**
	*  UTF-8 to MacGreek mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given MacGreek string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string MacGreek string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into MacGreek
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    MacGreek string data
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