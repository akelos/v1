<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package ActionView
 * @subpackage Helpers
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */


require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'tag_helper.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'form_tag_helper.php');
require_once(AK_LIB_DIR.DS.'AkInflector.php');

/**
* Provides a set of methods for working with forms and especially forms related to objects assigned to the template.
* The following is an example of a complete form for a person object that works for both creates and updates built
* with all the form helpers. The <tt>$person</tt> object was assigned by an action on the controller:
*   <form action="save_person" method="post">
*     Name:
*     <?= $form_helper->text_field("person", "name", array("size" => 20)) ?>
*
*     Password:
*     <?= $form_helper->password_field("person", "password", array("maxsize" => 20)) ?>
*
*     Single?:
*     <?= $form_helper->check_box("person", "single") ?>
*
*     Description:
*     <?= $form_helper->text_area("person", "description", array("cols" => 20)) ?>
*
*     <input type="submit" value="Save" />
*   </form>
*
* ...is the same as:
*
*   <form action="save_person" method="post">
*     Name:
*     <input type="text" id="person_name" name="person[name]"
*       size="20" value="<?= $person->name ?>" />
*
*     Password:
*     <input type="password" id="person_password" name="person[password]"
*       size="20" maxsize="20" value="<?= $person->password ?>" />
*
*     Single?:
*     <input type="checkbox" id="person_single" name="person[single]" value="1" />
*
*     Description:
*     <textarea cols="20" rows="40" id="person_description" name="person[description]">
*       <?= $person->description ?>
*     </textarea>
*
*     <input type="submit" value="Save">
*   </form>
*
* If the object name contains square brackets the id for the object will be inserted. Example:
*
*   <?= $form_helper->textfield("person[]", "name") ?> 
* 
* ...becomes:
*
*   <input type="text" id="person_<?= $person->id ?>_name" name="person[<?= $person->id ?>][name]" value="<?= $person->name ?>" />
*
* If the helper is being used to generate a repetitive sequence of similar form elements, for example in a partial
* used by render_collection_of_partials, the "index" option may come in handy. Example:
*
*   <?= $form_helper->text_field("person", "name", "index" => 1) ?>
*
* becomes
*
*   <input type="text" id="person_1_name" name="person[1][name]" value="<?= $person->name ?>" />
*
* There's also methods for helping to build form tags in $form_options, $date and $active_record
*/


class FormHelper extends AkActionViewHelper
{
    public $defaults;
    /**
     * 
      * Creates a form and a scope around a specific model object, which is then used as a base for questioning about
      * values for the fields. Examples:
      *
      *   <?php $f = $form_helper->form_for('person', $Person, array('url' => array('action' => 'update'))); ?>
      *     First name: <?= $f->text_field('first_name'); ?>
      *     Last name : <?= $f->text_field('last_name'); ?>
      *     Biography : <?= $f->text_area('biography'); ?>
      *     Admin?    : <?= $f->check_box('admin'); ?>
      *   <?= $f->end_form_tag(); ?>
      *
      * The form_for yields a form_builder object, in this example as $f, which emulates the API for the stand-alone 
      * FormHelper methods, but without the object name. So instead of <tt>$form_helper->text_field('person', 'name');</tt>,
      * you get away with <tt>$f->text_field('name');</tt>. 
      *
      * That in itself is a modest increase in comfort. The big news is that form_for allows us to more easily escape the instance
      * variable convention, so while the stand-alone approach would require <tt>$form_helper->text_field('person', 'name', array('object' => $Person));</tt> 
      * to work with local variables instead of instance ones, the form_for calls remain the same. You simply declare once with 
      * <tt>'person', $Person</tt> and all subsequent field calls save <tt>'person'</tt> and <tt>'object' => $Person</tt>.
      *
      * Also note that form_for doesn't create an exclusive scope. It's still possible to use both the stand-alone FormHelper methods
      * and methods from FormTagHelper. Example:
      *
      *   <?php $f = $form_helper->form_for('person', $Person, array('url' => array('action' => 'update'))); ?>
      *     First name: <?= $f->text_field('first_name'); ?>
      *     Last name : <?= $f->text_field('last_name'); ?>
      *     Biography : <?= $f->text_area('person', $Biography); ?>
      *     Admin?    : <?= $form_helper->check_box_tag('person[admin]', $Person->company->isAdmin()); ?>
      *   <?= $f->end_form_tag(); ?>
      *
      * Note: This also works for the methods in FormOptionHelper and DateHelper that are designed to work with an object as base.
      * Like collection_select and datetime_select.
      */
    function form_for($object_name, &$object, $options = array(), $defaults = array())
    {
        $url_for_options = $options['url'];
        $this->defaults = $defaults;
        $options['class'] = isset($defaults['sexy']) ? 'sexy' : null;
        echo $this->_controller->form_tag_helper->form_tag($url_for_options, $options);
        return $this->fields_for($object_name, $object);
    }

