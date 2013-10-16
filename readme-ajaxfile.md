The Booking Calendar Library uses an Ajax call to fill the calendars with day information (booked days) on calendar navigation.

What you need to do is to set the ajax url in the BookingCalendar class with the 'ajax_url' property.

When this is done, you have to insert some php code in the file that will be called through Ajax :

<?php
/* Call the Calendar library */
jimport('bookingcalendar.calendar');

/* Get uri variables */
$app = JFactory::getApplication();
$jinput = $app->input;

$item_id = $jinput->get('item_id', 0, 'INT');
$month = $jinput->get('month', 0, 'INT');
$year = $jinput->get('year', 0, 'INT');
$status = $jinput->get('status', '', 'STRING');

/* Create calendar */
$calendar = new BookingCalendar();

/* Get Booked Days (custom function) */
$booked_days = my_booked_days($item_id, $month, $year, $status);

/* Display calendar */
echo $calendar->populate($month, $year, $booked_days);
?>
