<?php

/**
*@file koi8_r.php
* KOI8-R Mapping and Charset implementation.
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
* KOI8-R  driver for Charset Class
*
* Charset::koi8_r provides functionality to convert
* KOI8-R strings, to UTF-8 multibyte format and vice versa.
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
class koi8_r extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* KOI8-R to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>9472,129=>9474,130=>9484,131=>9488,132=>9492,133=>9496,134=>9500,135=>9508,136=>9516,137=>9524,138=>9532,139=>9600,140=>9604,141=>9608,142=>9612,143=>9616,144=>9617,145=>9618,146=>9619,147=>8992,148=>9632,149=>8729,150=>8730,151=>8776,152=>8804,153=>8805,154=>160,155=>8993,156=>176,157=>178,158=>183,159=>247,160=>9552,161=>9553,162=>9554,163=>1105,164=>9555,165=>9556,166=>9557,167=>9558,168=>9559,169=>9560,170=>9561,171=>9562,172=>9563,173=>9564,174=>9565,175=>9566,176=>9567,177=>9568,178=>9569,179=>1025,180=>9570,181=>9571,182=>9572,183=>9573,184=>9574,185=>9575,186=>9576,187=>9577,188=>9578,189=>9579,190=>9580,191=>169,192=>1102,193=>1072,194=>1073,195=>1094,196=>1076,197=>1077,198=>1092,199=>1075,200=>1093,201=>1080,202=>1081,203=>1082,204=>1083,205=>1084,206=>1085,207=>1086,208=>1087,209=>1103,210=>1088,211=>1089,212=>1090,213=>1091,214=>1078,215=>1074,216=>1100,217=>1099,218=>1079,219=>1096,220=>1101,221=>1097,222=>1095,223=>1098,224=>1070,225=>1040,226=>1041,227=>1062,228=>1044,229=>1045,230=>1060,231=>1043,232=>1061,233=>1048,234=>1049,235=>1050,236=>1051,237=>1052,238=>1053,239=>1054,240=>1055,241=>1071,242=>1056,243=>1057,244=>1058,245=>1059,246=>1046,247=>1042,248=>1068,249=>1067,250=>1047,251=>1064,252=>1069,253=>1065,254=>1063,255=>1066);
		

	/**
	*  UTF-8 to KOI8-R mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given KOI8-R string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string KOI8-R string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into KOI8-R
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    KOI8-R string data
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