<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2007, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
* @package ActionView
* @subpackage Helpers
* @author Bermi Ferrer <bermi a.t akelos c.om>
* @copyright Copyright (c) 2002-2007, Akelos Media, S.L. http://www.akelos.org
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
*/

/**
* Returns an HTML calendar. In its simplest form, this method generates a plain
* calendar (which can then be customized using CSS) for a given month and year.
* However, this may be customized in a variety of ways -- changing the default CSS
* classes, generating the individual day entries yourself, and so on.
*
* The following options will default to current month and year:
*   'year'  # The 4 digit year number to show the calendar for.
*   'month' # The month number to show the calendar for.
* 
* The following are optional, available for customizing the default behavior:
*
*   'table_class'       => "calendar"        # The class for the <table> tag.
*   'month_name_class'  => "monthName"       # The class for the name of the month, at the top of the table.
*   'other_month_class' => "otherMonth"      # The class for the name for other months, at the top of the table.
*   'show_previous_month_days' => true       # Show previous month days in the calendar.
*   'show_next_month_days'  => true          # Show next month days in the calendar.
*   'abbrev'                => 1             # This option specifies how the day names should be abbreviated.
*                                             Use 3 for the first three letters, 1 for the first, and
*                                             false for the entire name. Defaults to 1.
*   'first_day_of_week' => 0                 # Renders calendar starting on Sunday. Use 1 for Monday, and so on. Defaults to current locale.
*   'weekends' => array(0,6)                 # An array containing weekend days, where 0 is Sunday, 2 Tuesday...
*   'accessible'        => false             # Turns on accessibility mode. This suffixes dates within the
*                                            # calendar that are outside the range defined in the <caption> with
*                                            # <span class="hidden">MonthName</span>
*                                            # You'll need to define an appropriate style in order to make this disappear.
*                                            # Choose your own method of hiding content appropriately.
*
*   'show_today'        => true             # Highlights today on the calendar using the CSS class 'today'.
*   'block'             => true             # Whether or not month_days() should return the result or behave a block. 
*   'previous'          => ''               # HTML to insert before the month name in the table caption. Useful for calendar navigation.
*   'next'              => ''               # HTML to insert after the month name in the table caption. Useful for calendar navigation.
*   'cell_id'           => ''               # An id prefix that will be added to every cell id attribute like <td id='day-2007-10-14' > if you set 'cell_id' to 'day'
*                                             this is really useful for capturing clicked dates on Ajax calendars. 
*   'month_names'       => array()          # An array of months days with their index as month number. 
*                                            By default this names are taken from app/locales/localize/date/ current locale.
*   'day_names'         => array()          # An array of day names. By default day names are taken from app/locales/localize/date/ current locale.
* 
*
* Example usage:
*   <%= calendar :year => 2005, :month => 6 %> # This prints the simplest possible calendar.
*   <%= calendar :year => 2005, :month => 6, :table_class => "calendar_helper" %> # This generates a calendar, as
*                                                                                 # before, but the <table>'s class
*                                                                                 # is set to "calendar_helper".
*   <%= calendar :year => 2005, :month => 6, :abbrev => false %>  # This generates a simple calendar but shows the
*                                                                 # entire day name ("Sunday", "Monday", etc.) instead
*                                                                 # of only the first three letters.
*   <? while($calendar_helper->month_days(array('month' => 10, 'year' => 2007), $d)) :  # PHP block without printing cell contents until the last pass
*       if(in_array($d->day, $list_of_special_days)){                                   # it will add the specialDay class to the days in the 
*         $d->cell_attributes = array('class' => 'specialDay');                         # $list_of_special_days array
*       }
*   endwhile; ?>
*   ?>
*
*   <? while($calendar_helper->month_days(array('month' => 10, 'year' => 2007), $d)) :  # PHP block capturing loop content as the cell content
*       if(in_array($d->day, $list_of_special_days)) : ?>                               # this allows you to print linked days and generate
*       <%= link_to d.day, :controller => 'events', :day => d.day %>                    # arbitrary HTML for a given day.
*   <? endif; endwhile; ?>
*
* An additional 'weekend' class is applied to weekend days.
*
* For consistency with the themes provided in the calendar_styles generator, use "specialDay" as the CSS class for marked days.
*
*/
class CalendarHelper extends AkActionViewHelper
{
    function calendar($options)
    {
        $result = true;
        $options['block'] = false;
        while($result === true){
            $result = $this->month_days($options, $d);
        }
        return $result;
    }

