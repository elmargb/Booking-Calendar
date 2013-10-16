<?php defined('JPATH_BASE') or die; ?>

<div id="key_wrapper">
	<div id="key" class="cal_month">
		<div class="cal_title"><?php echo JText::_('BOOKING_CALENDAR_LEGEND'); ?></div>
		<ul>
			<?php foreach($displayData['statuses'] as $status): ?>
				<li class="<?php echo $status; ?>" title="<?php echo JText::_($status); ?>"><span><?php echo JText::_($status); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>