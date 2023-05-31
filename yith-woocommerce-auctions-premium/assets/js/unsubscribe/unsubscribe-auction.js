jQuery( function($) {

    /* == Unsubscribe button click == */
    $(document).on('click','.ywcact-unsubscribe-auction-button',function ( e ) {
        var auctions = [];
        $.each($("input[name='yith_wcact_auction_follower_products[]']:checked"), function() {
            auctions.push($(this).val());
        });
        if (auctions.length === 0) {
            alert( yith_wcact_unsubscribe.no_auction_selected );
        } else {
            var email = $('.ywcact-unsubscribe-user-email').val();
            unsubscribe_auctions( auctions, email );

        }
    });

    function unsubscribe_auctions( auctions, email  ) {
        var post_data = {
            auctions: auctions,
            email   : email,
            security: yith_wcact_unsubscribe.nonce,
            action: 'yith_wcact_unsubscribe_auctions'
        };
        $.ajax( {
                    type    : "POST",
                    data    : post_data,
                    url     : yith_wcact_unsubscribe.ajaxurl,
                    success : function ( response ) {

                        //RELOAD CONTENT ALL FINE
                        console.log("ALL IS FINE");
                        console.log(response);
                        $('.ywcact-follower-auction-list-main').empty();
                        $('.ywcact-follower-auction-list-main').html(response);
                    },
                    complete: function () {
                    }
                } );

    }
});