    function month_days($options = array(), &$block)
    {
        // This will only happen on initiating the block
        if(!isset($block) || isset($block->_ended)){
            $block = new stdClass();
            $default_options  = array(
            'year' => Ak::getDate(null,'Y'),
            'month' => Ak::getDate(null,'n'),
            'table_class' => 'calendar',
            'month_name_class' => 'monthName',
            'other_month_class' => 'otherMonth',
            'abbrev' => 1,
            'show_previous_month_days' => true,
            'show_next_month_days' => true,
            'weekends' => array(0,6),
            'first_day_of_week' => Ak::locale('first_day_of_week'),
            'accessible' => false,
            'show_today' => true,
            'block' => true,
            'previous' => '',
            'next' => '',
            'cell_id' => '',
            'month_names' => array_combine(range(1,12), Ak::toArray(Ak::t('January,February,March,April,May,June,July,August,September,October,November,December', null, 'localize/date'))),
            'day_names' => Ak::toArray(Ak::t('Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday', null, 'localize/date'))
            );

            $block->_options = $options = array_merge($default_options, $options);

            $block->output = '';

            $block->Month =& new CalendarMonth($options);
            $block->_current_day = 1;
            $block->_days = array();

        }


        if($block->_current_day <= $block->Month->number_of_days){ // block iteration
            $Day =& $block->Month->days[$block->_current_day];
            $Day->name = $block->_options['day_names'][$Day->week_day];

            $Day->cell_attributes = array();
            $Day->url_options = array();
            $Day->url = array();
            $Day->text = $Day->day;


            $block->day =& $Day->day;
            $block->name =& $Day->name;
            $block->cell_attributes =& $Day->cell_attributes;
            $block->url_options =& $Day->url_options;
            $block->url =& $Day->url;
            $block->text =& $Day->text;

            if(!empty($block->_options['cell_id'])){
                $block->cell_attributes['id'] = $block->_options['cell_id'].'_'.$Day->year.'-'.$Day->month.'-'.$Day->day;
            }

            $block->cell_attributes['class'] = (in_array($Day->week_day, $block->_options['weekends']) ? 'weekendDay' : '').($block->_options['show_today'] && $Day->is_today ? ' today' : '');

            $block->_days[$block->_current_day] =& $Day;

            if($block->_options['block'] && isset($block->_days[($block->_current_day)-1])){
                $block->_days[($block->_current_day)-1]->content = trim(ob_get_clean());
            }

            if(!empty($block->_days[($block->_current_day)-1]->content) && empty($block->skip_special_days)){
                $block->_days[($block->_current_day)-1]->cell_attributes['class'] .= ' specialDay';
            }

            $block->_options['block'] ? ob_start() : null;

            $block->_current_day++;

            return true;
        }else{

            $block->output = empty($block->_options['table_header']) ? $this->_renderMonthTableHeader($block->_options) : $block->_options['table_header'];

            $block->_options['block'] ? $block->_days[($block->_current_day)-1]->content = trim(ob_get_clean()) : null;

            $block->output .=  '<tr>'.$this->_indentNext(1);
            $this->_getPreviousMonthDaysInCalendar($block);
            $this->_getCurrentMonthDaysInCalendar($block);
            $this->_getNextMonthDaysInCalendar($block);

        }

        $block->output .= '</tr>'.$this->_indentNext(-1).'</tbody>'.$this->_indentNext(-1).'</table>';

        $block->_ended = true;
        if(!$block->_options['block']){
            return $block->output;
        }else{
            echo $block->output;
        }
    }

