(function ( $, window, undefined ) {

    // tabs
    $( 'div.nav-tab-wrapper' ).each( function () {
        var $this = $( this ),
            $tabs = $( 'a', $this ).on( 'click', function ( e ) {
                e.preventDefault();

                // deselect
                $tabs.removeClass( 'nav-tab-active' ).each( function () {
                    // hide all sections
                    $( $( this ).attr( 'href' ) ).hide();
                } );

                // activate clicked section
                $( $( this ).blur().addClass( 'nav-tab-active' ).attr( 'href' ) ).show();
            } );

        // activate first tab
        if ( !$tabs.filter( '[href="' + location.hash + '"]' ).trigger( 'click' ).length )
            $this.find( 'a' ).first().trigger( 'click' );

        // toggle double opt in email settings
        $( 'input[name="openemm[doubleoptin]"]' ).on( 'change', function() {
            $( '#openemm-messages-double_opt_in' )[$(this).is( ':checked' ) ? 'show' : 'hide']();
            $tabs.filter( '[href="#openemm-email-settings"]' )[$(this).is( ':checked' ) ? 'show' : 'hide']();
        } ).trigger( 'change' );
    } );

    // sort sunscription fields
    $( '#form-settings tbody' ).sortable( {
        axis: 'y',
        containment: 'parent',
        handle: 'span.dashicons-move'
    } );

    // select copy text
    $( '#openemm-settings-form code' ).on( 'click', function () {
        var range = document.createRange();
        range.selectNodeContents( this );
        window.getSelection().addRange( range );
    } );

})( jQuery, this, this.document );
