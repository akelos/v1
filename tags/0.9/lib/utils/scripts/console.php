<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package ActiveSupport
 * @subpackage Scripts
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

error_reporting(defined('AK_ERROR_REPORTING_ON_SCRIPTS') ? AK_ERROR_REPORTING_ON_SCRIPTS : 0);

require_once(AK_LIB_DIR.DS.'Ak.php');
require_once(AK_LIB_DIR.DS.'AkObject.php');
require_once(AK_LIB_DIR.DS.'AkInflector.php');
require_once(AK_LIB_DIR.DS.'AkPhpParser.php');

defined('AK_SKIP_DB_CONNECTION') && AK_SKIP_DB_CONNECTION ? ($dsn='') : Ak::db(&$dsn);
defined('AK_RECODE_UTF8_ON_CONSOLE_TO') ? null : define('AK_RECODE_UTF8_ON_CONSOLE_TO', false);

require_once(AK_LIB_DIR.DS.'AkActiveRecord.php');
require_once(AK_LIB_DIR.DS.'AkActionMailer.php');
require_once(AK_APP_DIR.DS.'shared_model.php');
require_once(AK_LIB_DIR.DS.'utils'.DS.'generators'.DS.'AkelosGenerator.php');
require_once(AK_LIB_DIR.DS.'AkInstaller.php');


if ($id_dir = opendir(AK_MODELS_DIR.DS)){
    while (false !== ($file = readdir($id_dir))){
        if ($file != "." && $file != ".." && $file != '.svn' && $file[0] != '_' && substr($file,-12,8) != '_service'){
            if(!is_dir(AK_MODELS_DIR.DS.$file)){
                include_once(AK_MODELS_DIR.DS.$file);
            }
        }
    }
    closedir($id_dir);
}

define('AK_PROMT',fopen("php://stdin","r"));

$join_command = false;
$promt_line = ">>> ";
while(true){
    if(empty($__promt_for_command)){
        $__promt_for_command = true;
        echo "\nWelcome to the Akelos Framework Interactive Console\n\n>> ";
    }

    $command = ($join_command ? $command : '').fgets(AK_PROMT,25600);

    if(substr(trim($command,"\n\r "), -1) == '\\'){
        $command = rtrim($command, "\\\n\r");
        $join_command = true;
        echo "... ";
        continue;
    }else{
        $join_command = false;
    }

    switch (trim(strtolower($command),"\n\r\t ();")) {
        case 'exit':
        case 'die':
        fclose(AK_PROMT);
        exit;
        break;

        case '':
        echo "... ";
        break;

        case '<':
        $command = $last_command;
        echo "running command: ".$command;

        default:

        $last_command = $command;

        $_script_name = array_shift(explode(' ',trim($command).' '));
        
        $_script_file_name = AK_OS == 'WINDOWS' ? $_script_name : AK_SCRIPT_DIR.DS.$_script_name;

        if (file_exists($_script_file_name)){
            
            $command = trim(substr(trim($command),strlen($_script_name)));
                echo "\n";
                passthru((AK_OS == 'WINDOWS' ? 'php -q ':'').$_script_file_name.' '.escapeshellcmd($command));
                echo "\n>>> ";

        }else{

            ob_start();
            $parser = new AkPhpParser($command);
            echo $parser->parse() === 0 ? '' : "...";
            if(!$parser->hasErrors()){
                eval($parser->code);
            }else{
                echo "\nPHP Error: \n".join("\n", $parser->getErrors())."\n";
            }

            $result = ob_get_contents();
            ob_end_clean();

            $result = strstr($result,": eval()") ?
            strip_tags(array_shift(explode(': eval()',$result))) :
            $result;

            Ak::file_add_contents(AK_LOG_DIR.DS.'command_line.log',$promt_line.$command."\n".$result."\n");
            echo empty($result) ? $promt_line : "\n".
            (AK_RECODE_UTF8_ON_CONSOLE_TO ? Ak::recode($result, AK_RECODE_UTF8_ON_CONSOLE_TO) : $result).
            "\n\n$promt_line";
        }
        break;
    }
}
fclose(AK_PROMT);

?>