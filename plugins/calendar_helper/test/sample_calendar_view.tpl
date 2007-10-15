<h1>Sample calendars</h1>

<? 
// A list of special days
$list_of_special_days = array(5,15,20); 
?>

<h2>Print calendar directly</h2>

<%= calendar :month => 10, :year => 2007 %>

<h2>PHP block without printing cell content</h2>
<? while($calendar_helper->month_days(array('month' => 6, 'year' => 2010), $d)) :
    if(in_array($d->day, $list_of_special_days)){
        $d->cell_attributes = array('class' => 'specialDay');
    }
endwhile; ?>

<h2>PHP block printing capturing cell content</h2>
<? while($calendar_helper->month_days(array('month' => 9, 'year' => 2006), $d)) :
    if(in_array($d->day, $list_of_special_days)) : ?>
    
    <%= link_to d.day, :controller => 'events', :day => d.day %>
    
<? endif; endwhile; ?>
