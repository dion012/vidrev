/**
 * Popup handler
 *
 * @author YITH
 * @package YITH Auctions for WooCommerce Premium
 * @version 2.0.0
 */

;(function( $, window, document ){

    if( typeof ywcact_popup_data == 'undefined' ) {
        return;
    }

    function animateInElem( elem, animation, callback ) {
        elem.show().addClass( 'animated ' + animation );
        elem.one( 'animationend', function() {
            elem.removeClass( 'animated ' + animation );
            if( typeof callback != 'undefined' ) {
                callback();
            }
        });
    }

    function animateOutElem( elem, animation, callback ) {

        elem.addClass( 'animated ' + animation );
        elem.one( 'animationend', function() {
            elem.hide().removeClass( 'animated ' + animation );
            if( typeof callback != 'undefined' ) {
                callback();
            }
        });
    }


    /**
     * @param $popup
     * @param attr
     * @constructor
     */
    var YITHAuctionPopup = function( item ) {
        if( ! item.length ) {
            return;
        }

        this.self               = item;
        this.wrap               = item.find( '.yith-ywcact-popup-wrapper' );
        this.popup              = item.find( '.yith-ywcact-popup' );
        this.content            = item.find( '.yith-ywcact-popup-content-wrapper' );
        this.overlay            = item.find( '.yith-ywcact-overlay' );
        this.blocked            = false;
        this.opened             = false;
        this.additional         = false;
        this.currentSection     = null;
        this.previousSection    = null;
        this.animationIn        = this.popup.attr( 'data-animation-in' );
        this.animationOut       = this.popup.attr( 'data-animation-out' );

        // position first
        this.position( null );


        // prevent propagation on popup click
        $( this.popup ).on( 'click', function(ev){
            ev.stopPropagation();
        })

        // attach event
        $( window ).on( 'resize', { obj: this }, this.position );
        // open

        $( document ).on( 'click', '.yith-wcact-popup-button', { obj: this, additional: false }, this.open );

        //close the popup on overlay click
        $(document).on( 'click', '.yith-ywcact-overlay.close-on-click', function (e) {
            e.preventDefault();
            $('.yith-ywcact-popup-wrapper .yith-ywcact-popup-close').click();
        });

        //close the popup on X button click
        this.popup.on( 'click', '.yith-ywcact-popup-close', { obj: this }, this.close);
    };

    /** UTILS **/
    YITHAuctionPopup.prototype.position           = function( event ) {
        let popup    = event == null ? this.popup : event.data.obj.popup,
            window_w = $(window).width(),
            window_h = $(window).height(),
            margin   = ( ( window_w - 40 ) > ywcact_popup_data.popupWidth ) ? window_h/10 + 'px' : '0',
            width    = ( ( window_w - 40 ) > ywcact_popup_data.popupWidth ) ? ywcact_popup_data.popupWidth + 'px' : 'auto';

        popup.css({
            'margin-top'    : margin,
            'margin-bottom' : margin,
            'width'         : width,
        });
    },
        YITHAuctionPopup.prototype.block              = function() {
        if( ! this.blocked ) {
            this.popup.block({
                message   : null,
                overlayCSS: {
                    background: '#fff url(' + ywcact_popup_data.loader + ') no-repeat center',
                    opacity   : 0.5,
                    cursor    : 'none'
                }
            });
            this.blocked = true;
        }
    }
    YITHAuctionPopup.prototype.unblock            = function() {
        if( this.blocked ) {
            this.popup.unblock();
            this.blocked = false;
        }
    }


    /** EVENT **/
    YITHAuctionPopup.prototype.open               = function( event ) {
        event.preventDefault();

        let object = event.data.obj;
        let button = $(this);
        // if already opened, return
        if( object.opened ) {
            return;
        }

        if( !button ) {
            return;
        }
        $('#yith-ywcact .yith-ywcact-popup-close').show();

        //Get the content div class
        let divshow = button.data('ywcact-content-id');

        //Clone the content
        let contentshow = $('div' + divshow).clone();
        contentshow.removeAttr("style");

        object.opened = true;

        //Add and show template
        object.showTemplate(contentshow);

        // animate
        object.self.fadeIn("slow");
        animateInElem( object.overlay, 'fadeIn' );
        animateInElem( object.popup, object.animationIn );

        // add html and body class
        $('html, body').addClass( 'yith_ywcact_opened' );

        object.wrap.css('position', 'fixed');
        object.overlay.css('position', 'fixed');
        object.overlay.css('z-index', '2');

        // trigger event
        $(document).trigger( 'yith_ywcact_popup_opened', [ object.popup, object ] );
    }

    /*YITHAuctionPopup.prototype.loadTemplate       = function( id, data ) {
        var template            = wp.template( id );
        this.showTemplate( template( data ) );
    }*/

    YITHAuctionPopup.prototype.showTemplate       = function( section ) {
        this.content.hide().html( section ).fadeIn("slow");
        $(document).trigger( 'yith_ywcact_popup_template_loaded', [ this.popup, this ] );
    }

    YITHAuctionPopup.prototype.close              = function( event ) {
        event.preventDefault();

        var object = event.data.obj;

        object.additional    = false;
        object.opened        = false;
        object.self.fadeOut("slow");

        //Clean content popup
        object.content.html('');

        // remove body class
        $('html, body').removeClass( 'yith_ywcact_opened' );
        // trigger event
        $(document).trigger( 'yith_ywcact_popup_closed', [ object.popup, object ] );
    }


    // START
    $( function(){
        new YITHAuctionPopup( $( document ).find( '#yith-ywcact' ) );
    });

})( jQuery, window, document );
