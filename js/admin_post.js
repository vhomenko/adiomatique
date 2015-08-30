jQuery(document).ready(function(){
	window.ADI = window.ADI || {};

	var dateFormat = 'd.mm.y';
	var monthNames = ['Januar','Februar','März','April','Mai','Juni',
	'Juli','August','September','Oktober','November','Dezember'];
	var monthNamesShort = ['Jan','Feb','Mär','Apr','Mai','Jun',
	'Jul','Aug','Sep','Okt','Nov','Dez'];
	var dayNames = ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'];
	var dayNamesShort = ['So','Mo','Di','Mi','Do','Fr','Sa'];
	jQuery( "#adi_event_date" ).datepicker({
		closeText: 'Schließen',
		monthNames: monthNames,
		monthNamesShort: monthNamesShort,
		dayNames: dayNames,
		dayNamesShort: dayNamesShort,
		dayNamesMin: dayNamesShort,
		weekHeader: 'KW',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: '',
		dateFormat : dateFormat});
	if ( jQuery( '#adi_event_date' ).val() === '' ) {
		var cur = jQuery.datepicker.formatDate(dateFormat, new Date(), {
			monthNames: monthNames,
			monthNamesShort: monthNamesShort,
			dayNames: dayNames,
			dayNamesShort: dayNamesShort
		});
		jQuery( '#adi_event_date' ).val( cur );
	}
	window.ADI.toggleWeekToSkipBox = function () {
		if ( '1' !== document.getElementById( 'adi_event_periodicity' ).value ) {
			jQuery( '#adi_week_to_skip_box' ).hide();
			jQuery( '#adi_second_week_to_skip_box' ).hide();
		} else {
			jQuery( '#adi_week_to_skip_box' ).show();
			if ( '0' !== document.getElementById( 'adi_week_to_skip_box' ).value ) {
				jQuery( '#adi_second_week_to_skip_box' ).show();
			} else {
				jQuery( '#adi_second_week_to_skip_box' ).hide();
			}
		}
	}
	window.ADI.toggleSecondWeekToSkipBox = function () {
		if ( '1' !== document.getElementById( 'adi_event_periodicity' ).value ) {
			jQuery( '#adi_second_week_to_skip_box' ).hide();
			return;
		}
		var weekToSkip = document.getElementById( 'adi_event_week_to_skip' ).value;
		var secondWeekToSkip = document.getElementById( 'adi_event_second_week_to_skip' ).value;
		if ( '0' === weekToSkip ) {
			jQuery( '#adi_second_week_to_skip_box' ).hide();
		} else {
			jQuery( '#adi_second_week_to_skip_box' ).show();

			if ( weekToSkip >= secondWeekToSkip ) {
				jQuery('#adi_event_second_week_to_skip > option[value="0"]').prop("selected", true);
			}
			if ( weekToSkip > 1 ) {
				for ( var i = 2; i < 5; i++ ) {
					if ( i <= weekToSkip ) {
						jQuery('#adi_event_second_week_to_skip > option[value="' + i + '"]').attr("disabled", "disabled");
					} else {
						jQuery('#adi_event_second_week_to_skip > option[value="' + i + '"]').removeAttr("disabled");
					}
				}
			}
		}
	}
	window.ADI.toggleWeekToSkipBox();
	window.ADI.toggleSecondWeekToSkipBox();
	window.ADI.resetEventData = function () {
		jQuery( '#adi_event_time' ).val( '' );

		var cur = jQuery.datepicker.formatDate(dateFormat, new Date(), {
			monthNames: monthNames,
			monthNamesShort: monthNamesShort,
			dayNames: dayNames,
			dayNamesShort: dayNamesShort
		});
		jQuery( '#adi_event_date' ).val( cur );

		document.getElementById( 'adi_event_periodicity' ).value = '0';
		document.getElementById( 'adi_event_week_to_skip' ).value = '0';
		document.getElementById( 'adi_event_second_week_to_skip' ).value = '0';
		window.ADI.toggleWeekToSkipBox();
		document.getElementById( 'adi_event_location' ).value = '';
		document.getElementById( 'adi_event_titlepage_id' ).value = '0';
	}
	jQuery( '#adi_event_time' ).timepicker({
		defaultTime: '19:00',
		showLeadingZero: false, 
		showPeriodLabels: false,
		hourText: 'Stunden',
		minuteText: 'Min',
		showNowButton: false,
		closeButtonText: 'Schließen',
		showCloseButton: true,
		minutes: { interval: 15 }
	});
});