    /**
      * Creates a scope around a specific model object like form_for, but doesn't create the form tags themselves. This makes
      * fields_for suitable for specifying additional model objects in the same form. Example:
      *
      *   <?php $person_form = $this->form_for('person', $Person, array('url' => array('action'=>'update'))); ?>
      *     First name: <?= $person_form->text_field('first_name'); ?>
      *     Last name : <?= person_form->text_field('last_name'); ?>
      *     
      *     <?php $permission_fields = $form_helper->fields_for('permission', $Person->permission); ?>
      *       Admin?  : <?= $permission_fields->check_box('admin'); ?>
      *   <?= $person_form->end_form_tag(); ?>
      *
      * Note: This also works for the methods in FormOptionHelper and DateHelper that are designed to work with an object as base.
      * Like collection_select and datetime_select.
      */
    function fields_for($object_name, &$object)
    {
        return  new AkFormHelperBuilder($object_name, $object, $this);
    }

    function end_form_tag()
    {
        return '</form>';
    }
    /**
      * Returns an input tag of the "text" type tailored for accessing a specified attribute (identified by +column_name+) on an object
      * assigned to the template (identified by +object+). Additional options on the input tag can be passed as an
      * array with +options+.
      *
      * Examples (call, result):
      *   $form_helper->text_field("post", "title", array("size" => 20));
      *     <input type="text" id="post_title" name="post[title]" size="20" value="{post.title}" />
      */
    function text_field($object_name, $column_name = null, $tag_options = array(), $options = array())
    {
        return $this->_field('text', $object_name, $column_name, $tag_options, $options);
    }

    /**
      * Works just like text_field, but returns an input tag of the "password" type instead.
      */
    function password_field($object_name, $column_name = null, $tag_options = array(), $options = array())
    {
        return $this->_field('password', $object_name, $column_name, $tag_options, $options);
    }

    /**
      * Works just like text_field, but returns an input tag of the "hidden" type instead.
      */
    function hidden_field($object_name, $column_name = null, $tag_options = array(), $options = array())
    {
        return $this->_field('hidden', $object_name, $column_name, $tag_options, $options);
    }
        
    /**
      * Works just like text_field, but returns an input tag of the "file" type instead, which won't have a default value.
      */
    function file_field($object_name, $column_name = null, $tag_options = array(), $options = array())
    {
        return $this->_field('file', $object_name, $column_name, $tag_options, $options);
    }


    /**
      * Returns a textarea opening and closing tag set tailored for accessing a specified attribute (identified by +column_name+)
      * on an object assigned to the template (identified by +object+). Additional options on the input tag can be passed as an
      * array with +options+.
      *
      * Example (call, result):
      *   $form_helper->text_area('post', 'body', array('cols' => 20, 'rows' => 40));
      *     <textarea cols="20" rows="40" id="post_body" name="post[body]">
      *       {post.body}
      *     </textarea>
      */
    function text_area($object_name, $column_name = null, $tag_options = array(), $options = array())
    {
        return $this->_field('text_area', $object_name, $column_name, $tag_options, $options);
    }

