<?php

/**
*@file cp1124.php
* CP1124 Mapping and Charset implementation.
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
* CP1124  driver for Charset Class
*
* Charset::cp1124 provides functionality to convert
* CP1124 strings, to UTF-8 multibyte format and vice versa.
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
class cp1124 extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* CP1124 to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>128,129=>129,130=>130,131=>131,132=>132,133=>133,134=>134,135=>135,136=>136,137=>137,138=>138,139=>139,140=>140,141=>141,142=>142,143=>143,144=>144,145=>145,146=>146,147=>147,148=>148,149=>149,150=>150,151=>151,152=>152,153=>153,154=>154,155=>155,156=>156,157=>157,158=>158,159=>159,160=>160,161=>1025,162=>1026,163=>1168,164=>1028,165=>1029,166=>1030,167=>1031,168=>1032,169=>1033,170=>1034,171=>1035,172=>1036,173=>173,174=>1038,175=>1039,176=>1040,177=>1041,178=>1042,179=>1043,180=>1044,181=>1045,182=>1046,183=>1047,184=>1048,185=>1049,186=>1050,187=>1051,188=>1052,189=>1053,190=>1054,191=>1055,192=>1056,193=>1057,194=>1058,195=>1059,196=>1060,197=>1061,198=>1062,199=>1063,200=>1064,201=>1065,202=>1066,203=>1067,204=>1068,205=>1069,206=>1070,207=>1071,208=>1072,209=>1073,210=>1074,211=>1075,212=>1076,213=>1077,214=>1078,215=>1079,216=>1080,217=>1081,218=>1082,219=>1083,220=>1084,221=>1085,222=>1086,223=>1087,224=>1088,225=>1089,226=>1090,227=>1091,228=>1092,229=>1093,230=>1094,231=>1095,232=>1096,233=>1097,234=>1098,235=>1099,236=>1100,237=>1101,238=>1102,239=>1103,240=>8470,241=>1105,242=>1106,243=>1169,244=>1108,245=>1109,246=>1110,247=>1111,248=>1112,249=>1113,250=>1114,251=>1115,252=>1116,253=>167,254=>1118,255=>1119);
		

	/**
	*  UTF-8 to CP1124 mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given CP1124 string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string CP1124 string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into CP1124
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    CP1124 string data
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