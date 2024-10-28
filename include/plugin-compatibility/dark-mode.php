<?php
	add_action( "admin_head", function() {
		if( ass_is_plugin_page() ) {
			print "
			<style>
				.ass-shortcode-display pre, .ass-pre { background: #32373c; }
				table.ass-server-status tr.deleted { background: rgb(168, 46, 46)!important; }
			</style>";
		}
	} );