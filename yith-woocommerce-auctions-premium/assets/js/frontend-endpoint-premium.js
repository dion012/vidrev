jQuery( function($) {
    if ( yith_wcact_frontend_endpoint.time_check  > 0  )  {

        setInterval( live_auctions, yith_wcact_frontend_endpoint.time_check );

        function live_auctions() {
            var data = $('.ywcact-my-acount-auction-template').data('type');

            switch ( data ) {

                case 'my-auction' :

                    update_my_auction_template();

                    break;

                case 'my-watchlist' :

                    update_my_watchlist_template();

                    break;

            }

        }
    }

    /* == My Auction == */
    function update_my_auction_template() {

        $('.yith_wcact_my_auctions_table').block({message: null, overlayCSS: {background: "#fff", opacity: .6}});

        var post_data = {
            security: yith_wcact_frontend_endpoint.update_template,
            currency: $('#yith_wcact_currency').val(),
            action: 'yith_wcact_update_my_account_auctions'
        };

        $.ajax({
                   type: "POST",
                   data: post_data,
                   url: yith_wcact_frontend_endpoint.ajaxurl,
                   success: function (response) {
                       change_price_and_status_for_my_auction_template(response);
                       $('.yith_wcact_my_auctions_table').unblock();
                   },
                   complete: function () {
                   }
               });

    }

    function change_price_and_status_for_my_auction_template(response) {

        $('.yith-wcact-auction-endpoint').remove();
        for ( var i = 0; i<= Object.keys(response).length-1; i++) {
            var row_td = '<td class="order-number yith-wcact-auction-image" data-title="Image">'+response[i].image+'<a href="\'+response[i].product_url+\'">'+response[i].product_name+'</a></td>' +
                         '<td class="yith-wcact-my-bid-endpoint yith-wcact-my-auctions order-date '+response[i].color+'"  data-title="Your bid">'+response[i].my_bid+'</td>' +
                         '<td class="yith-wcact-current-bid-endpoint yith-wcact-my-auctions order-total" data-title="Current bid">'+response[i].price+'</td>' +
                         '<td class="yith-wcact-auctions-status yith-wcact-my-auctions order-status" data-title="Status">'+response[i].status+'</td>';
            var row_tr = $('<tr class="yith-wcact-auction-endpoint"></tr>').append(row_td);
            $('.yith_wcact_my_auctions_table').append(row_tr);
        }
    }


    /* == My Watchlist == */
    function update_my_watchlist_template() {

        $('.yith_wcact_my_auctions_my_watchlist').block({message: null, overlayCSS: {background: "#fff", opacity: .6}});

        var post_data = {
            currency: $('#yith_wcact_currency').val(),
            action: 'yith_wcact_update_my_watchlist_auctions',
            security: yith_wcact_frontend_endpoint.update_template,
        };

        $.ajax({
                   type: "POST",
                   data: post_data,
                   url: yith_wcact_frontend_endpoint.ajaxurl,
                   success: function (response) {
                       change_price_and_status_for_my_watchlist_template(response);
                       $('.yith_wcact_my_auctions_my_watchlist').unblock();
                   },
                   complete: function () {
                   }
               });

        $(document).trigger('yith_wcact_timer');

    }

    function change_price_and_status_for_my_watchlist_template(response) {

        $('.yith-wcact-auction-my-watchlist-endpoint').remove();
        for ( var i = 0; i<= Object.keys(response).length-1; i++) {
            var row_td =    '<td class="product-remove"><a class="remove remove_from_watchlist" href="'+response[i].url_remove+'">&times;</a></td>' +

                    '<td class="order-number yith-wcact-auction-image" data-title="Image">'+response[i].image+'</td>' +
                         '<td class="product-name" data-title="Product"><a href="'+response[i].product_url+'">'+response[i].product_name+'</a></td>' +
                         '<td class="yith-wcact-my-bid yith-wcact-my-auctions '+response[i].color+'"  data-title="Your bid">'+response[i].my_bid+'</td>' +
                         '<td class="yith-wcact-current-bid-endpoint yith-wcact-my-auctions order-total" data-title="Current bid">'+response[i].price+'</td>' +
                         '<td class="yith-wcact-end-on" data-title="Status">'+response[i].timeleft+'</td>';
            var row_tr = $('<tr class="yith-wcact-auction-my-watchlist-endpoint"></tr>').append(row_td);
            $('.yith_wcact_my_auctions_my_watchlist').append(row_tr);
        }
    }


    /* == Index == */

    $(document.body).on('yith_wcact_timer',function () {

        //remove letters for compact mode
        timer = setInterval(function() {
            $( '.yith-wcact-timer-auction' ).not('.ywcact-timer-finished').each( function ( index ) {
                var selector = $(this);
                var result = parseInt(selector.data('remaining-time'));
                if ( result >= 0 ) {
                    timeBetweenDates( result, selector );
                    result--;
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
                }
            });

        }, 1000);
    }).trigger('yith_wcact_timer');

    function timeBetweenDates(result,selector,reload=true ) {
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
                    seconds += "<span class='small-block-content' style='border: 1px solid #A3A3A3;padding: 0px 6px;margin:0px 2px;display: initial;background: " + yith_wcact_frontend_endpoint.small_blocks_background_color + ";'>" + value + "</span>";
                });

                $.each( minutes_split, function( index, value ){
                    minutes += "<span class='small-block-content' style='border: 1px solid #A3A3A3;padding: 0px 6px;margin:0px 2px;display: initial;background: " + yith_wcact_frontend_endpoint.small_blocks_background_color + ";'>" + value + "</span>";
                });

                $.each( hours_split, function( index, value ){
                    hours += "<span class='small-block-content' style='border: 1px solid #A3A3A3;padding: 0px 6px;margin:0px 2px;display: initial;background: " + yith_wcact_frontend_endpoint.small_blocks_background_color + ";'>" + value + "</span>";
                });

                $.each( days_split, function( index, value ){
                    days += "<span class='small-block-content' style='border: 1px solid #A3A3A3;padding: 0px 6px;margin:0px 2px;display: initial;background: " + yith_wcact_frontend_endpoint.small_blocks_background_color + ";'>" + value + "</span>";
                });
            }

            $("#days",selector).html(days);
            $("#hours",selector).html(hours);
            $("#minutes",selector).html(minutes);
            $("#seconds",selector).html(seconds);
        }
    }


});