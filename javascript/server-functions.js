function select_text( containerid ) {
	var node = document.getElementById( containerid );

	if ( document.selection ) {
		var range = document.body.createTextRange();
		range.moveToElementText( node  );
		range.select();
	} else if ( window.getSelection ) {
		var range = document.createRange();
		range.selectNodeContents( node );
		window.getSelection().removeAllRanges();
		window.getSelection().addRange( range );
	}
}

function mark_server(dom_selector, response) {
	var server_box = jQuery(dom_selector);

	server_box.removeClass("checking").addClass(response.status);
	server_box.find(".server-box-status").html(response.message);
}

function check_server(dom_selector, server_ID) {
	server = {server_ID: server_ID, action: "ass_check_server"}

	if( Settings["attempt-prevent-cache"] == "yes" ) {
		//var url = "/wp//wp-admin/admin-ajax.php?a="+alphanumeric_unique_id();
		var url = ATR_Ajaxurl+"?a="+alphanumeric_unique_id();
	} else {
		var url = ATR_Ajaxurl;
	}

	jQuery.post( url, server, function(response) {
		mark_server(dom_selector, response);
	}, "json");
}

function alphanumeric_unique_id() {
    return Math.random().toString(36).split('').filter( function(value, index, self) { 
        return self.indexOf(value) === index;
    }).join('').substr(2,8);
}

function check_server_deffered(dom_selector, server_ID) {
	return function() {
		// Wrap with a deferred
		var defer = jQuery.Deferred();

		if( Settings["attempt-prevent-cache"] == "yes" ) {
			var url = ATR_Ajaxurl+"?a="+alphanumeric_unique_id();
		} else {
			var url = ATR_Ajaxurl;
		}

		jQuery.ajax( {
			url: url,
			method: "POST",
			data: {
				server_ID: server_ID, action: "ass_check_server"
			}
		} ).done(function(response) {
			mark_server(dom_selector, JSON.parse(response) );

			// Resolve when complete always.
			// Even on failure we want to keep going with other requests
			defer.resolve();
		} );

		// return a promise so that we can chain properly in the each 
		return defer.promise();
	};
}