	/**
      * Returns a radio button tag for accessing a specified attribute (identified by +column_name+) on an object
      * assigned to the template (identified by +object+). If the current value of +column_name+ is +tag_value+ the
      * radio button will be checked. Additional options on the input tag can be passed as an
      * array with +options+.
      * Example (call, result). Imagine that $Post->category() returns "PHP":
      *   $form_helper->radio_button("post", "category", "PHP");
      *   $form_helper->radio_button("post", "category", "Ruby");
      *     <input type="radio" id="post_category" name="post[category]" value="PHP" checked="checked" />
      *     <input type="radio" id="post_category" name="post[category]" value="Ruby" />
      */
    function radio_button($object_name, $column_name = null, $tag_value, $tag_options = array(), $options = array())
    {
        $options = array_merge(array('tag_value' => $tag_value), $options);
        return $this->_field('radio_button', $object_name, $column_name, $tag_options, $options);
    }

    /**
      * Returns a checkbox tag tailored for accessing a specified attribute (identified by +column_name+) on an object
      * assigned to the template (identified by +object+). It's intended that +column_name+ returns an integer and if that
      * integer is above zero, then the checkbox is checked. Additional options on the input tag can be passed as an
      * array with +options+. The +checked_value+ defaults to 1 while the default +unchecked_value+
      * is set to 0 which is convenient for boolean values. Usually unchecked checkboxes don't post anything.
      * We work around this problem by adding a hidden value with the same name as the checkbox.
      *
      * Example (call, result). Imagine that $Post->validate() returns 1:
      *   $form_helper->check_box("post", "validate");
      *     <input type="checkbox" id="post_validate" name="post[validate]" value="1" checked="checked" />
      *     <input name="post[validated]" type="hidden" value="0" />
      *
      * Example (call, result). Imagine that $Puppy->gooddog() returns no:
      *   $form_helper->check_box("puppy", "gooddog", array(), "yes", "no");
      *     <input type="checkbox" id="puppy_gooddog" name="puppy[gooddog]" value="yes" />
      *     <input name="puppy[gooddog]" type="hidden" value="no" />
      */
    function check_box($object_name, $column_name = null, $tag_options = array(), $options = array())
    {
        $options = array_merge(array('checked_value' => '1', 'unchecked_value' => '0'), $options);
        return $this->_field('check_box', $object_name, $column_name, $tag_options, $options);
    }

    

    /**
      * Returns a submit tag.
      */
    function submit_button($value, $tag_options = array(), $options = array())
    {
        $button = TagHelper::tag('input', array_merge(array('type' => 'submit', 'value' => $value), $tag_options));
        $secondary = isset($options['secondary']) ? TagHelper::content_tag('span', $options['secondary'], array('class' => 'secondary')) : '';
        return TagHelper::content_tag('div', $secondary.$button, array('class' => 'submit_field'));
    }
	
    /**
      * Returns a box with some html content that helps users in the form.
      */
    function help_box($help_content)
    {
        return TagHelper::content_tag('small', $help_content, array('class' => 'help_box'));
    }

    /**
      * Returns a box with a error message.
      */
    function error_box($model, $column_name)
    {
		if($model && $model->getErrorsOn($column_name)){
			$errors = $model->getErrorsOn($column_name);						
			if(is_array($errors)){
				$errors_list = '';
				foreach ($errors as $error) {
				    $errors_list .= TagHelper::content_tag('li', $error);
				}	
			}else{
				$errors_list = TagHelper::content_tag('li', $errors);
			}			
			$errors_content = TagHelper::content_tag('ul', $errors_list);			
	        return TagHelper::content_tag('div', $errors_content, array('class' => 'error_box'));
		}else {
			return '';
		}
    }