    function _renderMonthTableHeader($options = array())
    {
        $output = '<table class="'.$options['table_class'].'" border="0" cellspacing="0" cellpadding="0">'.$this->_indentNext(1);
        $output .= '<caption class="'.$options['month_name_class'].'">'.$options['previous'].$options['month_names'][$options['month']].$options['next'].'</caption>'.$this->_indentNext(0).
        '<thead>'.$this->_indentNext(1).'<tr>'.$this->_indentNext(1);

        if(!empty($options['first_day_of_week'])){
            for($i = $options['first_day_of_week']; $i > 0; $i--){
                array_push($options['day_names'], array_shift($options['day_names']));
            }
        }

        foreach ($options['day_names'] as $k => $day_name){
            $abbreviated = $options['abbrev'] && $options['abbrev'] > 0 ? substr($day_name, 0, $options['abbrev']) : false;
            if($abbreviated){
                $output .= "<th scope='col'><abbr title='$day_name'>$abbreviated</abbr></th>";
            }else{
                $output .= "<th scope='col'>$day_name</th>";
            }
            $output .= $this->_indentNext($k == 6 ? -1 : 0);
        }
        $output .= '</tr>'.$this->_indentNext(-1).'</thead>'.$this->_indentNext(0).'<tbody>'.$this->_indentNext(1);

        return $output;
    }

    function _getLastDayOfWeek($options = array())
    {
        return $options['first_day_of_week'] > 0 ? $options['first_day_of_week']-1 : 6;
    }

    function _getCurrentMonthDaysInCalendar(&$block)
    {
        $last_day_of_week = $this->_getLastDayOfWeek($block->_options);
        foreach ($block->_days as $day){
            $block->output .= $this->_controller->tag_helper->content_tag('td', (empty($day->content) ? $day->text : $day->content), array_diff($day->cell_attributes, array('')));
            $block->output .= $day->week_day == $last_day_of_week ? $this->_indentNext(-1).'</tr>'.$this->_indentNext(0).'<tr>'.$this->_indentNext(1) : $this->_indentNext(0);
        }
    }

    function _renderOtherMonthDayCell($Day, $options = array(), $show = true)
    {
        return '<td class="'.$options['other_month_class'].(in_array($Day->week_day, $options['weekends']) ? ' weekendDay' : '').'">'.($show ?
        ($options['accessible'] ? $Day->day.'<span class="hidden"> '. $options['month_names'][$Day->month].'</span>' : $Day->day) : '').'</td>';
    }

    function _getPreviousMonthDaysInCalendar(&$block)
    {
        if($block->Month->starts_on > $block->_options['first_day_of_week']){
            $previous_month = ($block->Month->month == 1 ? 12 : $block->Month->month-1);
            $PreviousMonth =& $this->_loadMonthAndDays(array_merge($block->_options, array('month' => $previous_month, 'year' => $block->Month->year - ($previous_month == 12 ? 1 : 0))));
            for($i = $PreviousMonth->number_of_days-$block->Month->starts_on+1+$block->_options['first_day_of_week']; $i <= $PreviousMonth->number_of_days; $i++){
                $block->output .= $this->_renderOtherMonthDayCell($PreviousMonth->days[$i], $block->_options, $block->_options['show_previous_month_days']).$this->_indentNext(0);
            }
        }
    }

    function _getNextMonthDaysInCalendar(&$block)
    {
        $last_day_of_week = $this->_getLastDayOfWeek($block->_options);
        if($block->Month->ends_on != $this->_getLastDayOfWeek($block->_options)){
            $next_month = ($block->Month->month == 12 ? 1 : $block->Month->month+1);
            $NextMonth =& $this->_loadMonthAndDays(array_merge($block->_options, array('month' => $next_month, 'year' => $block->Month->year + ($next_month == 1 ? 1 : 0))));
            foreach (range(1,($this->_beginningOfWeek($NextMonth->days[7], $block->_options['first_day_of_week']) - 1)) as $i){
                $block->output .= $this->_renderOtherMonthDayCell($NextMonth->days[$i], $block->_options, $block->_options['show_next_month_days']).$this->_indentNext($NextMonth->days[$i]->week_day == $last_day_of_week ? -1 : 0);
            }
        }
    }

    function &_loadMonthAndDays($options)
    {
        $Month =& new CalendarMonth($options);
        $Month->loadDays();
        return $Month;
    }

    function _beginningOfWeek($day, $start = 1)
    {
        return $day->day - $this->_daysBetween($start, $day->week_day);
    }

