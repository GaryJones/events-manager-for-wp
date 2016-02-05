jQuery(document).ready(function($){
	$('.rr-events-calendar-date').datepicker();
	$('#rr-events-calendar-allday').change(function(event) {
		if ( $(this).is(":checked") ) {
			$('#rr-events-calendar-start-time').val('12:01AM').hide();
			$('#rr-events-calendar-end-time').val('11:59PM').hide();
		} else {
			$('#rr-events-calendar-start-time').val('').show();
			$('#rr-events-calendar-end-time').val('').show();
		}
	});
	if ( $('#rr-events-calendar-allday').is(":checked") ) {
		$('#rr-events-calendar-start-time').hide();
		$('#rr-events-calendar-end-time').hide();
	}
});