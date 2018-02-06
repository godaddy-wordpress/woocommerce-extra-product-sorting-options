jQuery( document ).ready( function( $ ) {
	'use strict';

	/* === Checkbox Multiple Control === */

	$( '.customize-control-checkbox-multiple input[type="checkbox"]' ).on( 'change', function() {

		var $checkbox_values = $( this ).parents( '.customize-control' ).find( 'input[type="checkbox"]:checked' ).map( function() {
			return this.value;
		} ).get().join( ',' );

		$( this ).parents( '.customize-control' ).find( 'input[type="hidden"]' ).val( $checkbox_values ).trigger( 'change' );
	} );
} );