    function _daysBetween($first, $second)
    {
        return ($first > $second) ? ($second + (7 - $first)) : ($second - $first);
    }
    
    function _indentNext($indent = 0)
    {
        $indent = $this->_last_indent = (!isset($this->_last_indent) ? 1 : ($this->_last_indent+$indent));
        return "\n".str_repeat('  ', $indent);
    }

}


class CalendarYear
{
    function CalendarYear($options = array())
    {
        $this->setOptions($options);
    }

    function setOptions($options = array())
    {
        $this->year = empty($options['year']) ? Ak::getDate(null,'Y') : $options['year'];
        $this->is_leap = CalendarCalculations::isLeapYear($this->year);
        if(!isset($options['load_months']) || $options['load_months']){
            $this->loadMonths(!isset($options['load_days']) || $options['load_days']);
        }
    }

    function getMonths()
    {
        $this->loadMonths();
        return $this->months;
    }

    function loadMonths($load_days = true)
    {
        if(empty($this->months)){
            $this->months = array();
            for ($i = 1; $i <= 12; $i++){
                $this->months[$i] = new CalendarMonth(array('year'=>$this->year, 'month'=>$i, 'load_days' => $load_days));
            }
        }
    }

}

class CalendarMonth
{
    function CalendarMonth($options = array())
    {
        $this->setOptions($options);
    }

    function setOptions($options = array())
    {
        $this->year = empty($options['year']) ? Ak::getDate(null,'Y') : $options['year'];
        $this->month = empty($options['month']) ? Ak::getDate(null,'n') : $options['month'];
        $this->starts_on = CalendarCalculations::getDayOnTheWeek($this->year, $this->month, 1);
        $this->first_day_of_week = !isset($options['first_day_of_week']) ? Ak::locale('first_day_of_week') : $options['first_day_of_week'];
        $this->number_of_days = $this->getNumberOfDaysForMonth();
        $this->ends_on = CalendarCalculations::getDayOnTheWeek($this->year, $this->month, $this->number_of_days);
        $this->last_month_days = $this->starts_on;
        $this->next_month_days = 6-$this->ends_on;
        $this->number_of_weeks = abs(($this->last_month_days+$this->number_of_days+$this->next_month_days)/7);
        $this->is_current_month = Ak::getDate(null, 'n') == $this->month;

        if(!isset($options['load_days']) || $options['load_days']){
            $this->loadDays();
        }
    }

    function getDays()
    {
        $this->loadDays();
        return $this->days;
    }

    function loadDays()
    {
        $this->days = array();
        for ($day = 1; $day <= $this->number_of_days; $day++){
            $this->days[$day] = new CalendarDay(array('year'=>$this->year, 'month'=>$this->month, 'day'=>$day));
        }
    }

