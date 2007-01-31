<?php

/**
*@file macukraine.php
* MacUkraine Mapping and Charset implementation.
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
* MacUkraine  driver for Charset Class
*
* Charset::macukraine provides functionality to convert
* MacUkraine strings, to UTF-8 multibyte format and vice versa.
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
class macukraine extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* MacUkraine to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>1040,129=>1041,130=>1042,131=>1043,132=>1044,133=>1045,134=>1046,135=>1047,136=>1048,137=>1049,138=>1050,139=>1051,140=>1052,141=>1053,142=>1054,143=>1055,144=>1056,145=>1057,146=>1058,147=>1059,148=>1060,149=>1061,150=>1062,151=>1063,152=>1064,153=>1065,154=>1066,155=>1067,156=>1068,157=>1069,158=>1070,159=>1071,160=>8224,161=>176,162=>1168,163=>163,164=>167,165=>8226,166=>182,167=>1030,168=>174,169=>169,170=>8482,171=>1026,172=>1106,173=>8800,174=>1027,175=>1107,176=>8734,177=>177,178=>8804,179=>8805,180=>1110,181=>181,182=>1169,183=>1032,184=>1028,185=>1108,186=>1031,187=>1111,188=>1033,189=>1113,190=>1034,191=>1114,192=>1112,193=>1029,194=>172,195=>8730,196=>402,197=>8776,198=>8710,199=>171,200=>187,201=>8230,202=>160,203=>1035,204=>1115,205=>1036,206=>1116,207=>1109,208=>8211,209=>8212,210=>8220,211=>8221,212=>8216,213=>8217,214=>247,215=>8222,216=>1038,217=>1118,218=>1039,219=>1119,220=>8470,221=>1025,222=>1105,223=>1103,224=>1072,225=>1073,226=>1074,227=>1075,228=>1076,229=>1077,230=>1078,231=>1079,232=>1080,233=>1081,234=>1082,235=>1083,236=>1084,237=>1085,238=>1086,239=>1087,240=>1088,241=>1089,242=>1090,243=>1091,244=>1092,245=>1093,246=>1094,247=>1095,248=>1096,249=>1097,250=>1098,251=>1099,252=>1100,253=>1101,254=>1102,255=>164);
		

	/**
	*  UTF-8 to MacUkraine mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given MacUkraine string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string MacUkraine string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into MacUkraine
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    MacUkraine string data
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