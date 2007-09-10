<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2007, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
* @package ActiveRecord
* @subpackage Behaviours
* @author Bermi Ferrer <bermi a.t akelos c.om>
* @copyright Copyright (c) 2002-2007, Akelos Media, S.L. http://www.akelos.org
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
*/

require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.'AkObserver.php');

/**
 * Configuration options (Taking the model Page as the target to be versioned)
 * 
 * class_name - versioned model class name (default: PageVersion)
 * table_name - versioned model table name (default: page_versions)
 * foreign_key - foreign key used to relate the versioned model to the original model (default: page_id)
 * inheritance_column - name of the column to save the model's inheritance_column value for Single Table Inheritance.  (default: versioned_type)
 * version_column - name of the column in the model that keeps the version number (default: version)
 * limit - number of revisions to keep, defaults to unlimited.
 * ignored_columns - columns that will be ignored for considering that the model needs a new version. Default updated_at
 * if - method to check in the model before saving a new version.  If this method returns false, a new version is not saved.
 * if_changed - Simple way of specifying attributes that are required to be changed before saving a model.
 *
 * == Database Schema
 *
 * The model that you're versioning needs to have a 'version' attribute. The model is versioned 
 * into a table called #{model}_versions where the model name is singlular. The _versions table should 
 * contain all the fields you want versioned, the same version column, and a {$model}_id foreign key field.
 *
 * A lock_version field is also accepted if your model uses Optimistic Locking.  If your table uses Single Table inheritance,
 * then that field is reflected in the versioned model as 'versioned_type' by default.
 * 
 * ActsAsVersioned comes prepared with the createVersionedTable method, perfect for a migration.
 * It will also create the version column if the main model does not already have it.
 */
class ActsAsVersioned extends AkObserver
{
    var $_ActiveRecordInstance;
    var $options = array();
    var $_skipVersioning = false;

    function ActsAsVersioned(&$ActiveRecordInstance)
    {
        $this->_ActiveRecordInstance =& $ActiveRecordInstance;
    }

    function init($options = array())
    {
        $success =  $this->_ensureIsActiveRecordInstance($this->_ActiveRecordInstance);
        $singularized_model_name = AkInflector::underscore(AkInflector::singularize($this->_ActiveRecordInstance->getTableName()));
        $default_options = array(
        'class_name' => $this->_ActiveRecordInstance->getModelName().'Version',
        'table_name' => $singularized_model_name.'_versions',
        'foreign_key' => $singularized_model_name.'_id',
        'inheritance_column' => 'versioned_type',
        'version_column' => 'version',
        'limit' => false,
        'if' => true,
        'if_changed' => array(),
        'ignored_columns' => array('updated_at'),
        );

        $this->options = array_merge($default_options, $options);

        $this->_ensureVersioningModelIsAvailable();

        return $success;
    }


    function _ensureIsActiveRecordInstance(&$ActiveRecordInstance)
    {
        if(is_object($ActiveRecordInstance) && method_exists($ActiveRecordInstance,'actsLike')){
            $this->_ActiveRecordInstance =& $ActiveRecordInstance;
            $this->observe(&$ActiveRecordInstance);
        }else{
            trigger_error(Ak::t('You are trying to set an object that is not an active record.'), E_USER_ERROR);
            return false;
        }
        return true;
    }

    function _ensureVersioningModelIsAvailable()
    {
        if(class_exists($this->options['class_name'])){
            return true;
        }elseif(Ak::import($this->options['class_name']) && class_exists($this->options['class_name'])){
            return true;
        }
        $this->createVersioningModel();
    }

