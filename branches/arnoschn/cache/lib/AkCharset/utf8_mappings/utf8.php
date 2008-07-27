<?php

/**
*@file utf8.php
* UTF-8 Mapping and Charset implementation.
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
* UTF-8  driver for Charset Class
*
* Charset::utf8 provides functionality to convert
* UTF-8 strings, to UTF-8 multibyte format and vice versa.
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
class utf8 extends AkCharset
{


	// ------ CLASS ATTRIBUTES ------ //



	// ---- Private attributes ---- //


	/**
	* UTF-8 to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_toUtfMap
	*/
	var $_toUtfMap = array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15,16=>16,17=>17,18=>18,19=>19,20=>20,21=>21,22=>22,23=>23,24=>24,25=>25,26=>26,27=>27,28=>28,29=>29,30=>30,31=>31,32=>32,33=>33,34=>34,35=>35,36=>36,37=>37,38=>38,39=>39,40=>40,41=>41,42=>42,43=>43,44=>44,45=>45,46=>46,47=>47,48=>48,49=>49,50=>50,51=>51,52=>52,53=>53,54=>54,55=>55,56=>56,57=>57,58=>58,59=>59,60=>60,61=>61,62=>62,63=>63,64=>64,65=>65,66=>66,67=>67,68=>68,69=>69,70=>70,71=>71,72=>72,73=>73,74=>74,75=>75,76=>76,77=>77,78=>78,79=>79,80=>80,81=>81,82=>82,83=>83,84=>84,85=>85,86=>86,87=>87,88=>88,89=>89,90=>90,91=>91,92=>92,93=>93,94=>94,95=>95,96=>96,97=>97,98=>98,99=>99,100=>100,101=>101,102=>102,103=>103,104=>104,105=>105,106=>106,107=>107,108=>108,109=>109,110=>110,111=>111,112=>112,113=>113,114=>114,115=>115,116=>116,117=>117,118=>118,119=>119,120=>120,121=>121,122=>122,123=>123,124=>124,125=>125,126=>126,127=>127,128=>128,129=>129,130=>130,131=>131,132=>132,133=>133,134=>134,135=>135,136=>136,137=>137,138=>138,139=>139,140=>140,141=>141,142=>142,143=>143,144=>144,145=>145,146=>146,147=>147,148=>148,149=>149,150=>150,151=>151,152=>152,153=>153,154=>154,155=>155,156=>156,157=>157,158=>158,159=>159,160=>160,162=>1415,163=>1417,164=>41,165=>40,166=>187,167=>171,168=>8212,169=>46,170=>1373,171=>44,172=>45,173=>1418,174=>8230,175=>1372,176=>1371,177=>1374,178=>1329,179=>1377,180=>1330,181=>1378,182=>1331,183=>1379,184=>1332,185=>1380,186=>1333,187=>1381,188=>1334,189=>1382,190=>1335,191=>1383,192=>1336,193=>1384,194=>1337,195=>1385,196=>1338,197=>1386,198=>1339,199=>1387,200=>1340,201=>1388,202=>1341,203=>1389,204=>1342,205=>1390,206=>1343,207=>1391,208=>1344,209=>1392,210=>1345,211=>1393,212=>1346,213=>1394,214=>1347,215=>1395,216=>1348,217=>1396,218=>1349,219=>1397,220=>1350,221=>1398,222=>1351,223=>1399,224=>1352,225=>1400,226=>1353,227=>1401,228=>1354,229=>1402,230=>1355,231=>1403,232=>1356,233=>1404,234=>1357,235=>1405,236=>1358,237=>1406,238=>1359,239=>1407,240=>1360,241=>1408,242=>1361,243=>1409,244=>1362,245=>1410,246=>1363,247=>1411,248=>1364,249=>1412,250=>1365,251=>1413,252=>1366,253=>1414,254=>1370);
		

	/**
	*  UTF-8 to UTF-8 mapping array.
	*
	* @access private
	* @var    array    $_fromUtfMap
	*/
	var $_fromUtfMap = null;


	// ------------------------------



	// ------ CLASS METHODS ------ //


	// ---- Public methods ---- //


	/**
	* Encodes given UTF-8 string into UFT-8
	*
	* @access public
	* @see UtfDecode
	* @param    string    $string UTF-8 string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringEncode($string)
	{
		return $string;
	
	}// -- end of &Utf8StringEncode -- //

	/**
	* Decodes given UFT-8 string into UTF-8
	*
	* @access public
	* @see UtfEncode
	* @param    string    $string UTF-8 string
	* @return    string    UTF-8 string data
	*/
	function _Utf8StringDecode($string)
	{
		return $string;
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
			$this->_fromUtfMap = $this->_toUtfMap;
		}
	}// -- end of _LoadInverseMap -- //
	
}

?>