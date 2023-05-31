jQuery( function($) {
    var timer;

    $(document.body).on('yith_wcact_timer',function () {

        var current_time = 0;
        //remove letters for compact mode
        timer = setInterval(function() {
            $( '.yith-wcact-timer-auction' ).not('.ywcact-timer-finished').each( function ( index ) {
                var selector = $(this);

                var utcSeconds     = parseInt( $( this ).data( 'finish-time' ) );
                //Date server converted to the customer timezone
                var d = new Date( 0 ); // The 0 there is the key, which sets the date to the epoch
                d.setUTCSeconds( utcSeconds );
                var fecha_server = d.getTime()/1000;

                var value_current_time = parseInt( $( this ).data( 'current-time' ) ); //Used if current time is updated via Ajax
                current_time =  current_time > value_current_time ? current_time : value_current_time;
                var k = current_time;

                var date_remaining = fecha_server - k;

                var result = date_remaining;

                if ( result >= 0 ) {
                    timeBetweenDates( result, selector );
                    //result--;
                    selector.data( 'remaining-time', result );

                    if ( selector.hasClass( 'yith-wcact-timeleft-compact' ) ) {

                        $( '.yith-wcact-number-label', selector ).each( function ( index ) {
                            var text = $( this ).text().substring( 0, 1 );
                            $( this ).text( text );
                        } );

                        if ( selector.hasClass( 'yith-wcact-timeleft-product-page' ) ) {
                            var dateend = $( '.yith_auction_datetime_shop', selector ).text();
                            $( '#auction_end', selector ).text( '' );
                            $( '.yith-wcact-timeleft-compact', selector ).append( "(" + dateend + ")" );
                        }

                    }
                } else {
                    selector.addClass('ywcact-timer-finished');
                    selector.removeClass('yith-wcact-timer-auction');
                    if( selector.closest('.yith-wcact-time-left-main').hasClass('yith-wcact-time-left-main') ) {
                        timeBetweenDates(result,selector,true);
                    } else {
                        timeBetweenDates( 1, selector ); // Is not necessary to reload.
                    }
                }
            });
            current_time = current_time + 1;
        }, 1000);
    }).trigger('yith_wcact_timer');


    function timeBetweenDates(result,selector,reload ) {
        reload = typeof reload !== 'undefined' ? reload : true;
        if (result <= 0) {

            // Timer done
            clearInterval(timer);
            if( reload ) {
                location.reload();
            }

        } else {

            var last_minute = parseInt(selector.data('last-minute'));

            if (( last_minute > 0 ) && !selector.hasClass('yith-wcact-countdown-last-minute') && result < last_minute ) {
                selector.addClass('yith-wcact-countdown-last-minute');
            }
            var seconds = Math.floor(result);
            var minutes = Math.floor(seconds / 60);
            var hours = Math.floor(minutes / 60);
            var days = Math.floor(hours / 24);

            hours %= 24;
            minutes %= 60;
            seconds %= 60;

            days = ajustar(1,days);
            hours = ajustar(1,hours);
            minutes = ajustar(1,minutes);
            seconds = ajustar(1,seconds);


            if ( selector.hasClass('yith-wcact-timeleft-small-blocks') ) {

                var seconds_split = seconds.toString().split( '' );
                var minutes_split = minutes.toString().split( '' );
                var hours_split   = hours.toString().split( '' );
                var days_split    = days.toString().split( '' );



                seconds = '';
                minutes = '';
                hours = '';
                days = '';

                $.each( seconds_split, function( index, value ){
                    seconds += "<span class='small-block-content' style='border: 1px solid #A3A3A3;padding: 0px 6px;margin:0px 2px;display: initial;background: " + ywcact_frontend_object.small_blocks_background_color + ";'>" + value + "</span>";
                });

                $.each( minutes_split, function( index, value ){
                    minutes += "<span class='small-block-content' style='border: 1px solid #A3A3A3;padding: 0px 6px;margin:0px 2px;display: initial;background: " + ywcact_frontend_object.small_blocks_background_color + ";'>" + value + "</span>";
                });

                $.each( hours_split, function( index, value ){
                    hours += "<span class='small-block-content' style='border: 1px solid #A3A3A3;padding: 0px 6px;margin:0px 2px;display: initial;background: " + ywcact_frontend_object.small_blocks_background_color + ";'>" + value + "</span>";
                });

                $.each( days_split, function( index, value ){
                    days += "<span class='small-block-content' style='border: 1px solid #A3A3A3;padding: 0px 6px;margin:0px 2px;display: initial;background: " + ywcact_frontend_object.small_blocks_background_color + ";'>" + value + "</span>";
                });
            }

            $("#days",selector).html(days);
            $("#hours",selector).html(hours);
            $("#minutes",selector).html(minutes);
            $("#seconds",selector).html(seconds);

            $(document.body).trigger( 'yith_wcact_update_timer', [selector] );

        }
    }

    function ajustar(tam, num) {
        if (num.toString().length <= tam) return ajustar(tam, "0" + num)
        else return num;
    }

    //Button up or down bid
    var current = $('#time').data('current');
    $(".bid").click(function(e){
        e.preventDefault();
        var actual_bid = $('#_actual_bid').val();
        if($(this).hasClass("button_bid_add")){
            if(!actual_bid){
                actual_bid = current;
            }
            if ( actual_bid === '' ) {
                actual_bid = 0;
            }
            actual_bid = parseInt( actual_bid ) + parseInt(date_params.actual_bid_add_value);
            $('#_actual_bid').val(actual_bid);
        } else {
            if(actual_bid){
                actual_bid = parseInt( actual_bid ) - parseInt(date_params.actual_bid_add_value);;
                if (actual_bid >= current){
                    $('#_actual_bid').val(actual_bid);
                }else{
                    $('#_actual_bid').val(current);
                }
            }
        }
    });

//Button bid
//
    $( document ).off( 'click', '.auction_bid' ).on( 'click', '.auction_bid', function( e ) {
        bid($(this));
    } );

    //bid function
    function bid(button) {

        $('#yith-wcact-form-bid').block({message:null, overlayCSS:{background:"#fff",opacity:.6}});
        var form = button.closest( '.cart' );
        var post_data = {
            'bid': form.find( '#_actual_bid').val(),
            'product' : form.find('#time').data('product'),
            'currency': $('#yith_wcact_currency').val(),
            'return_url':ywcact_frontend_object.return_form_url,
            security: ywcact_frontend_object.add_bid,
            action: 'yith_wcact_add_bid'
        };

        $(document.body).trigger('yith_wcact_before_send_bid',[button]);

        var validate = validate_field( form.find( '#_actual_bid'), ywcact_frontend_object.bid_empty_error );

        if( validate ) {
            $.ajax( {
                        type    : "POST",
                        data    : post_data,
                        url     : ywcact_frontend_object.ajaxurl,
                        success : function ( response ) {
                            //console.log(response.url);
                            $( '#yith-wcact-form-bid' ).unblock();
                            window.location = response.url;

                            //window.location.reload(true);
                            // On Success
                        },
                        complete: function () {
                        }
                    } );
        } else {
            $( '#yith-wcact-form-bid' ).unblock();
        }
    }

    function validate_field( field, message ) {
        var validate = true;
        if( !field.val() ) {
            $(field).css('border-color','red');
            $('.yith-wcact-error').hide();
            $(field).closest('.ywcact-bid-form').after("<small class='yith-wcact-error'>"+message+"</small>");
            validate = false;
        }

        return validate;
    }

    //Disable enter in input
    $("#_actual_bid").keydown(function( event ) {
        if ( event.which == 13 ) {
            event.preventDefault();
        }
    });

    //Change the datetime format to locale
    $( '.yith_auction_datetime' ).each( function ( index ) {
        var current_date     = change_datetime_format($(this).text());
        $( this ).text( current_date );
    } );

    $( '.yith_auction_datetime_shop' ).each( function ( index ) {

        var utcSeconds     = parseInt( $( this ).data( 'finnish-shop' ) );
        var b              = new Date();
        c                  = b.getTime() / 1000;

        //Pass Utc seconds to localTime
        var d = new Date( 0 ); // The 0 there is the key, which sets the date to the epoch
        d.setUTCSeconds( utcSeconds );
        string = format_date( d );
        $( this ).text( string );

    } );

    //Live auctions on product page
    if ( ywcact_frontend_object.live_auction_product_page  > 0 ) {
        setInterval(live_auctions,ywcact_frontend_object.live_auction_product_page);
        function live_auctions(){
            live_auctions_template();
        }

        function live_auctions_template() {
            $('#tab-yith-wcact-bid-tab').block({message:null, overlayCSS:{background:"#fff",opacity:.6}});

            var post_data = {
                security: ywcact_frontend_object.update_list_bids,
                product: $(':hidden#yith-wcact-product-id').val(),
                currency: $('#yith_wcact_currency').val(),
                action: 'yith_wcact_update_list_bids'
            };

            $.ajax({
                       type    : "POST",
                       data    : post_data,
                       url     : ywcact_frontend_object.ajaxurl,
                       success : function ( response ) {

                           if ( response != 0 ) {

                               $( '.yith-wcact-table-bids' ).empty();
                               $( '.yith-wcact-table-bids' ).html( response[ 'list_bids' ] );
                               //Change the datetime format to locale
                               $( '.yith_auction_datetime' ).each( function ( index ) {
                                   var current_date = change_datetime_format( $( this ).text() );
                                   $( this ).text( current_date );
                               } );
                               $( '#tab-yith-wcact-bid-tab' ).unblock();
                               $( 'p.price span:first-child' ).html( response[ 'current_bid' ] );
                               $( '#yith-wcact-max-bidder' ).empty();
                               $( '#yith-wcact-max-bidder' ).html( response[ 'max_bid' ] );
                               $( '#yith_wcact_reserve_and_overtime' ).empty();
                               $( '#yith_wcact_manual_bid_increment' ).empty();
                               $( '#yith_wcact_reserve_and_overtime' ).html( response[ 'reserve_price_and_overtime' ] );
                               if ( ywcact_frontend_object.ajax_activated && 'timeleft' in response ) {

                                   $('#timer_auction').data( 'finish', response[ 'new_end_date' ] );
                                   $('#timer_auction').data('finish-time', response[ 'new_end_date' ]  );

                                   var utcSeconds = parseInt( $( '#timer_auction' ).data( 'finish' ) );
                                   var d          = new Date( 0 ); // The 0 there is the key, which sets the date to the epoch
                                   d.setUTCSeconds( utcSeconds );
                                   string = format_date( d );
                                   $( '#dateend' ).text( string );
                               }
                           }
                       },
                       complete: function () {
                       }
                   });
        }
    }

    function format_date( date ) {
        var dateFormat    = date_params.format,
            formattedDate = dateFormat,
            day           = date.getDate(),
            fullDay       = ('0' + day).slice( -2 ),
            month         = date.getMonth() + 1,
            fullMonth     = ('0' + month).slice( -2 ),
            year          = date.getFullYear().toString().substr(-2),
            fullYear      = date.getFullYear(),
            hours         = date.getHours(),
            hours12       = hours % 12,
            meridiem      = hours < 12 ? 'am' : 'pm',
            meridiemUp    = hours < 12 ? 'AM' : 'PM',
            fullHours     = ('0' + hours).slice( -2 ),
            fullHours12   = ('0' + hours12).slice( -2 ),
            minutes       = date.getMinutes(),
            fullMinutes   = ('0' + minutes).slice( -2 ),
            seconds       = date.getSeconds(),
            fullSeconds   = ('0' + seconds).slice( -2 );
        formattedDate =  formattedDate.replace( /d|j|n|m|M|F|Y|y|h|H|i|s|a|A|G|g|/g, function(x){
            var toReturn = x;
            switch(x){
                case 'd':
                    toReturn = fullDay;
                    break;
                case 'j':
                    toReturn = day;
                    break;

                case 'n':
                    toReturn = month;
                    break;
                case 'm':
                    toReturn = fullMonth;
                    break;
                case 'M':
                    toReturn = date_params.month_abbrev[ date_params.month[ fullMonth ] ];
                    break;
                case 'F':
                    toReturn = date_params.month[ fullMonth ];
                    break;

                case 'Y':
                    toReturn = fullYear;
                    break;
                case 'y':
                    toReturn = year;
                    break;
                case 'h':
                    if( '00' == fullHours12 ) {
                        fullHours12 = '12';
                    }
                    toReturn = fullHours12;
                    break;
                case 'H':
                    toReturn = fullHours;
                    break;

                case 'i':
                    toReturn = fullMinutes;
                    break;
                case 's':
                    toReturn = fullSeconds;
                    break;

                case 'a':
                    toReturn = date_params.meridiem[ meridiem ];
                    break;
                case 'A':
                    toReturn = date_params.meridiem[ meridiemUp ];
                    break;
                case 'g':
                    if( 0 == hours12 ) {
                        hours12 = 12;
                    }
                    toReturn = hours12;
                    break;
                case 'G':
                    toReturn = hours;
                    break;

            }
            return toReturn;
        } );
        return formattedDate;
    }

    function change_datetime_format( time ) {
        var datetime = time;
        datetime     = datetime + ' UTC';
        datetime     = datetime.replace( /-/g, '/' );

        var current_date = new Date( datetime );

        return format_date( current_date );
    }

    //time format on related section on product page
    $( document ).on( 'yith_infs_added_elem', function () {
        $( '.date_auction' ).each( function ( index ) {

            var timer;
            var product = parseInt( $( this ).data( 'yith-product' ) );

            var utcSeconds     = parseInt( $( this ).data( 'yith-auction-time' ) );
            var b              = new Date();
            c                  = b.getTime() / 1000;
            var date_remaining = utcSeconds - c;

            //Pass Utc seconds to localTime
            var d = new Date( 0 ); // The 0 there is the key, which sets the date to the epoch
            d.setUTCSeconds( utcSeconds );
            string = format_date( d );
            $( this ).text( string );

        } );
    } );
    $( document ).trigger( 'yith_infs_added_elem' );

    /*Modal confirm bid*/
    $( document ).on( 'click', '.ywcact-auction-confirm', function( e ) {

        var form = $(this).closest( '.cart' );
        var bid = form.find( '#_actual_bid');

        var validate = validate_field (bid, ywcact_frontend_object.bid_empty_error );

        if( validate ) {
            $('.ywcact-bid-popup-value').html(bid.val());
            $('.yith-wcact-confirmation-bid').click();
        }
    });

    $( document ).on( 'click', '.ywcact-auction-fee-confirm', function( e ) {

        $('.ywcact-fee-amount-container').trigger('click');
    });


    $( document ).on('yith_ywcact_popup_template_loaded',function(event, popup, object) {

        $( '.ywcact-modal-button-confirm-bid' ).on( 'click', function( event ) {
            if ( object.opened ) {
                $('.yith-ywcact-popup-wrapper .yith-ywcact-popup-close').click();
                bid($('.ywcact-auction-confirm'));
            }
        });

        $( '.ywcact-modal-button-pay-fee' ).on( 'click', function( event ) { // Pay fee trigger.

            if( object.opened ) {
                pay_fee();
            }

        });

    });
    /*End modal confirm bid*/

    function pay_fee() {
        $('.yith-ywcact-popup-wrapper').block({message:null, overlayCSS:{background:"#fff",opacity:.6}});
        var data = {
            'fee_price': $("input[name='yith-wcact-pay-fee-auction-value']").val(),
            'product_id': $('#time').data('product'),
            'security': ywcact_frontend_object.add_bid,
            action: 'yith_wcact_pay_fee'
        };
        $.ajax({
                   type: "POST",
                   data: data,
                   url: ywcact_frontend_object.ajaxurl,
                   success: function (response) {

                       $('.yith-ywcact-popup-wrapper').unblock();
                       window.location = response.cart_url;

                   },
                   complete: function () {
                   }
               });
    }


    /* == Add to Watchlist == */
    $( document ).on( 'click', '.add_to_watchlist', function( e ) {

        e.preventDefault();

        var container = $('.ywcact-add-to-watchlist-container');

        container.block({message: null, overlayCSS: {background: "#fff", opacity: .6}});
        var product_id = $(this).data('product-id');
        var data = {
            'product_id': product_id,
            'user_id': $(this).data('user-id'),
            action: 'yith_wcact_add_to_watchlist',
            security: ywcact_frontend_object.add_bid
        };
        $.ajax({
                   type: "POST",
                   data: data,
                   url: ywcact_frontend_object.ajaxurl,
                   success: function (response) {

                       if( response ) {

                           if( response.url ) {
                               window.location = response.url;
                               return;
                           }

                           $(document).trigger( 'yith_ywcact_update_wishlist', [ $(this), data, 'add_watchlist' ] );
                           container.html( response.template_watchlist_button );
                       }
                       container.unblock();

                   },
                   complete: function () {
                   }
               });


    });

    /* == Remove to Watchlist == */
    $( document ).on( 'click', '.remove_from_watchlist', function( e ) {

        e.preventDefault();

        var container = $('.ywcact-add-to-watchlist-container');

        container.block({message: null, overlayCSS: {background: "#fff", opacity: .6}});
        var data = {
            'product_id': $(this).data('product-id'),
            'user_id': $(this).data('user-id'),
            security: ywcact_frontend_object.add_bid,
            action: 'yith_wcact_remove_from_watchlist'
        };
        $.ajax({
                   type: "POST",
                   data: data,
                   url: ywcact_frontend_object.ajaxurl,
                   success: function (response) {

                       if( response ) {

                           $(document).trigger( 'yith_ywcact_update_wishlist', [ $(this), data, 'remove_watchlist' ] );
                           container.html( response.template_watchlist_button );

                       }
                       container.unblock();

                   },
                   complete: function () {
                   }
               });


    });

    /* == Update Watchlist == */

    $( document ).on('yith_ywcact_update_wishlist',function(event, call, data, event_execution ) {
        if( data.user_id !== 'undefined' ) {
            var data_object = {
                'user_id': data.user_id,
                action: 'yith_wcact_get_watchlist_fragment_products',
                security: ywcact_frontend_object.add_bid,
            };
            $.ajax({
                       type: "POST",
                       data: data_object,
                       url: ywcact_frontend_object.ajaxurl,
                       success: function (response) {

                           if(response) {

                               $('.ywcact-watchlist-widget-content').block({message: null, overlayCSS: {background: "#fff", opacity: .6}});

                               $('.ywcact-watchlist-widget-content .list').html( response.fragmets_watchlist_product );

                               if('add_watchlist' == event_execution) {
                                   $( '.ywcact-watchlist-widget-content .list' ).each( function () {
                                       $(this).find(".yith-wcact-timer-auction").each(function (  ) {
                                           var remaining = $(this).data('remaining-time');
                                           var auction_change = $(document).find("[data-product-id='" + data.product_id + "']");
                                           auction_change.data('remaining-time',remaining);
                                       });
                                   } );
                               }
                               clearInterval(timer);
                               $(document.body).trigger('yith_wcact_timer');

                               /*Change widget count*/
                               var counter = $('.ywcact-watchlist-container-list').data('watchlist-product-counter');
                               var countertext = $('.ywcact-watchlist-widget-content .items-count').html();

                               if ( counter >= 10 ) {
                                   countertext = countertext.substring(2);
                               } else {
                                   if( undefined === countertext ) {
                                       countertext = '1';
                                   } else {
                                       countertext = countertext.substring(1);
                                   }
                               }

                               $('.items-count').html(counter);
                               $('.ywcact-watchlist-widget-content .items-count').html(counter+countertext);
                               $('.ywcact-watchlist-widget-content').unblock();

                           }

                       },
                       complete: function () {
                       }
                   });
        }
    });




});
