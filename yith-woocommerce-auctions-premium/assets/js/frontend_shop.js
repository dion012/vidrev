jQuery( function($) {
    $('.date_auction').each(function(index){

        var timer;
        var product = parseInt($(this).data('yith-product'));

        var utcSeconds =  parseInt($(this).text());
        var b = new Date();
        c = b.getTime()/ 1000;
        var date_remaining = utcSeconds - c;

        //Pass Utc seconds to localTime
        var d = new Date(0); // The 0 there is the key, which sets the date to the epoch
        d.setUTCSeconds(utcSeconds);
        string = format_date(d);
        $(this).text(string);

        timer = setInterval(function() {
            timeBetweenDates(date_remaining,product);
            date_remaining--;
        }, 1000);
    });

    function format_date( date ) {
        var dateFormat    = date_params.format,
            formattedDate = dateFormat,
            day           = date.getDate(),
            fullDay       = ('0' + day).slice( -2 ),
            month         = date.getMonth() + 1,
            fullMonth     = ('0' + month).slice( -2 ),
            year          = date.getYear(),
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

        formattedDate =  formattedDate.replace( /d|j|n|m|M|F|Y|y|h|H|i|s|a|A|g|G/g, function(x){
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
                case 'G':
                    toReturn = hours;
                    break;
                case 'g':
                    if( 0 == hours12 ) {
                        hours12 = 12;
                    }
                    toReturn = hours12;
                    break;
            }

            return toReturn;
        } );
        return formattedDate;
    }

    function timeBetweenDates(result,product) {
        if (result <= 0) {

            clearInterval(timer);
            //window.location.reload(true);

        } else {

            var seconds = Math.floor(result);
            var minutes = Math.floor(seconds / 60);
            var hours = Math.floor(minutes / 60);
            var days = Math.floor(hours / 24);

            hours %= 24;
            minutes %= 60;
            seconds %= 60;

            $( 'span[class="days_product_'+product+'"]' ).text(days);
            $( 'span[class="hours_product_'+product+'"]' ).text(hours);
            $( 'span[class="minutes_product_'+product+'"]' ).text(minutes);
            $( 'span[class="seconds_product_'+product+'"]' ).text(seconds);
        }
    }


    $( document ).on( 'click', '.auction_bid', function( e ) {
        //var target = $( e.target ); // this code get the target of the click -->  $('.bid')
        var form = $( this ).closest( '.cart' );
        var post_data = {
            'bid': form.find( '#_actual_bid').val(),
            'product' : form.find('#time').data('product'),
            //security: object.search_post_nonce,
            security: object.add_bid,
            action: 'yith_wcact_add_bid'
        };

        $.ajax({
            type    : "POST",
            data    : post_data,
            url     : object.ajaxurl,
            success : function ( response ) {
                //console.log(response.url);
                window.location = response.url;

                //window.location.reload(true);
                // On Success
            },
            complete: function () {
            }
        });
    } );

    //Disable enter in input
    $("#_actual_bid").keydown(function( event ) {
        if ( event.which == 13 ) {
            event.preventDefault();
        }
    });

});
