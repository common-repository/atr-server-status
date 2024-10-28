<?php
	$configurations = [
		[
			"type" => "boolean",
			"name" => "ass-follow-redirects",
			"default" => true
		]
	];

	 
	 add_action( 'admin_init', function() {
	 	add_settings_section(
			'ass-configurations',
			'Server Status Configurations',
			function() {},
			'ass-configurations'
		);
	 	
	 	// Add the field with the names and function to use for our new
	 	// settings, put it in our new section
	 	add_settings_field(
			'eg_setting_name',
			'Example setting Name',
			'eg_setting_callback_function',
			'reading',
			'eg_setting_section'
		);
	 	
	 	// Register our setting so that $_POST handling is done for us and
	 	// our callback function just has to echo the <input>
	 	register_setting( 'ass-configurations', 'eg_setting_name' );
	 } );
/*
	foreach($configurations as $config) {
		$args = [
			"sanitize_callback" => "wp_sanitize",
			"show_in_rest" => false,
			"type" => $config["type"],
			"default" => $config["default"]
		];

		register_setting( "ass-configurations", $config["name"], $args );
	}
	*/