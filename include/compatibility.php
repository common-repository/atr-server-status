<?php
	$compatiblePlugins = [
		"dark-mode/dark-mode.php",
		"acf-extended/acf-extended.php"
	];

	add_action( "admin_init", function() use ($compatiblePlugins) {
		foreach( $compatiblePlugins as $plugin ) {
			if( is_plugin_active( $plugin ) ) {
				$compatibility = plugin_dir_path( __FILE__ ) . "plugin-compatibility/" . basename($plugin);

				if( file_exists( $compatibility ) ) {
					require $compatibility;
				}
			}
		}
	} );


	if( ass_is_plugin_page() ) {
		if( extension_loaded( "curl" ) == false ) {
			StatusMessages::Instance()->set( __( "cURL Is not enabled for your server, this plugin won't work properly without, contact your hosting provider for more information about cURL.", "atr-server-status" ), "error" );
		}

		if( extension_loaded( "sockets" ) == false ) {
			StatusMessages::Instance()->set( __( "Sockets is not enabled for your server, this plugin won't work properly without, contact your hosting provider for more information about sockets.", "atr-server-status" ), "error" );
		}
	}