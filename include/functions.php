<?php
	defined( "ABSPATH" ) or die( 'Kiddie free zone!' );

	spl_autoload_register( function( $class ) {
		$classfile = ASS_PLUGIN_PATH."classes/".$class.".class.php";
		if( file_exists( $classfile ) ) {
			require_once $classfile;
		}
	});

	/**
	* Checks if the current user has access to to administer server overviews.
	* @return bool
	*/
	function ass_current_user_has_access() {
		$user = wp_get_current_user();
		return current_user_can( apply_filters( "atr_perm_administer_servers", "administrator" ) );
	}

	/**
	* Provides an array of protocols supported by the plugin
	* @return array
	*/
	function ass_get_supported_protocols() {
		return ["tcp", "udp", "http", "https"];
	}

	/**
	* Includes a plugin template.
	* @todo use locate_template(); in such a manner that templates can be overridden
	* @return void
	*/
	function ass_include_template( $tpl, $vars = [] ) {
		$template_path = ASS_PLUGIN_PATH."templates/".$tpl.".php";
		if( is_file( $template_path ) ) {
			extract( $vars, EXTR_SKIP );
			require $template_path;
		}
	}

	/**
	* Checks if current page is related to this plugin
	* @return bool
	*/
	function ass_is_plugin_page() {
		$plugin_pages = apply_filters( "atr_plugin_pages", [ "ass-admin-servers", "ass-admin-config" ] );

		return isset($_GET["page"]) && in_array($_GET["page"], $plugin_pages);
	}

	/**
	* Get meta key names for the ass_server custom post type
	* @return array
	*/
	function ass_get_server_meta_keys() {
		return ["hostname", "port", "timeout", "protocol"];
	}

	/**
	* Validates if a server is valid for insertion in db
	* @return bool
	*/
	function ass_validate_server( $server ) {
		foreach( $server as $key => $value ) {
			$key = sanitize_text_field( $key );
			$server[$key] = sanitize_text_field( $value );
		}

		if( ! in_array( $server["protocol"], ass_get_supported_protocols() ) ) return false;

		if( trim( $server["humanname"] ) == '' ) return false;

		foreach( ass_get_server_meta_keys() as $key ) {
			if( trim( $server[$key] ) == '') return false;
		}

		if( ! is_numeric( $server["port"] ) || ! is_numeric( $server["timeout"] ) ) return false;

		return true;
	}

	/**
	* Sets a given server post_id to one of the valid values
	* @return bool
	*/
	function ass_set_server_post_status( $post_id,  $new_status ) {
		$valid = [ "publish", "draft" ];

		if( in_array( $new_status, $valid ) ) {
			if( get_post_type( $post_id ) == "ass-server" ) {
				return wp_update_post( [ "ID" => $post_id, "post_status" => $new_status ] );
			}
		}

		return false;
	}

	/**
	* Returns a list of all servers
	* @return array
	*/
	function ass_get_all_servers( $args = [] ) {
		$servers = [];
		$defaults = [
			"post_type" => "ass-server",
			"orderby" => "menu_order",
			"order" => "asc",
			"posts_per_page" => -1,
			"post_status" => "publish"
		];

		$args = wp_parse_args( $args, $defaults );

		foreach( get_posts( $args ) as $wppost ) { $servers[] = ass_get_server($wppost->ID); }
		return $servers;
	}

	/**
	* Gets a single server as object
	* @return mixed A WP_Post object if server is found, false otherwise.
	*/
	function ass_get_server( $post_id ) {
		$post_id = (int) $post_id;

		if( $server = get_post( $post_id ) ) {
			$return = [
				"ID" => $server->ID,
				"humanname" => $server->post_title,
				"weight" => $server->menu_order,
				"status" => $server->post_status
			];

			foreach( get_post_meta( $server->ID ) as $key => $value ) { $return[$key] = $value[0]; }
			return (object) $return;
		}

		return false;
	}

	/**
	* Saves the server to the database
	* @return mixed - WP_Error if user has insufficient privileges, The newly inserted server id otherwise
	*/
	function ass_save_server($server) {
		if(ass_current_user_has_access() === false) return new WP_Error("insufficient-privileges", __("<strong>Insufficient privileges.</strong><br>You're not allowed to perform this action", "atr-server-status") );

		foreach($server as $key => $value) {
			$key = sanitize_text_field( $key );
			$server[$key] = sanitize_text_field( $value );
		}
		
		if( isset( $server["ID"] ) ) {
			$server_id = wp_update_post( [
				"ID" => (int) $server["ID"],
				"post_type" => "ass-server",
				"post_title" => $server["humanname"],
			] );
		} else {
			$server_id = wp_insert_post( [
				"post_type" => "ass-server",
				"post_status" => "publish",
				"post_title" => $server["humanname"],
				"menu_order" => $server["weight"]
			], true );
		}

		if( ! is_wp_error( $server_id ) ) {
			foreach( ass_get_server_meta_keys() as $key) {
				if( isset( $server[$key] ) ) {
					if( isset( $server["ID"] ) ) {
						update_post_meta( $server_id, $key, $server[$key] );
					} else {
						add_post_meta( $server_id, $key, $server[$key] );
					}
				}
			}
		}

		return $server_id;
	}

	/**
	* Deletes a server from the database
	* @return mixed WP_Error if user doesn't have such permissions true otherwise
	*/
	function ass_remove_server($id) {
		if( ass_current_user_has_access() === false ) return new WP_Error( "insufficient-privileges", __( "<strong>Insufficient privileges.</strong><br>You're not allowed to remove servers from this list.", "atr-server-status" ) );

		$id = (int) sanitize_text_field($id);
		$server = get_post($id);
		
		// Double check we're not accidently deleting important data
		if( $server->post_type == "ass-server" ) {
			wp_delete_post( $server->ID, true );

			foreach( ass_get_server_meta_keys() as $key ) {
				delete_post_meta( $server->ID, $key );
			}

			return true;
		}
	}

	/**
	* Checks if a given server is either up or down
	* @return array
	*/
	function ass_check_server_availability() {
		$server = ass_get_server( (int) sanitize_text_field( $_POST["server_ID"] ) );
		$status = ["status" => "is-down", "message" => "Unsupported protocol '".$server->protocol."'"];
		$setttings = \ATR\Settings::Instance()->Get(); 

		$protocol = $server->protocol."://";
		if( in_array( $server->protocol, ["tcp","udp"] ) ) {
			try {
				$socket = new SocketConnection( $server->hostname, $server->port, $server->timeout, $protocol );

				$socket->testConnection();

				$status["status"] = "is-up";
				$status["message"] = apply_filters( "atr_server_success_message", __( "Server is up and running.", "atr-server-status" ), $server );
			} catch(Exception $e) {
				$status["status"] = "is-down";
				$status["message"] = apply_filters( "atr_server_error_message", $e->getMessage(), $server, $e );
			}
		} else if( in_array( $server->protocol, ["http", "https"] ) ) {
			try {
				$url = $protocol.$server->hostname;
				$request = new \Http\Request($url);
				$request->port($server->port);

				if( (bool) $setttings["follow-location"] == true ) {
					$request->setOption( CURLOPT_MAXREDIRS, $setttings["max-redirs"] );
				} else {
					$request->setOption( CURLOPT_MAXREDIRS, 0 );
				}

				if( $server->protocol == "https" ) {
					$request->setOption( CURLOPT_SSL_VERIFYPEER, $settings["ssl-verifypeer"] );
					$request->setOption( CURLOPT_SSL_VERIFYHOST, $settings["ssl-verifyhost"] );
				}

				$response = $request->get(false, $server->timeout);

				$status["status"] = "is-up";
				$status["message"] = apply_filters("atr_server_success_message", __("Server is up and running.", "atr-server-status"), $server);
			} catch (Exception $e) {
				$status["status"] = "is-down";
				$status["message"] = apply_filters("atr_server_error_message", $e->getMessage(), $server);
			}
		}

		print json_encode($status);
		exit;
	}