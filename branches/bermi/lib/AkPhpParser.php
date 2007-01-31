<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

require_once(AK_VENDOR_DIR.DS.'pear'.DS.'PHP'.DS.'Compat'.DS.'Constant'.DS.'T.php');


/**
 * This is a modified version of the pear/PHP_Shell package by Jan Kneschke
 * This is used for the interactive PHP shell
 * 
 * @package AkelosFramework
 * @subpackage Console
 * @author Jan Kneschke <jan@kneschke.de>
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */
class AkPhpParser
{
    var $errors = array();
    var $code = '';

    function AkPhpParser($code)
    {
        $this->code = trim($code);
    }
    /**
    * parse the PHP code
    *
    * we parse before we eval() the code to
    * - fetch fatal errors before they come up
    * - know about where we have to wait for closing braces
    *
    * @return int 0 if a executable statement is in the code-buffer, non-zero otherwise
    */
    function parse()
    {

        $this->code = trim($this->code);
        if (empty($this->code)){
            return 1;
        }

        $t = token_get_all('<?php '.$this->code.' ?>');

        $need_semicolon = 1; /* do we need a semicolon to complete the statement ? */
        $need_return = 1;    /* can we prepend a return to the eval-string ? */
        $eval = '';          /* code to be eval()'ed later */
        $braces = array();   /* to track if we need more closing braces */

        $methods = array();  /* to track duplicate methods in a class declaration */
        $ts = array();       /* tokens without whitespaces */

        foreach ($t as $ndx => $token) {
            if (is_array($token)) {
                $ignore = 0;

                switch($token[0]) {
                    case T_WHITESPACE:
                    case T_OPEN_TAG:
                    case T_CLOSE_TAG:
                    $ignore = 1;
                    break;
                    case T_FOREACH:
                    case T_DO:
                    case T_WHILE:
                    case T_FOR:

                    case T_IF:
                    case T_RETURN:

                    case T_CLASS:
                    case T_FUNCTION:
                    case T_INTERFACE:

                    case T_PRINT:
                    case T_ECHO:

                    case T_COMMENT:
                    case T_UNSET:

                    case T_INCLUDE:
                    case T_REQUIRE:
                    case T_INCLUDE_ONCE:
                    case T_REQUIRE_ONCE:
                    case T_TRY:
                    $need_return = 0;
                    break;
                    case T_VARIABLE:
                    case T_STRING:
                    case T_NEW:
                    case T_EXTENDS:
                    case T_IMPLEMENTS:
                    case T_OBJECT_OPERATOR:
                    case T_DOUBLE_COLON:
                    case T_INSTANCEOF:

                    case T_CATCH:

                    case T_ELSE:
                    case T_AS:
                    case T_LNUMBER:
                    case T_DNUMBER:
                    case T_CONSTANT_ENCAPSED_STRING:
                    case T_ENCAPSED_AND_WHITESPACE:
                    case T_CHARACTER:
                    case T_ARRAY:
                    case T_DOUBLE_ARROW:

                    case T_CONST:
                    case T_PUBLIC:
                    case T_PROTECTED:
                    case T_PRIVATE:
                    case T_ABSTRACT:
                    case T_STATIC:
                    case T_VAR:

                    case T_INC:
                    case T_DEC:
                    case T_SL:
                    case T_SL_EQUAL:
                    case T_SR:
                    case T_SR_EQUAL:

                    case T_IS_EQUAL:
                    case T_IS_IDENTICAL:
                    case T_IS_GREATER_OR_EQUAL:
                    case T_IS_SMALLER_OR_EQUAL:

                    case T_BOOLEAN_OR:
                    case T_LOGICAL_OR:
                    case T_BOOLEAN_AND:
                    case T_LOGICAL_AND:
                    case T_LOGICAL_XOR:
                    case T_MINUS_EQUAL:
                    case T_PLUS_EQUAL:
                    case T_MUL_EQUAL:
                    case T_DIV_EQUAL:
                    case T_MOD_EQUAL:
                    case T_XOR_EQUAL:
                    case T_AND_EQUAL:
                    case T_OR_EQUAL:

                    case T_FUNC_C:
                    case T_CLASS_C:
                    case T_LINE:
                    case T_FILE:

                    /* just go on */
                    break;
                    default:
                    /* debug unknown tags*/
                    error_log(sprintf("unknown tag: %d (%s): %s".PHP_EOL, $token[0], token_name($token[0]), $token[1]));

                    break;
                }
                if (!$ignore) {
                    $eval .= $token[1]." ";
                    $ts[] = array("token" => $token[0], "value" => $token[1]);
                }
            } else {
                $ts[] = array("token" => $token, "value" => '');

                $last = count($ts) - 1;

                switch ($token) {
                    case '(':
                    /* walk backwards through the tokens */

                    if ($last >= 3 &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_OBJECT_OPERATOR &&
                    $ts[$last - 3]['token'] == T_VARIABLE ) {

                        /* $object->method( */

                        /* $object has to exist and has to be a object */
                        $objname = $ts[$last - 3]['value'];

                        if (!isset($GLOBALS[ltrim($objname, '$')])) {
                            $this->addError(sprintf('Variable \'%s\' is not set', $objname));
                        }

                        $k = ltrim($objname, '$');

                        if(isset($GLOBALS[$k])){
                            $object = $GLOBALS[$k];

                            if (!is_object($object)) {
                                $this->addError(sprintf('Variable \'%s\' is not a class', $objname));
                            }

                            $method = $ts[$last - 1]['value'];

                            /* obj */

                            if (!method_exists($object, $method)) {
                                $this->addError(sprintf("Variable %s (Class '%s') doesn't have a method named '%s'",
                                $objname, get_class($object), $method));
                            }
                        }
                    } else if ($last >= 3 &&
                    $ts[$last - 1]['token'] == T_VARIABLE &&
                    $ts[$last - 2]['token'] == T_OBJECT_OPERATOR &&
                    $ts[$last - 3]['token'] == T_VARIABLE ) {

                        /* $object->$method( */

                        /* $object has to exist and has to be a object */
                        $objname = $ts[$last - 3]['value'];

                        if (!isset($GLOBALS[ltrim($objname, '$')])) {
                            $this->addError(sprintf('Variable \'%s\' is not set', $objname));
                        }
                        $object = $GLOBALS[ltrim($objname, '$')];

                        if (!is_object($object)) {
                            $this->addError(sprintf('Variable \'%s\' is not a class', $objname));
                        }

                        $methodname = $ts[$last - 1]['value'];

                        if (!isset($GLOBALS[ltrim($methodname, '$')])) {
                            $this->addError(sprintf('Variable \'%s\' is not set', $methodname));
                        }
                        $method = $GLOBALS[ltrim($methodname, '$')];

                        /* obj */

                        if (!method_exists($object, $method)) {
                            $this->addError(sprintf("Variable %s (Class '%s') doesn't have a method named '%s'",
                            $objname, get_class($object), $method));
                        }

                    } else if ($last >= 6 &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_OBJECT_OPERATOR &&
                    $ts[$last - 3]['token'] == ']' &&
                    /* might be anything as index */
                    $ts[$last - 5]['token'] == '[' &&
                    $ts[$last - 6]['token'] == T_VARIABLE ) {

                        /* $object[...]->method( */

                        /* $object has to exist and has to be a object */
                        $objname = $ts[$last - 6]['value'];

                        if (!isset($GLOBALS[ltrim($objname, '$')])) {
                            $this->addError(sprintf('Variable \'%s\' is not set', $objname));
                        }
                        $array = $GLOBALS[ltrim($objname, '$')];

                        if (!is_array($array)) {
                            $this->addError(sprintf('Variable \'%s\' is not a array', $objname));
                        }

                        $andx = $ts[$last - 4]['value'];

                        if (!isset($array[$andx])) {
                            $this->addError(sprintf('%s[\'%s\'] is not set', $objname, $andx));
                        }

                        $object = $array[$andx];

                        if (!is_object($object)) {
                            $this->addError(sprintf('Variable \'%s\' is not a class', $objname));
                        }

                        $method = $ts[$last - 1]['value'];

                        /* obj */

                        if (!method_exists($object, $method)) {
                            $this->addError(sprintf("Variable %s (Class '%s') doesn't have a method named '%s'",
                            $objname, get_class($object), $method));
                        }

                    } else if ($last >= 3 &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_DOUBLE_COLON &&
                    $ts[$last - 3]['token'] == T_STRING ) {

                        /* Class::method() */

                        /* $object has to exist and has to be a object */
                        $classname = $ts[$last - 3]['value'];

                        if (!class_exists($classname)) {
                            $this->addError(sprintf('Class \'%s\' doesn\'t exist', $classname));
                        }

                        $method = $ts[$last - 1]['value'];

                        if (!empty($method) && !in_array($method, (array)get_class_methods($classname))) {
                            $this->addError(sprintf("Class '%s' doesn't have a method named '%s'",
                            $classname, $method));
                        }
                    } else if ($last >= 3 &&
                    $ts[$last - 1]['token'] == T_VARIABLE &&
                    $ts[$last - 2]['token'] == T_DOUBLE_COLON &&
                    $ts[$last - 3]['token'] == T_STRING ) {

                        /* Class::method() */

                        /* $object has to exist and has to be a object */
                        $classname = $ts[$last - 3]['value'];

                        if (!class_exists($classname)) {
                            $this->addError(sprintf('Class \'%s\' doesn\'t exist', $classname));
                        }

                        $methodname = $ts[$last - 1]['value'];

                        if (!isset($GLOBALS[ltrim($methodname, '$')])) {
                            $this->addError(sprintf('Variable \'%s\' is not set', $methodname));
                        }
                        $method = $GLOBALS[ltrim($methodname, '$')];

                        if (!in_array($method, get_class_methods($classname))) {
                            $this->addError(sprintf("Class '%s' doesn't have a method named '%s'",
                            $classname, $method));
                        }

                    } else if ($last >= 2 &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_NEW ) {

                        /* new Class() */

                        $classname = $ts[$last - 1]['value'];

                        if (!class_exists($classname)) {
                            $this->addError(sprintf('Class \'%s\' doesn\'t exist', $classname));
                        }

                        if(AK_PHP5){
                            $r = new ReflectionClass($classname);

                            if ($r->isAbstract()) {
                                $this->addError(sprintf("Can't instantiate abstract Class '%s'", $classname));
                            }

                            if (!$r->isInstantiable()) {
                                $this->addError(sprintf('Class \'%s\' can\'t be instantiated. Is the class abstract ?', $classname));
                            }
                        }

                    } else if ($last >= 2 &&
                    $ts[0]['token'] != T_CLASS &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_FUNCTION ) {

                        /* make sure we are not a in class definition */

                        /* function a() */

                        $func = $ts[$last - 1]['value'];

                        if (function_exists($func)) {
                            $this->addError(sprintf('Function \'%s\' is already defined', $func));
                        }
                    } else if ($last >= 4 &&
                    $ts[0]['token'] == T_CLASS &&
                    $ts[1]['token'] == T_STRING &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_FUNCTION ) {

                        /* make sure we are not a in class definition */

                        /* class a { .. function a() ... } */

                        $func = $ts[$last - 1]['value'];
                        $classname = $ts[1]['value'];

                        if (isset($methods[$func])) {
                            $this->addError(sprintf("Can't redeclare method '%s' in Class '%s'", $func, $classname));
                        }

                        $methods[$func] = 1;

                    } else if ($last >= 1 &&
                    $ts[$last - 1]['token'] == T_STRING ) {
                        /* func() */
                        $funcname = $ts[$last - 1]['value'];

                        if (!function_exists($funcname)) {
                            $this->addError(sprintf("Function %s() doesn't exist", $funcname));
                        }
                    } else if ($last >= 1 &&
                    $ts[$last - 1]['token'] == T_VARIABLE ) {

                        /* $object has to exist and has to be a object */
                        $funcname = $ts[$last - 1]['value'];

                        if (!isset($GLOBALS[ltrim($funcname, '$')])) {
                            $this->addError(sprintf('Variable \'%s\' is not set', $funcname));
                        }
                        $k = ltrim($funcname, '$');

                        if(isset($GLOBALS[$k])){
                            $func = $GLOBALS[$k];

                            if (!function_exists($func)) {
                                $this->addError(sprintf("Function %s() doesn't exist", $func));
                            }
                        }

                    }

                    array_push($braces, $token);
                    break;
                    case '{':
                    $need_return = 0;

                    if ($last >= 2 &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_CLASS ) {

                        /* class name { */

                        $classname = $ts[$last - 1]['value'];

                        if (class_exists($classname)) {
                            $this->addError(sprintf("Class '%s' can't be redeclared", $classname));
                        }
                    } else if ($last >= 4 &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_EXTENDS &&
                    $ts[$last - 3]['token'] == T_STRING &&
                    $ts[$last - 4]['token'] == T_CLASS ) {

                        /* class classname extends classname { */

                        $classname = $ts[$last - 3]['value'];
                        $extendsname = $ts[$last - 1]['value'];

                        if (class_exists($classname, false)) {
                            $this->addError(sprintf("Class '%s' can't be redeclared",
                            $classname));
                        }
                        if (!class_exists($extendsname, false)) {
                            $this->addError(sprintf("Can't extend '%s' from not existing Class '%s'",
                            $classname, $extendsname));
                        }
                    } else if ($last >= 4 &&
                    $ts[$last - 1]['token'] == T_STRING &&
                    $ts[$last - 2]['token'] == T_IMPLEMENTS &&
                    $ts[$last - 3]['token'] == T_STRING &&
                    $ts[$last - 4]['token'] == T_CLASS ) {

                        /* class name implements interface { */

                        $classname = $ts[$last - 3]['value'];
                        $implements = $ts[$last - 1]['value'];

                        if (class_exists($classname, false)) {
                            $this->addError(sprintf("Class '%s' can't be redeclared",
                            $classname));
                        }
                        if (!interface_exists($implements, false)) {
                            $this->addError(sprintf("Can't implement not existing Interface '%s' for Class '%s'",
                            $implements, $classname));
                        }
                    }

                    array_push($braces, $token);
                    break;
                    case '}':
                    $need_return = 0;
                    case ')':
                    array_pop($braces);
                    break;
                }

                $eval .= $token;
            }
        }

        $last = count($ts) - 1;
        if ($last >= 2 &&
        $ts[$last - 0]['token'] == T_STRING &&
        $ts[$last - 1]['token'] == T_DOUBLE_COLON &&
        $ts[$last - 2]['token'] == T_STRING ) {

            /* Class::constant */

            /* $object has to exist and has to be a object */
            $classname = $ts[$last - 2]['value'];

            if (!class_exists($classname)) {
                $this->addError(sprintf('Class \'%s\' doesn\'t exist', $classname));
            }

            $constname = $ts[$last - 0]['value'];

            if(AK_PHP5){
                $c = new ReflectionClass($classname);
                if (!$c->hasConstant($constname)) {
                    $this->addError(sprintf("Class '%s' doesn't have a constant named '%s'",
                    $classname, $constname));
                }
            }
        } else if ($last == 0 &&
        $ts[$last - 0]['token'] == T_VARIABLE ) {

            /* $var */

            $varname = $ts[$last - 0]['value'];

            if (!isset($GLOBALS[ltrim($varname, '$')])) {
                $this->addError(sprintf('Variable \'%s\' is not set', $varname));
            }
        }


        $need_more = count($braces);

        if ($need_more || ';' === $token) {
            $need_semicolon = 0;
        }

        if ($need_return) {
            $eval = "return ".$eval;
        }

        /* add a traling ; if necessary */
        if ($need_semicolon){
            $eval .= ';';
        }

        if (!$need_more) {
            $this->code = $eval;
        }

        return $need_more;
    }

    function addError($error)
    {
        $this->errors[$error] = '';
    }

    function hasErrors()
    {
        return !empty($this->errors);
    }

    function getErrors()
    {
        return array_keys($this->errors);
    }

}

?>