    function getNumberOfDaysForMonth()
    {
        $days = array(31, (CalendarCalculations::isLeapYear($this->year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        return intval($days[($this->month)-1]);
    }

}

class CalendarDay
{
    function CalendarDay($options = array())
    {
        $this->setOptions($options);
    }

    function setOptions($options = array())
    {
        $this->year = empty($options['year']) ? Ak::getDate(null,'Y') : $options['year'];
        $this->month = empty($options['month']) ? Ak::getDate(null,'n') : $options['month'];
        $this->day = empty($options['day']) ? Ak::getDate(null,'j') : $options['day'];
        $this->week_day = CalendarCalculations::getDayOnTheWeek($this->year, $this->month, $this->day);
        $this->week = date('W', mktime(0,0,0,$this->month, $this->day, $this->year));
        $this->is_today = Ak::getDate(null, 'Y-n-j') == $this->year.'-'.$this->month.'-'.$this->day;
    }
}


/**
* The following class implements a Gregorian calendar algorithm based upon 
* single-digit numbers by Ken Hennacy and Richard Hennacy
* available at http://www.cs.umd.edu/~khennacy/research/cell/calendar.pdf
* 
* @author     Bermi Ferrer <bermi a.t akelos c.om> 2007
* @license    GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
*/
class CalendarCalculations
{
    /**
    * Returns the day of the week 0 Sunday, 2 Tuesday... for a given year, month and day
    */
    function getDayOnTheWeek($year, $month, $day)
    {
        return ($day + CalendarCalculations::getMonthConstant($month) + CalendarCalculations::getYearConstant($year, $month)) % 7;
    }

    /**
    * Returns an array of months in a year where certain day falls in a weekday.
    *
    * Example for getting the months which had a friday 16th on 1978:
    *  getMonthsWhereDayIsOnWeekday(1978, 16, 5) returns array(6) => June
    */
    function getMonthsWhereDayIsOnWeekday($year, $day, $weekday)
    {
        return array_keys(array(1=>6, 2=>2, 3=>2, 4=>5, 5=>0, 6=>3, 7=>5, 8=>1, 9=>4, 10=>6, 11=>2, 12=>4),
        abs(($weekday - CalendarCalculations::getDayOnTheWeek($year, null, $day) - CalendarCalculations::getYearConstant($year)) % 7) % 7);
    }

    /**
    * Determines the date associated with a given day of the week in a month.
    * 
    * For example, What is the date for the 4th Tuesday in December, 1845?
    * 
    * getDayOfTheWeekInAMonth(4, 2, 12, 1845) => 23rd 
    */
    function getDayOfTheWeekInAMonth($position, $weekday, $month, $year)
    {
        return ($position-1)*7 + (CalendarCalculations::modulo($weekday - CalendarCalculations::getMonthConstant($month) - CalendarCalculations::getYearConstant($year, $month), 7));
    }


    function isLeapYear($year)
    {
        return ($year > 0 && !($year % 4) && ($year % 100)  || !($year % 400));
    }

    function getYearConstant($year, $month = null)
    {
        $century = CalendarCalculations::getCenturyConstant(substr($year, 1, 1), substr($year, 0, 1));
        $year = substr($year, 2);
        $leap_constant = !empty($month) && $month < 3 ? (CalendarCalculations::isLeapYear($year) ? 1 : 0)  : 0;
        return  (($century + ($year%7) + ($year/4)) % 7) - $leap_constant;
    }

    function getCenturyConstant($century, $milenium)
    {
        $c = $milenium % 2 ?
        array(0 => 3, 1 => 1, 2 => 0, 3 => 5, 4 => 3, 5 => 1, 6 => 0, 7 => 5, 8 => 3, 9 => 1) :
        array(0 => 0, 1 => 5, 2 => 3, 3 => 1, 4 => 0, 5 => 5, 6 => 3, 7 => 1, 8 => 0, 9 => 5);
        return $c[$century];
    }

    function getMonthConstant($month)
    {
        $C = array(1=>6, 2=>2, 3=>2, 4=>5, 5=>0, 6=>3, 7=>5, 8=>1, 9=>4, 10=>6, 11=>2, 12=>4);
        return isset($C[$month]) ? $C[$month] : null;
    }

    /**
    * PHP modulo % returns the dividend which is not the expected result on 
    * Math operations where the divisor is expected.
    * 
    * For example PHP will return -5%7 = -5 when expected was 2
    */
    function modulo($a, $n)
    {
        $n = abs($n);
        return $n===0 ? null : $a-$n*floor($a/$n);
    }
}


/* Unit Tests for CalendarCalculations * /
assert(CalendarCalculations::getYearConstant(1926) == 5) or print 'Invalid year constant';
assert(CalendarCalculations::getYearConstant(1989) == 0) or print 'Invalid year constant';
assert(CalendarCalculations::getYearConstant(2005) == 6) or print 'Invalid year constant';
assert(CalendarCalculations::getDayOnTheWeek(1926, 6, 13) == 0) or print 'June 13, 1926 should be Sunday on getDayOnTheWeek';
assert(CalendarCalculations::getDayOnTheWeek(2004, 1, 7) == 3) or print 'January 7, 2004 should be Wednesday on getDayOnTheWeek';
assert(CalendarCalculations::getMonthsWhereDayIsOnWeekday(1789, 13, 5) == array(2,3,11)) or print 'Months in 1789 whith Fri 13th should be Feb, Mar, Nov';
assert(CalendarCalculations::getDayOfTheWeekInAMonth(4, 2, 12, 1845) == 23) or print 'The date for the 4 Tuesday in December, 1845 should be 23rd';
/**/

?>