<?php

error_reporting(E_ALL);
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
require_once(dirname(__FILE__).str_repeat(DS.'..', 5).DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'AkActionViewHelper.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'tag_helper.php');
require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'calendar_helper.php');

class CalendarHelperTestCase extends AkUnitTest
{

    function test_setup()
    {
        $this->calendar_helper =& new CalendarHelper();
        $this->calendar_helper->_controller =& new stdClass();
        $this->calendar_helper->_controller->tag_helper =& new TagHelper();
    }

    function test_should_display_month()
    {
        $this->assertPattern('/August/', $this->_calendarWithDefaults());
    }

    function test_should_have_default_css_classes_on_calendar_with_defaults()
    {
        foreach (array(
        'table_class' => 'calendar',
        'month_name_class' => 'monthName'
        ) as
        $key => $value){
            $this->assertPattern("/class=\"$value\"/", $this->_calendarWithDefaults());
        }
    }

    function test_custom_css_classes()
    {
        foreach (array('table_class', 'month_name_class') as $key){
            $this->assertPattern("/class=\"$key\"/", $this->_calendarWithDefaults(array($key => $key)));
        }
    }

    function test_should_differentiate_abbreviations()
    {
        $this->assertPattern('/>Mon</', $this->_calendarWithDefaults(array('abbrev' => 3)));
        $this->assertPattern('/>M</', $this->_calendarWithDefaults(array('abbrev' => 1)));
        $this->assertPattern('/>Monday</', $this->_calendarWithDefaults(array('abbrev' => false)));
    }

    function test_should_add_abbreviation()
    {
        $this->assertPattern('/<abbr title=\'Monday\'>M</', $this->_calendarWithDefaults());
    }

    function test_should_not_add_abbreviation_when_not_abreviating()
    {
        $this->assertPattern('/<th scope=\'col\'>Monday</', $this->_calendarWithDefaults(array('abbrev' => false)));
    }

    function test_first_day_of_week()
    {
        $moday_calendar = $this->_calendarWithDefaults(array('first_day_of_week' => 1));
        $sunday_calendar = $this->_calendar();
        $this->assertTrue(strpos($moday_calendar, 'Monday') < strpos($moday_calendar, 'Sunday'));
        $this->assertTrue(strpos($sunday_calendar, 'Monday') > strpos($sunday_calendar, 'Sunday'));
    }

    function test_today_should_be_marked()
    {
        $this->assertPattern('/today.+'.date('d').'/', $this->_calendarForThisMonth());
    }

    function test_today_should_not_be_marked()
    {
        $this->assertTrue(!strstr($this->_calendarForThisMonth(array('show_today' => false)), 'today'));
    }

    function _calendar($options = array())
    {
        return $this->calendar_helper->calendar($options);
    }

    function _calendarWithDefaults($options= array())
    {
        return $this->_calendar(array_merge(array('year' => 2006, 'month' => 8), $options));
    }

    function _calendarForThisMonth($options= array())
    {
        return $this->_calendar(array_merge(array('year' => date('Y'), 'month' => date('n')), $options));
    }
}

ak_test('CalendarHelperTestCase');

?>