<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package ActiveRecord
 * @subpackage Base
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

require_once(AK_LIB_DIR.DS.'Ak.php');
require_once(AK_LIB_DIR.DS.'AkInflector.php');
require_once(AK_LIB_DIR.DS.'AkActiveRecord.php');

/**
* Observer classes respond to life-cycle callbacks to implement trigger-like 
* behavior outside the original class. This is a great way to reduce the clutter
* that normally comes when the model class is burdened with functionality that
* doesn't pertain to the core responsibility of the class. 
* 
* Example:
* 
*     class CommentObserver extends AkObserver
*     {
*         public function afterSave($comment)
*         {
*             Ak::mail("admin@example.com", "New comment was posted",
*                     $comment->toString());
*         }
*     }
* 
* This Observer sends an email when a Comment::save is finished.
* 
* ## Observing a class that can't be inferred
* 
* Observers will by default be mapped to the class with which they share a name.
* So CommentObserver will be tied to observing Comment, ProductManagerObserver
* to ProductManager, and so on. If you want to name your observer differently
* than the class you're interested in observing, you can use the
* AkActiveRecord->observe() class method:
* 
*     public function afterUpdate(&$account)
*     {
*         $AuditTrail = new AuditTrail($account, "UPDATED");
*         $AuditTrail->save();
*     }
* 
* If the audit observer needs to watch more than one kind of object, this can be
* specified with multiple arguments:
* 
*     public function afterUpdate(&$record)
*     {
*         $ObservedRecord = new AuditTrail($record, "UPDATED");
*         $ObservedRecord->save();
*     }
* 
* The AuditObserver will now act on both updates to Account and Balance by
* treating them both as records.
* 
* ## Available callback methods
* 
* The observer can implement callback methods for each of these methods:
* beforeCreate, beforeValidation, beforeValidationOnCreate, beforeSave,
* afterValidation, afterValidationOnCreate, afterCreate and afterSave
* 
* ## Triggering Observers
* 
* In order to activate an observer, you need to call create an Observer instance
* and attach it to a model. 
* 
* In the Akelos Framework, this can be done in controllers using the short-hand
* of for example: 
* 
*     $ComentObserverInstance = new CommentObserver();
*     $Model->addObserver(&$ComentObserverInstance);
*
*/
class AkObserver extends AkObject
{
    /**
     * Models in this array will automatically be observed
     * 
     * Example:
     * 
     * var $observe = array('Person','Car');
     * 
     * @var array
     */
    public $observe = array();
    /**
    * $_observing array of models that we're observing
    */
    public $_observing = array();

    public function __construct()
    {
        $num_args = func_num_args();
        for ($i = 0; $i < $num_args; $i++){
            $target = func_get_arg($i);
            if(is_object($target)){
                $this->observe(&$target);
            }else{
                $this->setObservedModels($target);
            }
        }
        $this->_initModelObserver();
    }
    
    /**
     * adds itself to the models which are listed
     * in var $observe = array(...)
     *
     */
    public function _initModelObserver()
    {
        
        $this->observe = Ak::toArray($this->observe);
        if (count($this->observe)>0) {
            $this->setObservedModels($this->observe);
        }
        
    }
    
    /**
    * Constructs the Observer
    * @param $subject the name or names of the Models to observe
    */
    public function observe (&$target)
    {
        static $memo;
        $model_name = $target->getModelName();
        $class_name = get_class($this);
        if(empty($memo[$class_name]) || !in_array($model_name, $memo[$class_name])){
            $memo[$class_name][] = $model_name;
            $this->_observing[] = $model_name;
            $target->addObserver(&$this);
        }
    }
    
    /**
    * Constructs the Observer
    * @param $subject the name or names of the Models to observe
    */
    public function setObservedModels ()
    {        
        $args = func_get_args();
        $models = func_num_args() == 1 ? ( is_array($args[0]) ? $args[0] : array($args[0]) ) : $args;

        foreach ($models as $class_name)
        {   
            /**
            * @todo use Ak::import() instead.
            */
            $class_name = AkInflector::camelize($class_name);
            if (!class_exists($class_name)){
                require_once(AkInflector::toModelFilename($class_name));
            }
            $model = new $class_name();
            $this->observe(&$model);
        }
    }
    

    public function update($state = '')
    {
    }

}

?>
