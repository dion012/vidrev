( function ( $ ) {

	if ( typeof yith_wcmv_vacation === 'undefined' ) {
		return false;
	}

	// Date Picker
	$( document.body )
		.on( 'wcmv-init-datepickers', function () {
			let vacation_start_date = $( '#vacation_schedule_from' ),
				vacation_end_date 	= $( '#vacation_schedule_to' ),
				args 				= {
					dateFormat: yith_wcmv_vacation.dateFormat,
					numberOfMonths: 1,
					showButtonPanel: false,
					showAnim: false,
					minDate: 0,
					beforeShow: function ( input, instance ) {
						instance.dpDiv.addClass( 'yith-plugin-fw-datepicker-div' );
					}
				};

			vacation_start_date.datepicker( $.extend( args, {
				onClose: function( selectedDate, instance ) {
					instance.dpDiv.removeClass( 'yith-plugin-fw-datepicker-div' );

					vacation_end_date.datepicker(
						'option',
						'minDate',
						selectedDate
					);
				}
			} ) );


			vacation_end_date.datepicker( $.extend( args, {
				onClose: function( selectedDate, instance ) {
					instance.dpDiv.removeClass( 'yith-plugin-fw-datepicker-div' );

					vacation_start_date.datepicker(
						'option',
						'maxDate',
						selectedDate
					);
				}
			} ) );
		} )
		.trigger( 'wcmv-init-datepickers' );

	$( document ).on( 'change', '#vacation_enabled', function() {
		let row 		= $( '#vacation_schedule' ).closest( 'tr' ),
			row_trigger = $( '#vacation_schedule_enabled' );

		if ( ! $(this).is(':checked') ) {
			row.hide();
		} else {
			row_trigger.change();
		}
	});


} )( jQuery );
