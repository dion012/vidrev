jQuery( function ( $ ) {
  $( document ).on( 'yith_infs_added_elem', function () {
    $( '.date_auction' ).each( function ( index ) {

      var timer;
      var product = parseInt( $( this ).data( 'yith-product' ) );

      //var utcSeconds     = parseInt( $( this ).text() );
      //yith-auction-time
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

  function timeBetweenDates( result, product ) {
    if ( result <= 0 ) {

      clearInterval();
      //window.location.reload(true);

    } else {

      var seconds = Math.floor( result );
      var minutes = Math.floor( seconds / 60 );
      var hours   = Math.floor( minutes / 60 );
      var days    = Math.floor( hours / 24 );

      hours %= 24;
      minutes %= 60;
      seconds %= 60;

      $( 'span[class="days_product_' + product + '"]' ).text( days );
      $( 'span[class="hours_product_' + product + '"]' ).text( hours );
      $( 'span[class="minutes_product_' + product + '"]' ).text( minutes );
      $( 'span[class="seconds_product_' + product + '"]' ).text( seconds );
    }
  }


  //Disable enter in input
  $( "#_actual_bid" ).keydown( function ( event ) {
    if ( event.which == 13 ) {
      event.preventDefault();
    }
  } );

  $( '.yith_auction_datetime_shop' ).each( function ( index ) {

    var timer;
    var product = parseInt( $( this ).data( 'yith-product' ) );

    var utcSeconds     = parseInt( $( this ).data( 'finnish-shop' ) );
    var b              = new Date();
    c                  = b.getTime() / 1000;
    var date_remaining = utcSeconds - c;

    //Pass Utc seconds to localTime
    var d = new Date( 0 ); // The 0 there is the key, which sets the date to the epoch
    d.setUTCSeconds( utcSeconds );
    string = format_date( d );
    $( this ).text( string );

  } );

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

  $( document ).on( "yith-wcan-ajax-filtered", function ( e, response ) {
    $( document ).trigger( 'yith_infs_added_elem' );
  } );

  $(document).on("qv_loader_stop", function ( e, response ) {

    var utcSeconds     = parseInt( $( '.yith_auction_datetime_shop' ).data( 'finnish-shop' ) );
    //Pass Utc seconds to localTime
    var d = new Date( 0 ); // The 0 there is the key, which sets the date to the epoch
    d.setUTCSeconds( utcSeconds );
    string = format_date( d );
    $( '.yith_auction_datetime_shop' ).text( string );
  } );

  //Timeleft on shop page

  $( '.yith-wcact-timer-auction' ).each( function ( index ) {
    let selector = $(this);
    //let result = parseInt(selector.data('remaining-time'));
    let result = parseInt(selector.data('finish-time') - selector.data('current-time') );

    //Timeleft
    setInterval(function() {
      timeBetweenDatesonShop(result,selector);
      result--
    }, 1000);

    if( selector.hasClass('yith-wcact-timeleft-compact') ) {

      $('.yith-wcact-number-label', selector).each(function (index) {
        var text = $(this).text().substring(0, 1);
        $(this).text(text);
      });
    }


  } );

  function timeBetweenDatesonShop(result,selector) {
    if (result < 0) {

      // Timer done

      /*clearInterval(timer);*/
      $('.yith-wcact-timeleft-loop',selector).hide();
      selector.hide();

    } else {

      var last_minute = parseInt(selector.data('last-minute'));

      if (( last_minute > 0 ) && !selector.hasClass('yith-wcact-countdown-last-minute') && result < last_minute ) {
        selector.addClass('yith-wcact-countdown-last-minute');
      }

      let seconds = Math.floor(result);
      let minutes = Math.floor(seconds / 60);
      let hours = Math.floor(minutes / 60);
      let days = Math.floor(hours / 24);

      hours %= 24;
      minutes %= 60;
      seconds %= 60;

      $("#days",selector).text(days);
      $("#hours",selector).text(hours);
      $("#minutes",selector).text(minutes);
      $("#seconds",selector).text(seconds);

      $(document.body).trigger( 'yith_wcact_update_timer', [selector] );
    }
  }


} );
