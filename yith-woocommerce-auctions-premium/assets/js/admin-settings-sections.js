jQuery( function($) {

    var ywcactFieldsVisibility = {
        showPrefix        : '.ywcact_show_if_',

        conditions        : {
            buy_now                 : 'buy_now',
            overtime                : 'overtime',
            overtime_set            : 'overtime_set',
            fee                     : 'fee',
            ask_fee                 : 'ask_fee',
            reschedule              : 'reschedule',
            reschedule_no_bids      : 'reschedule_without_bids',
            reschedule_no_reserve   : 'reschedule_reserve_price',
            simple_rule             : 'simple',
            advanced_rule           : 'advanced',
            bid_up                  : 'bid_up',
            bid_up_set              : 'bid_up_set',
            auction_normal          : 'auction_normal',
            commission_fee          : 'commission_fee',
            apply_commission_fee    : 'apply_commission_fee',
        },
        dom               : {
            buyNowOff               : $( '#_yith_auction_buy_now_onoff' ),
            oVertime                : $( '#_yith_auction_overtime_onoff' ),
            oVertime_Set            : $( '#_yith_auction_overtime_set_onoff' ),
            Fee                     : $( '#_yith_auction_fee_onoff' ),
            Ask_Fee                 : $( '#_yith_auction_fee_ask_onoff' ),
            rEschedule              : $( '#_yith_auction_reschedule_onoff' ),
            rEschedule_no_bids      : $( '#_yith_auction_reschedule_closed_without_bids_onoff' ),
            rEschedule_no_reserve   : $( '#_yith_auction_reschedule_reserve_no_reached_onoff' ),
            bId_type                : $( '#_yith_wcact_bid_type_radio' ),
            bId_type_set            : $( '#_yith_wcact_bid_type_set_radio' ),
            bId_type_OnOff          : $( '#_yith_auction_bid_type_onoff' ),
            aUction_type            : $( '#_yith_wcact_auction_type' ),
            Commission_Fee          : $( '#_yith_auction_commission_fee_onoff' ),
            Apply_Commission_Fee    : $( '#_yith_auction_commission_apply_fee_onoff' ),

        },
        init              : function () {
            var self = ywcactFieldsVisibility;


            // Buy now onff enabled
           self.dom.buyNowOff.on( 'change', function () {
                self.handle( self.conditions.buy_now, 'yes' === self.dom.buyNowOff.val() );
            } ).trigger( 'change' );

            // Overtime onoff enabled
            self.dom.oVertime.on( 'change', function () {
                self.handle( self.conditions.overtime, 'yes' === self.dom.oVertime.val() );
                self.dom.oVertime_Set.trigger('change');
            } ).trigger( 'change' );

            self.dom.oVertime_Set.on( 'change', function () {
                self.handle( self.conditions.overtime_set, 'yes' === self.dom.oVertime.val() && 'yes' === self.dom.oVertime_Set.val() );
            } ).trigger( 'change' );

            // fee onoff enabled
            self.dom.Fee.on( 'change', function () {
                self.handle( self.conditions.fee, 'yes' === self.dom.Fee.val() );
                self.dom.Ask_Fee.trigger('change');
            } ).trigger( 'change' );
            // fee onoff enabled
            self.dom.Ask_Fee.on( 'change', function () {
                self.handle( self.conditions.ask_fee, 'yes' === self.dom.Ask_Fee.val() && 'yes' === self.dom.Fee.val() );
            } ).trigger( 'change' );


            // reschedule onoff enabled
            self.dom.rEschedule.on( 'change', function () {
                self.handle( self.conditions.reschedule, 'yes' === self.dom.rEschedule.val() );
                self.dom.rEschedule_no_bids.trigger( 'change' );
            } ).trigger( 'change' );
            self.dom.rEschedule_no_bids.on( 'change', function () {
                self.handle( self.conditions.reschedule_no_bids, 'yes' === self.dom.rEschedule.val() && ( 'yes' === self.dom.rEschedule_no_bids.val() || 'yes' === self.dom.rEschedule_no_reserve.val()  )  );
            } ).trigger( 'change' );
            self.dom.rEschedule_no_reserve.on( 'change', function () {
                self.handle( self.conditions.reschedule_no_reserve, 'yes' === self.dom.rEschedule.val() && ( 'yes' === self.dom.rEschedule_no_reserve.val() || 'yes' === self.dom.rEschedule_no_bids.val()  )  );
            } ).trigger( 'change' );

            //Bid type onoff
            // reschedule onoff enabled
            self.dom.bId_type_OnOff.on( 'change', function () {
                self.handle( self.conditions.bid_up, 'yes' === self.dom.bId_type_OnOff.val() );
                self.dom.bId_type_set.trigger('change');
            } ).trigger( 'change' );
            //Bid type set
            self.dom.bId_type_set.on( 'change', function () {
                self.handle( self.conditions.bid_up_set, 'yes' === self.dom.bId_type_OnOff.val() && 'automatic' === self.dom.bId_type_set.val()  );
            } ).trigger( 'change' );
            //Bid type radiobutton
            self.dom.bId_type.on( 'change', function () {

                self.handle( self.conditions.simple_rule, 'simple' === self.dom.bId_type.val() );
                self.handle( self.conditions.advanced_rule, 'advanced' === self.dom.bId_type.val() );

            } ).trigger( 'change' );

            //Auction type options
            self.dom.aUction_type.on( 'change', function () {

                self.handle( self.conditions.auction_normal, 'normal' === self.dom.aUction_type.val() );
                self.change_decrement( self.dom.aUction_type.val() );

            } ).trigger( 'change' );

            /* == Commission Fee */
            self.dom.Commission_Fee.on( 'change', function () {

                self.handle( self.conditions.commission_fee, 'yes' === self.dom.Commission_Fee.val() );
                self.dom.Apply_Commission_Fee.trigger('change');

            } ).trigger( 'change' );

            /* == Apply Commission Fee */

            self.dom.Apply_Commission_Fee.on( 'change', function () {

                self.handle( self.conditions.apply_commission_fee, 'yes' === self.dom.Apply_Commission_Fee.val() && 'yes' === self.dom.Commission_Fee.val() );

            } ).trigger( 'change' );

        },
        handle            : function ( target, condition ) {
            var targetHide    = ywcactFieldsVisibility.showPrefix + target;

            if ( condition ) {
                $( targetHide ).show();
            } else {
                $( targetHide ).hide();
            }
        },

        change_decrement : function ( condition ) { // Set labels on minimun increment amount section on product page

            if( 'normal' === condition ) {


                $('.ywcact-minimun-increment-amount label').html(admin_settings_section.minimun_increment_amount);
                $('.ywcact-minimun-increment-amount .yith-wcact-form-field__description .ywcact-min-incr-amount').html('<span class="ywcact-min-incr-amount">'+admin_settings_section.minimun_increment_amount_desc+'<span>');

            } else {

                $('.ywcact-minimun-increment-amount label').html(admin_settings_section.minimun_decrement_amount);
                $('.ywcact-minimun-increment-amount .yith-wcact-form-field__description .ywcact-min-incr-amount').html('<span class="ywcact-min-incr-amount">'+admin_settings_section.minimun_decrement_amount_desc+'<span>');
            }

        }
    };

    ywcactFieldsVisibility.init();

    $('.ywcact-add-rule').prependTo($('.ywcact-automatic-bid-increment-advanced-end'));
    $('.ywcact-product-add-rule').prependTo($('.ywcact-automatic-product-bid-increment-advanced-end'));


    $('.ywcact-product-add-rule').on('click',function (e) {

        let row = $( '.ywcact-automatic-product-bid-increment-advanced-rule:last' ).clone().css( {'display': 'none'} );
        let numItems = $('.ywcact-bid-increment-row').length;
        row.find( 'input' ).val( '' );
        row.insertBefore( $( ".ywcact-automatic-product-bid-increment-advanced-end" ) );
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
            $(this).attr('name','_yith_auction_bid_increment_advanced['+actualinput+']['+data_type+']');
        });

        let end = $( ".ywcact-automatic-product-bid-increment-advanced-end" );
        let inputs_end = $( end ).find( "input" );
        $(inputs_end).each(function (i) {
            let data_type = $(this).data('input-type');
            $(this).attr('name','_yith_auction_bid_increment_advanced['+numItems+']['+data_type+']');
        });


        row.fadeTo(
            400,
            1,
            function () {
                row.css( {'display': 'block'} );
            }
        );
    });


    $('.ywcact-remove-rule').on('click',function () { //COntar de nuevo los inputs

        let row = $(this).closest('.ywcact-automatic-product-bid-increment-advanced-rule');

        row.remove();
        reassign_id();

    });

    function reassign_id() {
        $('.ywcact-bid-increment-row').each(function (j) {

            let inputs = $( this ).find( "input" );

            $(inputs).each(function (i) {
                let data_type = $(this).data('input-type');
                $(this).attr('name','_yith_auction_bid_increment_advanced['+j+']['+data_type+']');
            });
        });
    }


    /*===== How to reschedule auctions not paid =====*/
    var ywcactRescheduleFieldsVisibility = {
        showPrefix        : '.ywcact-general-reschedule-for-another__section',

        conditions                  : {
            section_3               : '3',
            section_2               : '2',
            section_1               : '1',
            section_stripe1         : 'stripe1',
            section_stripe2         : 'stripe2',
        },
        dom               : {
            mAxSelectReminder   : $( '#ywcact_settings_reschedule_auction_not_paid_max_select_reminder' ),
            aFterSelectReminder : $( '#ywcact_settings_reschedule_auction_not_paid_after_select_reminder' ),

            stripeFirstStep:         $('#ywcact_settings_reschedule_auction_not_paid_stripe_fist_step'),

        },
        init              : function () {
            var self = ywcactRescheduleFieldsVisibility;

            // Max select reminder.
            self.dom.mAxSelectReminder.on( 'change', function () {
                self.handle( self.conditions.section_2, 'send_reminder' === self.dom.mAxSelectReminder.val() );
                self.handle( self.conditions.section_3, 'send_winner_email_second_bidder' === self.dom.aFterSelectReminder.val() && 'send_reminder' === self.dom.mAxSelectReminder.val() );

            } ).trigger( 'change' );

            // After Select reminder.
            self.dom.aFterSelectReminder.on( 'change', function () {
                self.handle( self.conditions.section_3, 'send_winner_email_second_bidder' === self.dom.aFterSelectReminder.val() && 'send_reminder' === self.dom.mAxSelectReminder.val() );
            } ).trigger( 'change' );

            //First step Stripe integration.

            self.dom.stripeFirstStep.on( 'change', function (  ) {
                self.handle( self.conditions.section_stripe2, 'change_second_bidder' === self.dom.stripeFirstStep.val() );
            } ).trigger( 'change' );


            
        },
        handle            : function ( target, condition ) {
            var targetHide    = ywcactRescheduleFieldsVisibility.showPrefix + target;
            console.log(targetHide);
            console.log(condition);
            if ( condition ) {
                $( targetHide ).show();
            } else {
                $( targetHide ).hide();
            }
        }
    };

    ywcactRescheduleFieldsVisibility.init();


    /* == Apply CSS rule on select2 fields*/
    $('.ywcact-select-inline').each(function () {
       $(this).siblings('.select2').addClass('ywcact-select-inline');
    });

    /* == Handle checkboxgroup followers == */
    var ywcactFollowersVisibility = {
        conditions          : {
            notify_followers_on_new_bids    : '#yith_wcact_notify_followers_on_new_bids',
            notice_automatic_charge         : '#yith_wcact_stripe_note_automatic_charge',
        },
        dom               : {
            allowSubscribe   : $( '#yith_wcact_settings_tab_auction_allow_subscribe' ),
            forceUsersAddCreditCard: $('#yith_wcact_verify_payment_method'),
            chargeAutomaticallyAuctionPrice: $('#yith_wcact_stripe_charge_automatically_price'),

        },
        init              : function () {
            var self = ywcactFollowersVisibility;

            // Max select reminder.
            self.dom.allowSubscribe.on( 'change', function () {
                self.handle( self.conditions.notify_followers_on_new_bids, 'yes' === self.dom.allowSubscribe.val(), 'tr' );
            } ).trigger( 'change' );

            self.dom.chargeAutomaticallyAuctionPrice.on( 'change', function (  ) {
                self.handle( self.conditions.notice_automatic_charge, 'yes' === self.dom.chargeAutomaticallyAuctionPrice.val(), 'tr' );
            } ).trigger('change');
            //Force users to add a credit card
            self.dom.forceUsersAddCreditCard.on( 'change', function (  ) {
                self.handle( self.conditions.notice_automatic_charge, 'yes' === self.dom.forceUsersAddCreditCard.val() && 'yes' === self.dom.chargeAutomaticallyAuctionPrice.val(), 'tr' );
            }).trigger('change');



        },
        handle            : function ( target, condition, closest ) {
            var targetHide    = $(target);

            if( closest ) {
                targetHide = $(target).closest(closest);
            }
            if ( condition ) {
                targetHide.show();
            } else {
                targetHide.hide();
            }
        }

    };
    ywcactFollowersVisibility.init();

});
