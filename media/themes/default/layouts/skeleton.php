<?php defined('JPATH_BASE') or die; ?>

<div class="booking-calendar <?php echo $displayData['custom_class']; ?>">
	<?php echo $displayData['html_controls']; ?>
	
	<?php echo $displayData['html_main']; ?>
	
	<?php if ($displayData['html_statuses'] != ''): ?>
	<?php echo $displayData['html_statuses']; ?>
	<?php endif; ?>
	
	<div style="clear:both;"></div>
</div>