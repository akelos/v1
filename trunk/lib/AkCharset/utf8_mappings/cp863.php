<?php

/**
*@file cp863.php
* CP863 Mapping and Charset implementation.
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
* CP863  driver for Charset Class
*
* Charset::cp863 provides functionality to convert
* CP863 strings, to UTF-8 multibyte format and vice versa.
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
class cp863 extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* CP863 to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>199,129=>252,130=>233,131=>226,132=>194,133=>224,134=>182,135=>231,136=>234,137=>235,138=>232,139=>239,140=>238,141=>8215,142=>192,143=>167,144=>201,145=>200,146=>202,147=>244,148=>203,149=>207,150=>251,151=>249,152=>164,153=>212,154=>220,155=>162,156=>163,157=>217,158=>219,159=>402,160=>166,161=>180,162=>243,163=>250,164=>168,165=>184,166=>179,167=>175,168=>206,169=>8976,170=>172,171=>189,172=>188,173=>190,174=>171,175=>187,176=>9617,177=>9618,178=>9619,179=>9474,180=>9508,181=>9569,182=>9570,183=>9558,184=>9557,185=>9571,186=>9553,187=>9559,188=>9565,189=>9564,190=>9563,191=>9488,192=>9492,193=>9524,194=>9516,195=>9500,196=>9472,197=>9532,198=>9566,199=>9567,200=>9562,201=>9556,202=>9577,203=>9574,204=>9568,205=>9552,206=>9580,207=>9575,208=>9576,209=>9572,210=>9573,211=>9561,212=>9560,213=>9554,214=>9555,215=>9579,216=>9578,217=>9496,218=>9484,219=>9608,220=>9604,221=>9612,222=>9616,223=>9600,224=>945,225=>223,226=>915,227=>960,228=>931,229=>963,230=>181,231=>964,232=>934,233=>920,234=>937,235=>948,236=>8734,237=>966,238=>949,239=>8745,240=>8801,241=>177,242=>8805,243=>8804,244=>8992,245=>8993,246=>247,247=>8776,248=>176,249=>8729,250=>183,251=>8730,252=>8319,253=>178,254=>9632,255=>160);
		

	/**
	*  UTF-8 to CP863 mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given CP863 string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string CP863 string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into CP863
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    CP863 string data
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