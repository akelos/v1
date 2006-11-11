<?php

/**
*@file macarabic.php
* MacArabic Mapping and Charset implementation.
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
* MacArabic  driver for Charset Class
*
* Charset::macarabic provides functionality to convert
* MacArabic strings, to UTF-8 multibyte format and vice versa.
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
class macarabic extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* MacArabic to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>196,129=>160,130=>199,131=>201,132=>209,133=>214,134=>220,135=>225,136=>224,137=>226,138=>228,139=>1722,140=>171,141=>231,142=>233,143=>232,144=>234,145=>235,146=>237,147=>8230,148=>238,149=>239,150=>241,151=>243,152=>187,153=>244,154=>246,155=>247,156=>250,157=>249,158=>251,159=>252,165=>1642,172=>1548,176=>1632,177=>1633,178=>1634,179=>1635,180=>1636,181=>1637,182=>1638,183=>1639,184=>1640,185=>1641,187=>1563,191=>1567,192=>1645,193=>1569,194=>1570,195=>1571,196=>1572,197=>1573,198=>1574,199=>1575,200=>1576,201=>1577,202=>1578,203=>1579,204=>1580,205=>1581,206=>1582,207=>1583,208=>1584,209=>1585,210=>1586,211=>1587,212=>1588,213=>1589,214=>1590,215=>1591,216=>1592,217=>1593,218=>1594,224=>1600,225=>1601,226=>1602,227=>1603,228=>1604,229=>1605,230=>1606,231=>1607,232=>1608,233=>1609,234=>1610,235=>1611,236=>1612,237=>1613,238=>1614,239=>1615,240=>1616,241=>1617,242=>1618,243=>1662,244=>1657,245=>1670,246=>1749,247=>1700,248=>1711,249=>1672,250=>1681,254=>1688,255=>1746);
		

	/**
	*  UTF-8 to MacArabic mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given MacArabic string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string MacArabic string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into MacArabic
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    MacArabic string data
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