jQuery( function($) {


	$('#reshedule_button').on('click',function(){
		$('#yith_auction_settings').block({message:null, overlayCSS:{background:"#fff",opacity:.6}});
		var post_data = {
			'id': object.id,
			security: object.reschedule_product,
			action: 'yith_wcact_reshedule_product'
		};

		$.ajax({
			type    : "POST",
			data    : post_data,
			url     : object.ajaxurl,
			success : function ( response ) {
				$('#yith_auction_settings').unblock();
				$('#reshedule_button').hide();
				$('#yith-reshedule-notice-admin').show();
				$('#_stock_status').val('instock');
				//window.location.reload();
				// On Success
			},
			complete: function () {
			}
		});
	});

	$(document).on('click','.yith-wcact-delete-bid',function(e){
		e.preventDefault();
		$('#yith-wcgpf-auction-bid-list').block({message:null, overlayCSS:{background:"#fff",opacity:.6}});

		if(window.confirm(object.confirm_delete_bid)){
			var post_data = {
				'user_id': $(this).data('user-id'),
				'product_id': $(this).data('product-id'),
				'date' : $(this).data('date-time'),
				'bid': $(this).data('bid'),
				'delete_id': $(this).data('delete-id'),
				'security': object.delete_bid,
				action: 'yith_wcact_delete_customer_bid'
			};

			$.ajax({
				type    : "POST",
				data    : post_data,
				url     : object.ajaxurl,
				success : function ( response ) {
					current_target          = $( e.target );
					parent                  = current_target.closest( '.yith-wcact-row' );
					parent.remove();
					$('#yith-wcgpf-auction-bid-list').unblock();
				},
				complete: function () {
				}
			});

		} else {
			$('#yith-wcgpf-auction-bid-list').unblock();
		}

	})


	$(document.body).on('yith_wcact_send_winner_email_button',function () {

        $('#yith-wcact-send-winner-email').on('click',function(){
            $('.yith-wcact-admin-auction-status').block({message:null, overlayCSS:{background:"#fff",opacity:.6}});
            var post_data = {
                'id': object.id,
                //security: object.search_post_nonce,
                action: 'yith_wcact_resend_winner_email'
            };

            $.ajax({
                type    : "POST",
                data    : post_data,
                url     : object.ajaxurl,
                success : function ( response ) {
                    $('.yith-wcact-admin-auction-status').empty();
                    $('.yith-wcact-admin-auction-status').html( response['resend_winner_email'] );
                    $(document.body).trigger('yith_wcact_send_winner_email_button');
                    $('.yith-wcact-admin-auction-status').unblock();
                },
                complete: function () {
                }
            });
        });
    });


	$(document.body).trigger('yith_wcact_send_winner_email_button');

	$( document ).find( '._tax_status_field' ).closest( 'div' ).addClass( 'show_if_auction' );

	$( 'select#product-type' ).trigger('change');


	/* == Change auction by default == */

	if( object.auction_by_default  ) {
		$("#product-type").val('auction').trigger('change');
		$(".yith_Auction_options a").trigger('click');
	}

	/* == Validation on auction save == */
	var productType = $("#product-type");
	var canSubmit = false;
	var error_field = [];
	isAuction   = function(){
		return 'auction' === productType.val();
	};
	dataValidation = function() {
		 var nodisplayMessage = true;
		$('.ywcact-data-validation').each( function (  ) {

			var data_validation = $(this).data('validation');
			var field_name = $(this).data('title-field');

			switch ( data_validation ) {
				case 'has_value':
					if(!$(this).val()) {
						$( this ).addClass( "ywcact-data-validation-error" );
						error_field.push( field_name );
						nodisplayMessage = false;

					}
				break;
				case 'has_dependencies':
					var dependency = $(this).data('dependency');
					var dependency_value =  $(this).data('value');
					if( $(dependency).val() === dependency_value && !$(this).val() ) {
						$( this ).addClass( "ywcact-data-validation-error" );
						error_field.push( field_name );
						nodisplayMessage = false;
					}

				break;
			}
		} );

		return nodisplayMessage;
	};
	checkForFields = function() {
		var checkfields = false;
		var button_clicked = $("input[type=submit][ywcact-clicked=true]").attr("id");

		if ('publish' === button_clicked ) {

			var original_post_status = $('#original_post_status').val();
			var post_status = $('#post_status').val();

			if( 'publish' === original_post_status && 'publish' === post_status ) {
				checkfields = true;
			} else if( 'draft' === original_post_status || 'auto-draft' === original_post_status  ) {
				checkfields = true;
			}

		}


		return checkfields;

	};
	notice_reset = function() {
		$('.ywcact-notice').remove();
		$('.ywcact-data-validation-error').removeClass('ywcact-data-validation-error');
		error_field = [];
	};

	$('#post').on('submit', function(e){

		if ( isAuction() && !canSubmit && checkForFields() ){
			notice_reset();
			if ( dataValidation() ){
				canSubmit = true;
				$('#publish').trigger('click');
			} else {
				e.preventDefault();

				var original_message =  object.error_validation;
				var error_count = error_field.length;
				var error_message = '';
				var i = 0;
				error_field.forEach(function(valor, indice, array) {
					console.log(valor);
					var sep = ( ++i === error_count ) ? '' : ', ';
					error_message = error_message+valor+sep;
				});
				console.log(error_message);

				var message = original_message.replace("%s", error_message );

				var message_block = "<div class='ywcact-notice ywcact-notice-error'><p><span class=\"dashicons dashicons-warning\"></span><span class='ywcact-notice-error-message'>"+message+"</span></p></div>";
				$(message_block).insertBefore($('div.wrap #poststuff'));
			}
		}
	});

	$("#post input[type=submit]").click(function() {
		$("input[type=submit]", $(this).parents("form")).removeAttr("ywcact-clicked");
		$(this).attr("ywcact-clicked", "true");
	});

	/**
	 *
	 * Start Js for General settings options
	 *
	 */

	$( "input[name='yith_wcact_settings_automatic_bid_type']" ).on('click',function() {

		switch ( this.value ) {

			case 'simple' :
				$('.ywcact-automatic-bid-increment-simple').removeClass('ywcact-hide');
				$('.ywcact-automatic-bid-increment-simple').addClass('ywcact-show');
				$('.ywcact-automatic-bid-increment-advanced').removeClass('ywcact-show');
				$('.ywcact-automatic-bid-increment-advanced').addClass('ywcact-hide');
				break;

			case 'advanced' :
				$('.ywcact-automatic-bid-increment-simple').removeClass('ywcact-show');
				$('.ywcact-automatic-bid-increment-simple').addClass('ywcact-hide');
				$('.ywcact-automatic-bid-increment-advanced').removeClass('ywcact-hide');
				$('.ywcact-automatic-bid-increment-advanced').addClass('ywcact-show');
				break;


		}
	});

	$('.ywcact-add-rule').on('click',function (e) {

		let row = $( '.ywcact-automatic-bid-increment-advanced-rule:last' ).clone().css( {'display': 'none'} );
		let numItems = $('.ywcact-bid-increment-row').length;
		row.find( 'input' ).val( '' );
		row.insertBefore( $( ".ywcact-automatic-bid-increment-advanced-end" ) );
		row.removeClass('ywcact-hide');
		row.addClass('ywcact-bid-increment-row');
		$(row).find('.ywcact-remove-rule').bind("click", function() {
			row.remove();
			reassign_id();
		});

		let inputs = $( row ).find( "input" );
		let actualinput = parseInt(numItems) - 1;

		$(inputs).each(function (i) {
			let data_type = $(this).data('input-type');
			$(this).attr('name','ywcact_automatic_bid_advanced['+actualinput+']['+data_type+']');
		});

		let end = $( ".ywcact-automatic-bid-increment-advanced-end" );
		let inputs_end = $( end ).find( "input" );
		$(inputs_end).each(function (i) {
			let data_type = $(this).data('input-type');
			$(this).attr('name','ywcact_automatic_bid_advanced['+numItems+']['+data_type+']');
		});


		row.fadeTo(
			400,
			1,
			function () {
				row.css( {'display': 'block'} );
			}
		);
	});

	$('.ywcact-remove-rule').on('click',function () {

		let row = $(this).closest('.ywcact-automatic-bid-increment-advanced-rule');
		row.remove();
		reassign_id();

	});

	function reassign_id() {
		$('.ywcact-bid-increment-row').each(function (j) {

			let inputs = $( this ).find( "input" );

			$(inputs).each(function (i) {
				let data_type = $(this).data('input-type');
				$(this).attr('name','ywcact_automatic_bid_advanced['+j+']['+data_type+']');
			});
		});
	}

	/* == General settings Privacy dependencies == */

	$('#yith_wcact_settings_tab_auction_allow_subscribe').on('change',function() {
		if( 'no' === $(this).val() && 'yes' === $('#yith_wcact_show_privacy_field').val() ) {
			$('.yith_wcact_privacy_fields').closest('.yith-plugin-fw-panel-wc-row').hide();
		} else if( 'yes' === $(this).val() ) {
			$('#yith_wcact_show_privacy_field').trigger('change');
		}
	} );


	/* == Prevent WooCommerce warning for changes without saving. ==*/
	$(document).on('click','#yith-auction-list-table #search-submit, #yith-auction-list-table #post-query-submit', function(e){
		window.onbeforeunload = null;
	});

	/* == Display background color FFF8F2 on auction scheduled == */
	$('.yith-auction-non-start').each(function (  ) {
		$(this).parents('tr').css("background-color",'#FFF8F2');
	})
	/* == Display popup table list bids on auction list panel == */
	$(document).on('click','.yith-wcact-auction-bidders-button', function(e){
		e.preventDefault();
		var post_data = {
			'product_id': $(this).data('product_id'),
			'bidders_count' : $(this).data('bids'),
			action: 'yith_wcact_load_bidders_table',
			security: object.display_bids
		};

		$.ajax({
				   type    : "POST",
				   data    : post_data,
				   url     : object.ajaxurl,
				   success : function ( response ) {
					   yith.ui.modal(
						   {
							   title  : response.title,
							   content: response.content,
							   width: 800,
							   classes : {
								   'wrap' : 'yith-wcact-list-bids-wrapper-modal',
								   'main' : 'yith-wcact-main-list-bids-wrapper-modal',
								   'content' : 'yith-wcact-content-list-bids-wrapper-modal',
							   },
						   }
					   );
				   },
				   complete: function () {
				   }
			   });
	});

	/* == Pagination on popup table list bids on auction list panel == */
	$(document).on('click','.yith-wcact-pagination-section .page-numbers',function ( e ) {
		e.preventDefault();
		const contentModal = $( '.yith-wcact-content-list-bids-wrapper-modal' );
		const currentPage = contentModal.find('.yith-wcact-pagination-section').data('current-page');
		var new_page = 1;
		contentModal.addClass( 'loading' );
		contentModal.block( {
								message   : null,
								overlayCSS: {
									background: '#fff url(' + object.loader + ') no-repeat center',
									opacity   : 0.5,
									cursor    : 'none'
								}
							});

		if( $(this).hasClass('next')) {
			 new_page = currentPage + 1;
		} else if( $(this).hasClass('prev') ) {
			 new_page = currentPage - 1;
		} else {
			new_page = $(this).text();
		}
		var post_data = {
			'product_id': $('#yith-wcact-product-id').val(),
			'bidders_count' : $('#yith-wcact-bids-count').val(),
			'current_page' : new_page,
			action: 'yith_wcact_load_bidders_table',
			security: object.display_bids
		};
		$.ajax({
				   type    : "POST",
				   data    : post_data,
				   url     : object.ajaxurl,
				   success : function ( response ) {
				   	  if( response ) {
				   	  	contentModal.empty();
				   	  	contentModal.append( response.content );
					  }
					  contentModal.unblock();
				   },
				   complete: function () {
				   }
			   });


	});

	/*
	 * Handle auction payment Stripe Options
	 */
	
	function auction_payment_dependencies() {


		var stripe_Enabled = object.stripe_enabled;

		var dependencies = {
			charge_automatically: $('.yith-wcact-deps-charge-automatically'),
			winner_create_order : $('.yith-wcact-deps-winner-create-order'),
		}
		
		if( stripe_Enabled ) {
			//yith_wcact_stripe_charge_automatically_price and yith_wcact_verify_payment_method must be true in order to hide elements.
			$( '#yith_wcact_stripe_charge_automatically_price' ).on( 'change', function() {
				handle_dependencies( dependencies.charge_automatically, 'yes' === $(this).val() && 'yes' === $('#yith_wcact_verify_payment_method').val() );
			} ).trigger('change');
			//yith_wcact_stripe_charge_automatically_price and yith_wcact_verify_payment_method must be true in order to hide elements.
			$( '#yith_wcact_verify_payment_method' ).on( 'change', function() {
				handle_dependencies( dependencies.charge_automatically, 'yes' === $(this).val() && 'yes' === $('#yith_wcact_stripe_charge_automatically_price').val() );
			} ).trigger('change');

			
			$( '#yith_wcact_auction_winner_create_order' ).on( 'change', function() {
				handle_dependencies( dependencies.winner_create_order, 'yes' === $(this).val() );
			} ).trigger('change');
			
		} else {
			$( '#yith_wcact_auction_winner_create_order' ).on( 'change', function() {
				handle_dependencies( dependencies.winner_create_order, 'yes' === $(this).val() );
			} ).trigger('change');

			$( '#yith_wcact_auction_reschedule_how_to_not_paid_stripe').closest('.yith-plugin-fw-panel-wc-row').hide(); // Hide option if yith stripe is not enabled.
		}
	 }

	 function handle_dependencies( dependencies, condition ) {
			dependencies.each(function( index ) {
				if ( condition ) {
					$( this ).closest('.yith-plugin-fw-panel-wc-row').hide();
				} else {
					$( this ).closest('.yith-plugin-fw-panel-wc-row').show();
				}
			});
	 }


	auction_payment_dependencies();

	/**
	 *
	 * END Js for General settings options
	 *
	 */

	

});