    /**
    * File field auxiliar function
    * @access private
    */
    private function _field($type, $object_name, $column_name = null, $tag_options = array(), $options = array())
    {   
        // If we provide only one attribute plus type
        if(!$column_name && $column_name != null && isset($this->object_name)){
            $column_name = $object_name;
            $object_name = $this->object_name;
		// If we skip object_name 
        }elseif(!empty($this->template) && ($column_name == null || is_array($column_name)) && isset($this->object_name)){
			$options     = $tag_options;
			$tag_options = $column_name;            
            $column_name = $object_name;
			$object_name = $this->object_name;            
        }
        
        $object = null;
        if(isset($tag_options['object'])){
            if(is_object($tag_options['object'])){
                $object =& $tag_options['object'];
                if(empty($this->_remove_object_from_options)){
                    unset($tag_options['object']);
                }
            }
        }
        if(empty($object) && !empty($this->object)){
            $object =& $this->object;
        }
        
        $InstanceTag = new AkFormHelperInstanceTag($object_name, $column_name, $this, null, $object);
                
		switch ($type) {
            case 'file':
            case 'hidden':
            case 'password':
            case 'text':
                $tag_options['class'] =  $this->_set_input_class($tag_options, 'text');
                $field = $InstanceTag->to_input_field_tag($type, $tag_options);
                break;
            case 'text_area':
                $field =  $InstanceTag->to_text_area_tag($tag_options);
                break;
            case 'radio_button':
                $tag_options['class'] =  $this->_set_input_class($tag_options, 'radio_button');
                $field =  $InstanceTag->to_radio_button_tag($options['tag_value'], $tag_options);
                break;
            case 'check_box':
                $tag_options['class'] =  $this->_set_input_class($tag_options, 'check_box');
                $field =  $InstanceTag->to_check_box_tag($tag_options, $options['checked_value'], $options['unchecked_value']);
                break;
            default:
                break;
        }
		
		// If sexy form is activated, output all the boxes
		return  isset($this->defaults['sexy']) ?  $this->_render_sexy_form($options, $field, $object, $column_name, $InstanceTag) : $field;
    }
	
	/**
     * Returns a sexy form with wrapping div, labels and information
     * @param array $options 
	 * @param array $field
     * @return string full field
     */
	private function _render_sexy_form($options, $field, $object, $column_name, $InstanceTag){
		$label     = empty($options['label']) && empty($this->defaults['label']) ? '' : $InstanceTag->to_label_field_tag($options);
						
		$help_box  = empty($options['help_box']) ? '' : $this->help_box($options['help_box']);
		$error_box = $this->error_box($object, $column_name);
		
        $class = $object->getErrorsOn($column_name) ? 'error field' : 'field';
        
		return TagHelper::content_tag('div', $label.$help_box.$field.$error_box, array('class' => $class));
	}
	
	/**
     * Sets classes to inputs
     * @param string $class 
	 * @param string $value
     * @return string new class
     */
	private function _set_input_class($options, $value){
	    if(isset($this->defaults['sexy'])){
            return empty($options['class']) ? $value : $value.' '.$options['class'];
        }else{
            return $class;
        }
	}
}

class AkFormHelperInstanceTag extends TagHelper
{
    var $default_field_options = array('size'=>30);
    var $default_radio_options = array();
    var $default_text_area_options = array('cols'=>40,'rows'=>20);
    var $default_date_options = array('discard_type'=>true);
    var $_column_name;
    var $_object_name;
    var $_auto_index;


    //AkFormHelperInstanceTag

    function AkFormHelperInstanceTag($object_name, $column_name, &$template_object, $local_binding = null, $object = null)
    {
        $this->object_name = $object_name;
        $this->_column_name = $column_name;
        $this->_template_object =& $template_object;
        $this->_local_binding = $local_binding;

        if(empty($object) && !empty($this->_template_object->_controller->{$this->object_name})){
            $this->object =& $this->_template_object->_controller->{$this->object_name};            
        }else{
            $this->object =& $object;
        }

        $_object_name = preg_replace('/\[\]$/','',$this->object_name);
        if($_object_name != $this->object_name){
            $this->_auto_index = $this->_template_object->{AkInflector::camelize($_object_name)}->id_before_type_cast;
        }
    }
    
