<?php

/**
*@file cp1046.php
* CP1046 Mapping and Charset implementation.
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
* CP1046  driver for Charset Class
*
* Charset::cp1046 provides functionality to convert
* CP1046 strings, to UTF-8 multibyte format and vice versa.
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
class cp1046 extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* CP1046 to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>65160,129=>215,130=>247,131=>63734,132=>63733,133=>63732,134=>63735,135=>65137,136=>136,137=>9632,138=>9474,139=>9472,140=>9488,141=>9484,142=>9492,143=>9496,144=>65145,145=>65147,146=>65149,147=>65151,148=>65143,149=>65162,150=>65264,151=>65267,152=>65266,153=>65230,154=>65231,155=>65232,156=>65270,157=>65272,158=>65274,159=>65276,160=>160,161=>63738,162=>63737,163=>63736,164=>164,165=>63739,166=>65163,167=>65169,168=>65175,169=>65179,170=>65183,171=>65187,172=>1548,173=>173,174=>65191,175=>65203,176=>1632,177=>1633,178=>1634,179=>1635,180=>1636,181=>1637,182=>1638,183=>1639,184=>1640,185=>1641,186=>65207,187=>1563,188=>65211,189=>65215,190=>65226,191=>1567,192=>65227,193=>1569,194=>1570,195=>1571,196=>1572,197=>1573,198=>1574,199=>1575,200=>1576,201=>1577,202=>1578,203=>1579,204=>1580,205=>1581,206=>1582,207=>1583,208=>1584,209=>1585,210=>1586,211=>1587,212=>1588,213=>1589,214=>1590,215=>1591,216=>65223,217=>1593,218=>1594,219=>65228,220=>65154,221=>65156,222=>65166,223=>65235,224=>1600,225=>1601,226=>1602,227=>1603,228=>1604,229=>1605,230=>1606,231=>1607,232=>1608,233=>1609,234=>1610,235=>1611,236=>1612,237=>1613,238=>1614,239=>1615,240=>1616,241=>1617,242=>1618,243=>65239,244=>65243,245=>65247,246=>63740,247=>65269,248=>65271,249=>65273,250=>65275,251=>65251,252=>65255,253=>65260,254=>65257);
		

	/**
	*  UTF-8 to CP1046 mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given CP1046 string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string CP1046 string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into CP1046
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    CP1046 string data
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