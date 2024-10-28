<?php
	add_action( "admin_head", function() {
		?>
			<style type="text/css">
				.ass-shortcode-display pre {
					overflow: visible!important;
					padding:  0px!important;
					line-height: normal!important;
				}
			</style>
		<?php
	} );