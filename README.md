Booking-Calendar
================

Booking Calendar Joomla! Library

This library lets you display a calendar filled with a custom # of months. For each month, you can set which days are "booked".

The booking source can be what you want. You can code your own way to retrieve booked days as soon as you follow the required structure for the booked days array (check the getBookedDays() method).  

For the calendar navigation, you just need to provide an 'item_id' (Joomla current displayed article id as example) and customize the ajax url to call the right page that will retrieve the booked days for this item id for the previous r next months and display the new infos.

Some options :

- Number of months to show
- Legend with different statuses
- custom css class
- custom themes

The library uses the Joomla! 3.x layouts feature for its theme template files:
http://docs.joomla.org/J3.1:Sharing_layouts_across_views_or_extensions_with_JLayout

The themes are stored in 'media/bookingcalendar/themes' and the default theme is named 'default'.
To manage your own theme :
 - copy the default folder to a new one.
 - change the 'theme' class property with the name of the folder you've jsut created.
 - customize the template files located in the 'layouts' theme folder :
 	- skeleton.php -> it is the container for other templates
 	- main.php -> loads the calendars
 	- controls.php -> loads the navigation bar
 	- legend.php -> loads the legend
 	
Loading a calendar with default theme, 3 months displayed from current one and lengend displayed :

<?php
jimport('bookingcalendar.calendar');

$calendar = new BookingCalendar( array(
	'item_id' => $my_item_id
	)
);

$calendar->display();
?>

Loading a calendar with 'custom' theme, 12 months displayed from january of the current year and legend hidden :

<?php
jimport('bookingcalendar.calendar');

$calendar = new BookingCalendar( array(
	'item_id' => $my_item_id,
	'theme' => 'custom',
	'months_to_show' => 12,
	'start_month' => '01',
	'show_legend' => false
	)
);

$calendar->display();
?>
