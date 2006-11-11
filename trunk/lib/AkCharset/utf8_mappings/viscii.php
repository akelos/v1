<?php

/**
*@file viscii.php
* VISCII Mapping and Charset implementation.
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
* VISCII  driver for Charset Class
*
* Charset::viscii provides functionality to convert
* VISCII strings, to UTF-8 multibyte format and vice versa.
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
class viscii extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* VISCII to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>7858,3=>3,4=>4,5=>7860,6=>7850,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>7926,21=>21,22=>22,23=>23,24=>24,25=>7928,26=>26,27=>27,28=>28,29=>29,30=>7924,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>7840,129=>7854,130=>7856,131=>7862,132=>7844,133=>7846,134=>7848,135=>7852,136=>7868,137=>7864,138=>7870,139=>7872,140=>7874,141=>7876,142=>7878,143=>7888,144=>7890,145=>7892,146=>7894,147=>7896,148=>7906,149=>7898,150=>7900,151=>7902,152=>7882,153=>7886,154=>7884,155=>7880,156=>7910,157=>360,158=>7908,159=>7922,160=>213,161=>7855,162=>7857,163=>7863,164=>7845,165=>7847,166=>7849,167=>7853,168=>7869,169=>7865,170=>7871,171=>7873,172=>7875,173=>7877,174=>7879,175=>7889,176=>7891,177=>7893,178=>7895,179=>7904,180=>416,181=>7897,182=>7901,183=>7903,184=>7883,185=>7920,186=>7912,187=>7914,188=>7916,189=>417,190=>7899,191=>431,192=>192,193=>193,194=>194,195=>195,196=>7842,197=>258,198=>7859,199=>7861,200=>200,201=>201,202=>202,203=>7866,204=>204,205=>205,206=>296,207=>7923,208=>272,209=>7913,210=>210,211=>211,212=>212,213=>7841,214=>7927,215=>7915,216=>7917,217=>217,218=>218,219=>7929,220=>7925,221=>221,222=>7905,223=>432,224=>224,225=>225,226=>226,227=>227,228=>7843,229=>259,230=>7919,231=>7851,232=>232,233=>233,234=>234,235=>7867,236=>236,237=>237,238=>297,239=>7881,240=>273,241=>7921,242=>242,243=>243,244=>244,245=>245,246=>7887,247=>7885,248=>7909,249=>249,250=>250,251=>361,252=>7911,253=>253,254=>7907,255=>7918);
		

	/**
	*  UTF-8 to VISCII mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given VISCII string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string VISCII string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return parent::_Utf8StringEncode($string, $this->_toUtfMap);
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into VISCII
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    VISCII string data
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