    function createVersioningModel()
    {
        $class_name = $this->options['class_name'];
        $table_name = AkInflector::tableize($class_name);
        eval("
class $class_name extends ActiveRecord
{
    var \$_recordTimestamps = false;
    var \$_avoidTableNameValidation = true;
    function $class_name()
    {
        \$this->setModelName('$class_name');
        \$attributes = (array)func_get_args();
        \$this->setTableName('$table_name', true, true);
        \$this->init(\$attributes);
    }
    function getVersionedAttributes()
    {
        if(!empty(\$this->VersionHandler)){
            return \$this->VersionHandler->getFilteredAttributesFromVersion(parent::getAttributes());
        }
        return parent::getAttributes();
    }

    function &getPrevious()
    {
        \$Version =& \$this->_getPreviousOrNext();
        return \$Version;
    }

    function &getNext()
    {
        \$Version =& \$this->_getPreviousOrNext(false);
        return \$Version;
    }

    function &_getPreviousOrNext(\$previous = true)
    {
        \$Version = false;
        if(!empty(\$this->VersionHandler)){
            \$this->VersionHandler->load();
            \$Owner =& \$this->VersionHandler->_ActiveRecordInstance;
            \$versions =& \$Owner->versions;
            \$options =& \$this->VersionHandler->options;
            \$version_keys = array_keys(\$versions);
            if(!\$previous){
                rsort(\$version_keys);
            }
            foreach (\$version_keys as \$k){
                if(\$versions[\$k]->get(\$options['version_column']) == \$this->get(\$options['version_column'])){
                    return \$Version;
                }
                \$Version =& \$versions[\$k];
            }
        }
        return \$Version;
    }
}
");
    }

    function createVersionedTable()
    {
        require_once(AK_LIB_DIR.DS.'AkInstaller.php');
        $Installer =& new AkInstaller();

        if(!$this->_ActiveRecordInstance->hasColumn($this->options['version_column'])){
            $Installer->addColumn($this->_ActiveRecordInstance->getTableName(), $this->options['version_column'].' integer default \'1\'');
            $this->_ActiveRecordInstance->getColumns(true); // forcing column settings reloading
        }

        if(!$Installer->tableExists($this->options['table_name'])){
            $Installer->createTable($this->options['table_name'],
            'id,'.$this->_getMigrationStringForTheVersioningTable());
        }
    }
    
    function dropVersionedTable()
    {
        require_once(AK_LIB_DIR.DS.'AkInstaller.php');
        $Installer =& new AkInstaller();
        if($Installer->tableExists($this->options['table_name'])){
            $Installer->dropTable($this->options['table_name']);
        }
    }

    /**
     * Inspects for column setting on the owner model and creates a migration string for creating the *_versions table
     */
    function _getMigrationStringForTheVersioningTable()
    {
        $columns = $this->getVersionedColumnSettings();
        $migration_columns = array();
        foreach ($columns as $name => $details){
            $migration_columns[] = $name.' '.$details['type'];
        }
        return join(',', $migration_columns);
    }

    function getVersionedColumnSettings()
    {
        $columns = $this->_ActiveRecordInstance->getColumns();
        $primary_key = $this->_ActiveRecordInstance->getPrimaryKey();
        if($this->options['foreign_key'] != $primary_key){
            $columns[$primary_key]['name'] = $this->options['foreign_key'];
            $columns[$primary_key]['type'] = $columns[$primary_key]['type'] == 'serial' ? 'integer' : $columns[$primary_key]['type'];
            $columns[$primary_key]['versioned_primary_key'] = true;
            $columns[$this->options['foreign_key']] = $columns[$primary_key];
            unset($columns[$primary_key]);
        }
        return $columns;
    }

    function getVersionedAttributes()
    {
        $attributes = array();
        foreach ($this->getVersionedColumnSettings() as $name => $details){
            if(!empty($details['versioned_primary_key'])){
                $attributes[$name] = $this->_ActiveRecordInstance->get($this->_ActiveRecordInstance->getPrimaryKey());
            }else{
                $attributes[$name] = $this->_ActiveRecordInstance->get($name);
            }
        }
        return $attributes;
    }

    function getFilteredAttributesFromVersion($attributes = array())
    {
        $Versioned =& $this->getInstance();
        $filtered_attributes = array();
        unset($attributes[$Versioned->getPrimaryKey()]);
        foreach ($attributes as $column => $value){
            // Uncast the versioned so we can get properly casted fields on the original owner.
            $value = $this->_ActiveRecordInstance->castAttributeFromDatabase($column, $Versioned->castAttributeForDatabase($column, $value, false));
            if($this->options['foreign_key'] == $column){
                $filtered_attributes = array_merge(array($this->_ActiveRecordInstance->getPrimaryKey() => $value), $filtered_attributes);
            }else{
                $filtered_attributes[$column] = $value;
            }
        }
        return $filtered_attributes;
    }

    function getType()
    {
        return 'version';
    }

    function saveWithoutRevision()
    {
        $this->_skipVersioning = true;
        $success = $this->_ActiveRecordInstance->save();
        $this->_skipVersioning = false;
        return $success;
    }

    function &getLatestVersion()
    {
        $Versioned =& $this->getInstance();
        $Versioned =& $Versioned->findFirstBy($this->options['foreign_key'], $this->_ActiveRecordInstance->get($this->_ActiveRecordInstance->getPrimaryKey()), array('sort' =>$this->options['version_column'].' DESC' ));
        if($Versioned){
            $Versioned->VersionHandler =& $this;
        }
        return $Versioned;
    }

    function &getInstance()
    {
        $Versioned =& new $this->options['class_name']();
        $Versioned->VersionHandler =& $this;
        return $Versioned;
    }

    function isDifferentFromLastVersion()
    {
        return count($this->getChangedAttributes()) != 0;
    }

    function isDifferentFromVersion($version_number)
    {
        return count($this->getChangedAttributes($version_number)) != 0;
    }

    function getChangedAttributes($version_number = null)
    {
        $changed_attributes = array();
        if(empty($version_number)){
            $Version =& $this->getLatestVersion();
        }else{
            $Version =& $this->getVersion($version_number);
        }
        if($Version){
            $versioned_attributes = $Version->getVersionedAttributes();
            $owner_attributes = $this->_ActiveRecordInstance->getAttributes();
            $this->_removeIgnoredAttributes_($owner_attributes);
            foreach ($owner_attributes as $name => $value){
                if(isset($versioned_attributes[$name]) && $versioned_attributes[$name] == $value){
                    continue;
                }
                $changed_attributes[$name] = $value;
            }
        }
        return $changed_attributes;
    }

    function _removeIgnoredAttributes_(&$attributes)
    {
        if(!empty($this->options['ignored_columns'])){
            foreach ($this->options['ignored_columns'] as $name){
                if(array_key_exists($name, $attributes)){
                    unset($attributes[$name]);
                }
            }
        }
    }

    function &getVersion($version_number)
    {
        $Versioned =& $this->getInstance();
        $Versioned =& $Versioned->findFirstBy($this->options['foreign_key'].' AND '.$this->options['version_column'], $this->_ActiveRecordInstance->get($this->_ActiveRecordInstance->getPrimaryKey()), $version_number );
        if($Versioned){
            $Versioned->VersionHandler =& $this;
        }
        return $Versioned;
    }

    function getLastestVersionNumber()
    {
        $LastVersion =& $this->getLatestVersion();
        return (!$LastVersion ? $this->_ActiveRecordInstance->get($this->options['version_column']) : $LastVersion->get($this->options['version_column']));
    }

    function getNextVersionNumber()
    {
        return $this->getLastestVersionNumber()+1;
    }

    function revertToVersion($version_number)
    {
        if($this->isDifferentFromVersion($version_number) && $Version =& $this->getVersion($version_number)){

            $this->_ActiveRecordInstance->setAttributes($Version->getVersionedAttributes());
            return $this->_ActiveRecordInstance->save();

        }
        return false;
    }


    function beforeCreate(&$Object)
    {
        if(!empty($Object->versioned->_skipVersioning)){
            return true;
        }
        $Object->set($Object->versioned->options['version_column'], 1);
        return true;
    }

    function afterCreate(&$Object)
    {
        if(!empty($Object->versioned->_skipVersioning)){
            return true;
        }
        $this->createNewVersion($Object);
        return true;
    }

    function beforeUpdate(&$Object)
    {
        if(!empty($Object->versioned->_skipVersioning) || !$Object->versioned->isDifferentFromLastVersion()){
            return true;
        }
        $Object->set($Object->versioned->options['version_column'], $Object->versioned->getNextVersionNumber());
        return true;
    }

    function afterUpdate(&$Object)
    {
        if(!empty($Object->versioned->_skipVersioning)){
            return true;
        }
        $this->createNewVersion($Object);
        return true;
    }

    function createNewVersion(&$Object)
    {
        $Versioned =& $this->getInstance();
        $Versioned->setAttributes($Object->versioned->getVersionedAttributes());
        $Versioned->save();
        $Object->versions[] =& $Versioned;
        $Object->versioned->cleanupOldVersions();
    }

    function cleanupOldVersions()
    {
        if(!empty($this->options['limit'])){
            $this->load(true);
            while (($this->options['limit']) < count($this->_ActiveRecordInstance->versions)) {
                $this->_ActiveRecordInstance->versions[0]->destroy();
                array_shift($this->_ActiveRecordInstance->versions);
            }
        }
    }


    function load($force_reload = false)
    {
        if($force_reload || empty($this->_ActiveRecordInstance->versions)){
            $this->_ActiveRecordInstance->versions =& $this->find();
        }
        if(empty($this->_ActiveRecordInstance->versions)){
            $this->_ActiveRecordInstance->versions = array();
        }
    }

    function &find()
    {
        $Versioned = false;
        $VersionedInstance =& $this->getInstance();
        $finder_sql = $this->options['foreign_key'].' = '.$this->_ActiveRecordInstance->quotedId();
        $finder_order = $this->options['version_column'].' ASC';

        $args = func_get_args();
        $args = $this->_getArgumentsForFinder($args, $finder_sql, $finder_order);

        $Versioned =& Ak::call_user_func_array(array(&$VersionedInstance,'find'), $args);

        if($Versioned){
            if(is_array($Versioned)){
                foreach (array_keys($Versioned) as $k){
                    $Versioned[$k]->VersionHandler =& $this;
                }
            }else{
                $Versioned->VersionHandler =& $this;
            }
        }

        return $Versioned;
    }

    function _getArgumentsForFinder($arguments, $finder_sql, $finder_order)
    {
        $num_args = count($arguments);
        if(!empty($arguments[$num_args-1]) && is_array($arguments[$num_args-1])){
            $options_in_args = true;
            $options = $arguments[$num_args-1];
        }else{
            $options_in_args = false;
            $options = array();
        }

        if (empty($options['conditions'])) {
            $options['conditions'] = $finder_sql;
        } elseif(!empty($finder_sql) &&
        is_array($options['conditions']) &&
        !strstr($options['conditions'][0], $finder_sql)) {
            $options['conditions'][0] .= ' AND '. $finder_sql;
        } elseif (!empty($finder_sql) && !strstr($options['conditions'], $finder_sql)) {
            $options['conditions'] .= ' AND '. $finder_sql;
        }

        $options['order'] = empty($options['order']) ? $finder_order : $options['order'];

        if($options_in_args){
            $arguments[$num_args-1] = $options;
        }else{
            $arguments = empty($arguments) ? array('all') : $arguments;
            array_push($arguments, $options);
        }
        return $arguments;
    }


    function afterDestroy(&$Object)
    {
        $success = true;
        $Object->versioned->load(true);
        foreach (array_keys($Object->versions) as $k){
            $success = $Object->versions[$k]->destroy() ? $success : false;
        }
        return $success;
    }
}



?>