    /**
     * Returns a label with proper for attribute
     * @param array $options 
     * @return string tag
     */
    public function to_label_field_tag($options = array())
    {   
		$label = (isset($options['label']) && is_string($options['label'])) ? $options['label']: AkInflector::humanize($this->_column_name);
		
        $required = (!empty($options['required']) && $options['required'] == true) ? TagHelper::content_tag('span', '*', array('class' => 'required')) : '';
        $question_mark = empty($options['help_box']) ? '' : TagHelper::content_tag('a', '[?]', array('href'=>'#', 'title'=>'Need more help about this field?', 'class'=>'question_mark'));

		return TagHelper::content_tag('label', $required.$label.$question_mark, array('for' => $this->tag_id()));
    }
    
    function to_input_field_tag($field_type, $options = array())
    {
        $options['size'] = !empty($options['size']) ? $options['size'] : (!empty($options['maxlength']) ? $options['maxlength'] : $this->default_field_options['size']);
        $options = array_merge($this->default_field_options,$options);
        if($field_type == 'hidden'){
            unset($options['size']);
        }
        if($field_type != 'file'){
            $options['value'] = isset($options['value']) ? $options['value'] : $this->value_before_type_cast();
        }
        $options['type'] = $field_type;
        $this->add_default_name_and_id($options);
        return TagHelper::tag('input', $options);
    }

    function to_radio_button_tag($tag_value, $options = array())
    {
        $options = array_merge($this->default_radio_options,$options);
        $options['type'] = 'radio';
        $options['value'] = $tag_value;
        if($this->getValue() == $tag_value){
            $options['checked'] = 'checked';
        }

        $pretty_tag_value = strtolower(preg_replace('/\W/', '', preg_replace('/\s/', '_',$tag_value)));
        $options['id'] = $this->_auto_index ?
        "{$this->object_name}_{$this->_auto_index}_{$this->_column_name}_{$pretty_tag_value}" :
        "{$this->object_name}_{$this->_column_name}_{$pretty_tag_value}";
        $this->add_default_name_and_id($options);
        return TagHelper::tag('input', $options);
    }

    function to_text_area_tag($options = array())
    {
        $options = array_merge($this->default_text_area_options,$options);
        $this->add_default_name_and_id($options);
        return TagHelper::content_tag('textarea', TagHelper::escape_once($this->value_before_type_cast()), $options);
    }

    function to_check_box_tag($options = array(), $checked_value = '1', $unchecked_value = '0')
    {
        $options['type'] = 'checkbox';
        $options['value'] = $checked_value;
        $value = $this->getValue();
        if (is_numeric($value)){
            $checked = $value != 0;
        }elseif (is_string($value)){
            $checked = $value == $checked_value;
        }else{
            $checked = !empty($value);
        }

        if($checked || isset($options['checked']) && $options['checked'] == 'checked'){
            $options['checked'] = 'checked';
        }else{
            unset($options['checked']);
        }
        $this->add_default_name_and_id($options);
        return TagHelper::tag('input', array('name' => $options['name'], 'type' => 'hidden', 'value' => $unchecked_value)).TagHelper::tag('input', $options);
    }

