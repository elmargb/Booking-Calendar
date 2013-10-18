<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class BookingCalendar {
	
	public $start_day = '0';
	public $date_format = 'Y-m-d';
	public $show_week_num = false;
	public $active_past_dates = false;
	
	public $months_to_show = 3;
	
	public $statuses = array();
	
	public $root_js = '/media/bookingcalendar/js';
	public $root_theme = '/media/bookingcalendar/themes';
	
	public $theme = 'default';
	
	public $start_year;
	public $start_month;
	
	public $item_id = 0;
	public $status = '';
	
	public $ajax_url = '';
	
	public $custom_class = '';
	
	public $show_legend = true;
	
	private $_week_days = array(0 =>'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday');
	private $_months = array('01' => 'january', '02' => 'february', '03'=> 'march', '04' => 'april', '05' => 'may', '06' => 'june', '07' => 'july', '08' => 'august', '09' => 'september', '10' => 'october', '11' => 'november', '12' => 'december');
	
	function __construct( $config = array()) {

		$this->start_year =	date('Y');	# current year
		$this->start_month	= date('m');	# current month
		
		foreach($config as $key => $property)
			if(property_exists($this, $key))
				$this->{$key} = $property;
	}
	
	function display() {
	
		$layoutPath = JPATH_ROOT . $this->root_theme . DIRECTORY_SEPARATOR . $this->theme . DIRECTORY_SEPARATOR . 'layouts';
		
		$this->_scripts();
		
		$html_skeleton = $html_main = $html_controls = $html_statuses = '';
		
		$layout_main = new JLayoutFile('main', $layoutPath);
		$html_main = $layout_main->render( array(
			'months_to_show' => $this->months_to_show,
			'year' => $this->start_year,
			'month' => $this->start_month,
			'root_js' => $this->root_js,
			'root_theme' => $this->root_theme,
			'theme' => $this->theme
			)
		);
		
		$layout_controls = new JLayoutFile('controls', $layoutPath);
		$html_controls = $layout_controls->render( array(
			'months_to_show' => $this->months_to_show,
			'year' => $this->start_year,
			'month' => $this->start_month,
			'root_theme' => $this->root_theme,
			'theme' => $this->theme
			)
		);
		
		if ($this->show_legend) {
			$layout_legend = new JLayoutFile('legend', $layoutPath);
			$html_legend = $layout_legend->render( array(
				'root_theme' => $this->root_theme,
				'theme' => $this->theme,
				'statuses' => $this->statuses
				)
			);
		}
		
		$layout_skeleton = new JLayoutFile('skeleton', $layoutPath);
		
		echo $layout_skeleton->render( array(
			'html_main' => $html_main,
			'html_controls' => $html_controls,
			'html_legend' => $html_legend
			)
		);
	}
	
	function populate($month, $year, $booked_days = null) {
	
		if (!(isset($booked_days)))
			$booked_days = $this->_getBookedDays($month, $year);
		
		$month=sprintf("%02s",$month);
		//	define vars
		$today_timestamp	=   mktime(0,0,0,date('m'),date('d'),date('Y'));	# 	current timestamp - used to check if date is in past
		$this_month 		= 	getDate(mktime(0, 0, 0, $month, 1, $year));		# 	convert month to timestamp
		$first_week_day 	= $this_month["wday"];								# 	define first weekday (0-6)  
		$days_in_this_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);	#	define number of days in week
		$day_counter_tot	=	0; #	count total number of days showin INCLUDING previous and next months - use to get 6th row of dates
		
		$list_day_titles = '';
		$list_days = '';
		
		//	get num days in previous month - used to add dates to "empty" cells
		$month_last	= $month-1;
		$year_last	= $year;
		if($month_last<1){
			$month_last=12;
			$year_last=$year-1;	
		}
		$days_in_last_month = cal_days_in_month(CAL_GREGORIAN,$month_last,$year_last);
	
		//	day column titles - using first letter of each day
		if($this->show_week_num)
			$list_day_titles='<li class="weeknum_spacer"></li>';
		
		if($this->start_day == 0){
			for($k=0; $k<7; $k++){
				$weekday = mb_substr(JText::_($this->_week_days[$k]),0,1,'UTF-8');
				$list_day_titles.='<li class="cal_weekday"> '.$weekday.'</li>';
			}
		}else{
			if ($first_week_day == 0)	$first_week_day =7;
			for($k=1; $k<=7; $k++){
				$weekday = mb_substr(JText::_($this->_week_days[$k == 7 ? 0 : $k]),0,1,'UTF-8');
				$list_day_titles.='<li title="'.JText::_($this->_week_days[$k == 7 ? 0 : $k]).'"> '.$weekday.'</li>';
			}
		}
		
		//	Fill the first week of the month with the appropriate number of blanks.       
		$j=1;
		
		if($this->start_day== 0)
			$first_week_day_start = $first_week_day;	# start sunday
		else
			$first_week_day	= $first_week_day-1;	# start monday
		
		$row_counter=0;
		
		if($first_week_day!=7){
			if($this->show_week_num)	$list_days.='<li class="weeknum">-</li>';
			$last_month_start_num=$days_in_last_month-$first_week_day+1;
			for($week_day = 0; $week_day < $first_week_day; $week_day++){
				$list_days.='<li class="cal_empty">'.$last_month_start_num.'</li>';   
				++$last_month_start_num;
				++$j;
				++$day_counter_tot;
				
				if($day_counter_tot % 7==1) ++$row_counter;
			}
		}
		$week_day=$j;
		$last_week_num = 0;
		//	loop through days (til max in month) to draw calendar
		for($day_counter = 1; $day_counter <= $days_in_this_month; $day_counter++){
			$day_classes 	=	"";
			$day_title_status =	" - ".JText::_("BOOKING_CALENDAR_AVAILABLE");
			
			//	format date for db modifying - the date is passed via ajax
			$date_db		=	$year."-".sprintf("%02s",$month)."-".sprintf("%02s",$day_counter);
	        
			//	check if day is available
			if(array_key_exists($date_db,$booked_days)) {
				list($day_classes, $day_title_status) = $this->_getBookedDayInfos($date_db,$booked_days);
			}
			
			//	set all dates to clickable for now.... need to control this for admin OR for user side booking		
			$day_classes.=' clickable';
			
			//	turn date into timestamp for comparison with current timestamp (defined above)
			$date_timestamp =   mktime(0,0,0, $month,($day_counter),$year);
			
			//	get week number
			$week_num=date("W",$date_timestamp);
			if($week_num!=$last_week_num){
				//	new week
				//$list_days .= '<li>-</li>';
			}
			//	highlight current day
			if($date_timestamp==$today_timestamp)	$day_classes.=' today';
			
			
	        //	format date for display only
	        $date_format = date($this->date_format, mktime(0, 0, 0, $month, $day_counter, $year));
						
			//	check if date is past			
			if( $date_timestamp<$today_timestamp){
				$day_classes.=" past";	#add "past" class to be modified via mootools if required
				//	overwrite clickable state if CLICKABLE_PAST is off
				if(!($this->active_past_dates)){
					//	date is previous - strip out "clickable" from classes
					$day_classes=str_replace(' clickable','',$day_classes);
				}
			}
			
			//	add weekend class - used in javascript to alter class or set opacity
			$getdate=getdate($date_timestamp);
			$day_num=$getdate["wday"]+1;
			if ($day_num % 7 == 1)		$day_classes.=' weekend';
			elseif ($day_num % 6 == 1)	$day_classes.=' weekend';
			
			$list_days .= '
			<li class="'.$day_classes.' "  id="date_'.$date_db.'" title="'.$date_format.$day_title_status.'" data-date="'.$date_format.'">'.$day_counter.'</li>';
			
			//	reset weekday counter if 7 (6)
			$week_day %= 7;			#	reset weekday to 0
			++$week_day;			#	increase weekday counter
			++$day_counter_tot;		#	add 1 to total days shown
			//echo "<br>".$week_day;
			if($this->show_week_num){
				if ($week_day==1) $list_days .= '<li class="weeknum">'.$week_num.'</li>';
			}
			$last_week_num=$week_num;
			if($day_counter_tot % 7==1) ++$row_counter;
		}
		//	add empty days till end of row
		$next_month_day=1;
	
		while($row_counter<6){
			//add days until it does :)
			for($till_day = $week_day; $till_day <=7; $till_day++){
				$list_days .= '<li class="cal_empty">'.$next_month_day.'</li>'; 
				++$next_month_day;  
				++$day_counter_tot;		#	add 1 to total days shown
				
			if($day_counter_tot % 7==1) ++$row_counter;
			}
			$week_day=1;
	
		}
		//	add empty dates (with next month numbers) until we get to 7
		if($week_day > 1){
			for($till_day = $week_day; $till_day <=7; $till_day++){
				$list_days .= '<li class="cal_empty">'.$next_month_day.'</li>'; 
				++$next_month_day;  
				++$day_counter_tot;		#	add 1 to total days shown
			}
		}
		
		
		//	put it all together (parent div defined in parent file)
		$the_cal='
		<div id="'.$month.'_'.$year.'" class="cal_title">'.JText::_($this->_months[$month]).' '.$year.'</div>
		<ul class="cal_weekday">
			'.$list_day_titles.'
		</ul>
		<ul>
			'.$list_days.'
		</ul>
		<div class="clear"></div>
		';
		return $the_cal;
	}

	protected function _getBookedDays($month, $year) {
		
		$booked_days = array();
		
		/* the booked days array must have this format :
		 * $booked_days[Y-m-d]['status'] = 'booked'
		 * [Y-m-d] -> The date of the day : 2013-10-15
		 * ['booked'] -> value of the status. Can be what you want. It will be used in the title of the day and as css class.
		 *  
		*/ 
		
		// this sets the 1st day of the $month and $year as 'booked'.
		/*
		$booked_date = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
		$booked_days[$booked_date]['status'] = 'booked';
		*/
		
		return $booked_days;
	}
	
	protected function _getBookedDayInfos($date_db, $booked_days) {
		$booking_status = $booked_days[$date_db]["status"];
		
		if (isset($this->statuses[$booking_status])) {
			$day_classes = $this->statuses[$booking_status];
			$day_title_status = " - ".JText::_($this->statuses[$booking_status]);
		} else {
			$day_classes = $booking_status;
			$day_title_status = " - ".JText::_($booking_status);
		}

		return array($day_classes, $day_title_status);
	}
	
	protected function _scripts() {
		JHtml::script(Juri::root() . $this->root_js . '/bookingcalendar.js');
		JHtml::stylesheet(Juri::root() . $this->root_theme . '/' . $this->theme . '/css/style.css');

		$document = JFactory::getDocument();
 
 		$script_declaration = '
			var bookingcalendar_ajax_url = "' . $this->ajax_url . '";
			var bookingcalendar_img_loading_month = "' . $this->root_theme . '/' . $this->theme . '/images/ajax-loader-month.gif";	
			var bookingcalendar_months_to_show = ' . $this->months_to_show . ';
			var bookingcalendar_clickable_past = ' . ($this->active_past_dates  ? 'true' : 'false') . ';
			var bookingcalendar_item_id = ' . $this->item_id . ';
			var bookingcalendar_status = "' . $this->status . '";
		';

		// Add Javascript
		$document->addScriptDeclaration($script_declaration);
	}
}