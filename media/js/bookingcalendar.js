

//	load calendar for month and year
function load_calendar(el,month,year){
	jQuery.ajax({
		type: "GET",
		async: false,
		url: ajax_url,
		data: {'item_id':item_id,'month':month,'year':year},
		beforeSend: function( xhr ) {
			el.html('<img class="img_loading_month" src="'+img_loading_month+'">');	
		}
	}).done(function(data) {
		el.html( data );
		
		if(clickable_past == false){
			jQuery('#the_months .past').each(function() {
				jQuery(this).css({opacity: 0.6});
			});
		}
	});
	
}
//	make the dates clickable
function activate_dates(){
	//	 add custom events here - eg to update booking form
}

//	calendar navigation buttons
function calendar_nav(){
	jQuery('#cal_controls img').each(function() {
		//	thanks to http://davidwalsh.name/mootools-image-mouseovers 
		var src = jQuery(this).attr('src');
		jQuery(this).css('cursor','pointer');
		var extension = src.substring(src.lastIndexOf('.'),src.length);
		jQuery(this).mouseenter( function() {
			jQuery(this).attr('src',src.replace(extension,'_over' + extension));
		});
		jQuery(this).mouseleave( function() {
			jQuery(this).attr('src',src);
		});
		jQuery(this).click( function() {
			
			var type=jQuery(this).parent().attr('id');

			//	get each calendar and calculate new date
			jQuery('div.load_cal').each(function(){
				var this_date=jQuery(this).children(":first").attr('id');
				var data=this_date.split('_');
				
				//	convert to numeric
				cur_month	= parseFloat(data[0]);
				new_year	= parseFloat(data[1]);
				
				if(type=='cal_prev'){
					new_month=(cur_month-months_to_show);
					if(new_month<1){
						//	reset month and add 1 year
						new_month=(new_month+12);
						new_year=(new_year-1);
					}
				}else if(type=='cal_next'){
					new_month=(cur_month+months_to_show);
					if(new_month>12){
						//	reset month and add 1 year
						new_month=(new_month-12);
						new_year=(new_year+1);
					}
				}

				load_calendar(jQuery(this),new_month,new_year);
			});
			
			//	once drawn, make calendars clickable
			activate_dates();
		});
	});
}


jQuery.noConflict();

jQuery(document).ready( function() {
	
	//	load initial calendars
	if(jQuery('.load_cal').length){
		jQuery('.load_cal').each(function() {
			var this_date=jQuery(this).attr('id');
			var data=this_date.split('_');
			load_calendar(jQuery(this),data[0],data[1]);
		});
		
		//	once drawn, make calendars clickable
		activate_dates();
		
		//	calendar next and back buttons
		calendar_nav();
	}	
});