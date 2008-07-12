<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2008, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package ActiveSupport
 * @subpackage Generators
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2008, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */


class MailerGenerator extends  AkelosGenerator
{
    var $command_values = array('class_name','(array)actions');

    function _preloadPaths()
    {
        $this->class_name = AkInflector::camelize($this->class_name);
        $this->assignVarToTemplate('class_name', $this->class_name);
        $this->actions = Ak::toArray(@$this->actions);
        $this->assignVarToTemplate('actions', $this->actions);
        $this->underscored_class_name = AkInflector::underscore($this->class_name);
        $this->model_path = 'app'.DS.'models'.DS.$this->underscored_class_name.'.php';
        $this->installer_path = 'app'.DS.'installers'.DS.$this->underscored_class_name.'_installer.php';
    }

    function hasCollisions()
    {
        $this->_preloadPaths();
        
        $this->collisions = array();

        $files = array(
        AkInflector::toModelFilename($this->class_name),
        AK_TEST_DIR.DS.'unit'.DS.'app'.DS.'models'.DS.$this->underscored_class_name.'.php',
        AK_TEST_DIR.DS.'fixtures'.DS.$this->model_path
        );
        
        foreach ($this->actions as $action){
            $files[] = AK_VIEWS_DIR.DS.AkInflector::underscore($this->class_name).DS.$action.'.tpl';
        }

        foreach ($files as $file_name){
            if(file_exists($file_name)){
                $this->collisions[] = Ak::t('%file_name file already exists',array('%file_name'=>$file_name));
            }
        }
        return count($this->collisions) > 0;
    }

    function generate()
    {
        $this->_preloadPaths();

        $this->class_name = AkInflector::camelize($this->class_name);

        $files = array(
        'mailer'=>AkInflector::toModelFilename($this->class_name),
        'unit_test'=>AK_TEST_DIR.DS.'unit'.DS.'app'.DS.'models'.DS.$this->underscored_class_name.'.php',
        'model_fixture.tpl'=>AK_TEST_DIR.DS.'fixtures'.DS.$this->model_path
        );

        foreach ($files as $template=>$file_path){
            $this->save($file_path, $this->render($template));
        }

        @Ak::make_dir(AK_VIEWS_DIR.DS.AkInflector::underscore($this->class_name));

        foreach ($this->actions as $action){
            $this->assignVarToTemplate('action', $action);
            $path = 'app'.DS.'views'.DS.AkInflector::underscore($this->class_name).DS.$action.'.tpl';
            $this->assignVarToTemplate('path', $path);
            $this->save(AK_VIEWS_DIR.DS.$path);
            $this->save(AK_TEST_DIR.DS.'fixtures'.DS.$path, $this->render('view_reference'));
        }
    }
}

?>
