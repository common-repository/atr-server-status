jQuery(document).ready(function() {
	if( typeof Servers !== "undefined" ) {
		for (var i = Servers.length - 1; i >= 0; i--) {
			check_server(".server-"+Servers[i], Servers[i]);
		}
	}
});