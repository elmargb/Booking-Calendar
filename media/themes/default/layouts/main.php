<?php defined('JPATH_BASE') or die; ?>

<div id="the_months">
	<?php
	$this_year		=	$displayData['year'];
	$this_month		=	$displayData['month'];
	?>
	<?php for($k=0; $k< $displayData['months_to_show']; ++$k): ?>
	<div id="<?php echo $this_month; ?>_<?php echo $this_year; ?>" class="cal_month load_cal"></div>
	<?php
		if($this_month==12){
			//	start new year and reset month numbers
			$this_month	=	$this_month=1;	#	set to 1
			$this_year	=	$this_year+1;	#	add 1 to current year
		}else{
			++$this_month;
		}
	?>
	<?php endfor; ?>
</div>