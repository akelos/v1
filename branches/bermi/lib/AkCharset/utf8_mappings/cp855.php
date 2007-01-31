<?php

/**
*@file cp855.php
* CP855 Mapping and Charset implementation.
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
* CP855  driver for Charset Class
*
* Charset::cp855 provides functionality to convert
* CP855 strings, to UTF-8 multibyte format and vice versa.
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
class cp855 extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* CP855 to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>1106,129=>1026,130=>1107,131=>1027,132=>1105,133=>1025,134=>1108,135=>1028,136=>1109,137=>1029,138=>1110,139=>1030,140=>1111,141=>1031,142=>1112,143=>1032,144=>1113,145=>1033,146=>1114,147=>1034,148=>1115,149=>1035,150=>1116,151=>1036,152=>1118,153=>1038,154=>1119,155=>1039,156=>1102,157=>1070,158=>1098,159=>1066,160=>1072,161=>1040,162=>1073,163=>1041,164=>1094,165=>1062,166=>1076,167=>1044,168=>1077,169=>1045,170=>1092,171=>1060,172=>1075,173=>1043,174=>171,175=>187,176=>9617,177=>9618,178=>9619,179=>9474,180=>9508,181=>1093,182=>1061,183=>1080,184=>1048,185=>9571,186=>9553,187=>9559,188=>9565,189=>1081,190=>1049,191=>9488,192=>9492,193=>9524,194=>9516,195=>9500,196=>9472,197=>9532,198=>1082,199=>1050,200=>9562,201=>9556,202=>9577,203=>9574,204=>9568,205=>9552,206=>9580,207=>164,208=>1083,209=>1051,210=>1084,211=>1052,212=>1085,213=>1053,214=>1086,215=>1054,216=>1087,217=>9496,218=>9484,219=>9608,220=>9604,221=>1055,222=>1103,223=>9600,224=>1071,225=>1088,226=>1056,227=>1089,228=>1057,229=>1090,230=>1058,231=>1091,232=>1059,233=>1078,234=>1046,235=>1074,236=>1042,237=>1100,238=>1068,239=>8470,240=>173,241=>1099,242=>1067,243=>1079,244=>1047,245=>1096,246=>1064,247=>1101,248=>1069,249=>1097,250=>1065,251=>1095,252=>1063,253=>167,254=>9632,255=>160);
		

	/**
	*  UTF-8 to CP855 mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given CP855 string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string CP855 string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into CP855
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    CP855 string data
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