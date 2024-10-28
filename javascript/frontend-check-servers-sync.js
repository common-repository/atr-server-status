jQuery(document).ready(function() {
	if( typeof Servers !== "undefined" ) {
		// This will trigger the first callback
		var base = jQuery.when({});

		jQuery.each( Servers, function( index, server_ID ) {
			base = base.then( check_server_deffered( ".server-"+server_ID, server_ID ) );
		} );
	}
} );