    function to_date_tag()
    {
        require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'date_helper.php');
        $defaults = $this->default_date_options;
        $date = $this->getValue();
        $date = !empty($date) ? $date : Ak::getDate();
        return DateHelper::select_day($date, array_merge($defaults,array('prefix'=>"{$this->object_name}[{$this->_column_name}(3)]"))) .
        DateHelper::select_month($date, array_merge($defaults,array('prefix'=>"{$this->object_name}[{$this->_column_name}(2)]"))) .
        DateHelper::select_year($date, array_merge($defaults,array('prefix'=>"{$this->object_name}[{$this->_column_name}(1)]")));
    }
    
    function to_date_select_tag($options = array())
    {
        require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'date_helper.php');
        $DateHelper =& new DateHelper();
        $object_name = empty($this->_object_name) ? $this->object_name : $this->_object_name;
        if(isset($this->object)){
            $DateHelper->_object[$object_name] =& $this->object;
        }
        return $DateHelper->date_select($object_name, $this->_column_name, $options);          
    }
    
    function to_datetime_select_tag($options = array())
    {
        require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'date_helper.php');
        $DateHelper =& new DateHelper();
        $object_name = empty($this->_object_name) ? $this->object_name : $this->_object_name;
        if(isset($this->object)){
            $DateHelper->_object[$object_name] =& $this->object;
        }        
        return $DateHelper->datetime_select($object_name, $this->_column_name, $options);
    }

    function to_boolean_select_tag($options = array())
    {
        $this->add_default_name_and_id($options);
        return '<select'.
        TagHelper::_tag_options($options).
        '><option value="false"'.
        ($this->getValue() == false ? ' selected' : '').
        '>'.Ak::t('False',array(),'helpers/form').'</option><option value="true"'.
        ($this->getValue() ? ' selected' : '').
        '>'.Ak::t('True',array(),'helpers/form').'</option></select>';
    }

    function to_content_tag($tag_name, $options = array())
    {
        return TagHelper::content_tag($tag_name, $this->getValue(), $options);
    }

    function &getObject($object_name = null)
    {
        if(!empty($this->object)){
            return $this->object;
        }elseif (!empty($this->_template_object->{$this->object_name})){
            return $this->_template_object->{$this->object_name};
        }
        if(!empty($object_name) && !empty($this->_object[$object_name])){
            return $this->_object[$object_name];
        }
        return $this->object;
    }

    function getValue()
    {
        $object = $this->getObject();
        if(!empty($object)){
            return $object->get($this->_column_name);
        }
    }

    function value_before_type_cast()
    {
        $object =& $this->getObject();
        if(!empty($object)){
            return !empty($object->{$this->_column_name.'_before_type_cast'}) ?
            $object->{$this->_column_name.'_before_type_cast'} :
            $object->get($this->_column_name);
        }
    }

    function add_default_name_and_id(&$options)
    {
        if(isset($options['index'])){
            $options['name'] = empty($options['name']) ? $this->tag_name_with_index($options['index']) : $options['name'];
            $options['id'] = empty($options['id']) ? $this->tag_id_with_index($options['index']) : $options['id'];
            unset($options['index']);
        }elseif(!empty($this->_auto_index)){
            $options['name'] = empty($options['name'])? $this->tag_name_with_index($this->_auto_index) : $options['name'];
            $options['id'] = empty($options['id']) ? $this->tag_id_with_index($this->_auto_index) : $options['id'];
        }else{
            $options['name'] = empty($options['name']) ? $this->tag_name() : $options['name'];
            $options['id'] = empty($options['id']) ? $this->tag_id() : $options['id'];
        }
        
        if(!empty($options['multiple'])){
            if(substr($options['name'],-2) != '[]'){
                $options['name'] = $options['name'].'[]';
            }
        }
    }

    function tag_name()
    {
        return "{$this->object_name}[{$this->_column_name}]";
    }

    function tag_name_with_index($index)
    {
        return "{$this->object_name}[{$index}][{$this->_column_name}]";
    }

    function tag_id()
    {
        return "{$this->object_name}_{$this->_column_name}";
    }

    function tag_id_with_index($index)
    {
        return "{$this->object_name}_{$index}_{$this->_column_name}";
    }
}

class AkFormHelperBuilder extends FormHelper
{
    function AkFormHelperBuilder($object_name, &$object, &$template)
    {
        $this->object_name = $object_name;
        $this->object =& $object;
        $this->template =& $template;
        $this->proccessing = $object_name;
        $this->template->_remove_object_from_options = true;
        $this->defaults = $template->defaults;
    }
}

?>