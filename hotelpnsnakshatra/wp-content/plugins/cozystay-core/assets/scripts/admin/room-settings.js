( function( $ ) {
	"use strict";
	// Reset room facilities
	if ( $( '.loftocean-room-reset-facilities button' ).length ) {
		$( '.loftocean-room-reset-facilities button' ).on( 'click', function( e ) {
			e.preventDefault();
            e.stopPropagation();
            var $btn = $( this ), $message = $btn.siblings( '.message' );
            $btn.attr( 'disabled', 'disabled' ).addClass( 'loading' );
            $message.hide();
            $.post( loftoceanRoomSettings.url, { 'action': loftoceanRoomSettings.actionResetFacilities } ).done( function( response, status ) {
                    if ( ( ! response[ 'success' ] ) || ( typeof response[ 'data' ] == 'undefined' || response[ 'data' ][ 'status' ] == 'failed' ) ) {
                        $message.show();
                    }
                } ).error( function() {
					$message.show();
				} ).always( function() {
    				$btn.attr( 'disabled', false ).removeClass( 'loading' );
    			} );
		} );
	}

	// Sync order data
	if ( $( '.loftocean-room-regenerate-orders button' ).length ) {
		$( '.loftocean-room-regenerate-orders button' ).on( 'click', function( e ) {
			e.preventDefault();
            e.stopPropagation();
            var $btn = $( this ), $message = $btn.siblings( '.message' );
            $btn.attr( 'disabled', 'disabled' ).addClass( 'loading' );
            $message.hide();
            $.post( loftoceanRoomSettings.url, { 'action': loftoceanRoomSettings.actionSyncOrderData } ).done( function( response, status ) {
                    if ( ( ! response[ 'success' ] ) || ( typeof response[ 'data' ] == 'undefined' || response[ 'data' ][ 'status' ] == 'failed' ) ) {
                        $message.show();
                    }
                } ).error( function(){
					$message.show();
				} ).always( function() {
    				$btn.attr( 'disabled', false ).removeClass( 'loading' );
    			} );
		} );
	}
} ) ( jQuery );
