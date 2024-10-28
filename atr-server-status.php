<?php
	/**
	* Plugin Name:  ATR Server Status
	* Plugin URI:   http://rehhoff.me
	* Description:  Allow your visitors to check your servers/service status in realtime.
	* Version:      1.5.1
	* Author:       Allan Thue Rehhoff
	* Author URI:   https://rehhoff.me
	* Requires PHP: 5.6
	* Text Domain:  atr-server-status
	* License:      GPLv3
	* License URI:  https://www.gnu.org/licenses/gpl-3.0.en.html
	*/

	defined( "ABSPATH" ) or die( 'Kiddie free zone!' );

	define( "ASS_PLUGIN_PATH", plugin_dir_path( __FILE__ ) );
	define( "ASS_SERVER_STATUS_SHORTCODE", "server-status" );

	require ASS_PLUGIN_PATH."include/post-types.php";
	require ASS_PLUGIN_PATH."include/functions.php";
	require ASS_PLUGIN_PATH."include/compatibility.php";
	require ASS_PLUGIN_PATH."classes/ATR/Settings.class.php";
	require ASS_PLUGIN_PATH."libraries/httprequest/autoload.php";

	/**
	* Add styling to backend
	*/
	add_action( "admin_enqueue_scripts", function($hook) {
		if( ass_is_plugin_page() === true ) {
			wp_enqueue_style( "ass-server-admin", plugins_url( "stylesheets/admin-servers.css", __FILE__ ) );
			wp_enqueue_script( "jquery-ui-sortable" );
		}

		wp_enqueue_script( "ass-server-script", plugins_url( "javascript/server-functions.js", __FILE__ ) );
	} );

	/**
	* Add the neccessarys cripts and styles to frontend
	*/
	add_action( "wp_enqueue_scripts", function() {
		$execorder = \ATR\Settings::Instance()->Get( "request-execution-order" );

		wp_enqueue_style( "ass-frontend-servers", plugins_url( "stylesheets/frontend-servers.css", __FILE__ ) );

		wp_enqueue_script( "ass-server-script", plugins_url( "javascript/server-functions.js", __FILE__ ) );
		wp_enqueue_script( "ass-frontend-check", plugins_url( "javascript/frontend-check-servers-$execorder.js", __FILE__ ), [ "ass-server-script", "jquery" ], false, true );
	} );

	/**
	* Adds the server management page to admin
	*/
	add_action( "admin_menu", function() {
		add_menu_page( __( "Server status", "atr-server-status" ), __( "Server status", "atr-server-status" ), "administrator", "ass-admin-servers", function() {
			ass_include_template( "admin-servers" );
		}, "dashicons-laptop" );

		add_submenu_page( "ass-admin-servers", __( "Server Status Settings", "atr-server-status"), __( "Configurations", "atr-server-status" ), "administrator", "ass-admin-config", function() {
			ass_include_template( "admin-config" );
		} );
	} );

	/**
	* Load plugin settings
	*/
	add_action( "init", function() {
		\ATR\Settings::Instance()->Load();
	} );

	/**
	* Add a settings link to plugin overview page.
	*/
	add_filter( "plugin_action_links_".plugin_basename( __FILE__ ), function( $links ) {
		$links[] = '<a href="options-general.php?page=ass-admin-servers">' . __( "Settings" ) . "</a>";
		return $links;		
	} );

	/**
	* Register a server status widget
	*/
	add_action( "widgets_init", function() {
		register_widget( "ServerStatusWidget" );
	} );

	/**
	* Allows the user to actually save a server as a wp-post
	*/
	add_action( "admin_post_ass_add_server", function() {
		$submitted_server = $_POST["server"];

		if( ass_validate_server( $submitted_server) && wp_verify_nonce( $_POST["_wpnonce"], "ass-add-server" ) ) {
			$server_id = ass_save_server( $submitted_server );

			if( !is_wp_error($server_id) ) {
				wp_redirect( "admin.php?page=ass-admin-servers&server=".$server_id ); exit;
			} else {
				StatusMessages::Instance()->set( $server_id->get_error_message(), "error", true );
			}
		} else {
			StatusMessages::Instance()->set( "<strong>".__("The submitted data did not validate.", "atr-server-status")."</strong><br>".__("This could either be you have entered incorrect values, or someone, somewhere is doing something really nasty, like a CSRF attack.", "atr-server-status"),
										"error", true );
		}
	} );

	/**
	* Saves a modified server to the database
	*/
	add_action( "admin_post_ass_edit_server", function() {
		$submitted_server = $_POST["server"];
		if( ass_validate_server( $submitted_server ) && wp_verify_nonce( $_POST["_wpnonce"], "ass-edit-server") ) {
			$server_id = ass_save_server( $submitted_server );

			if( !is_wp_error($server_id) ) {
				wp_redirect( "admin.php?page=ass-admin-servers&server=".$server_id ); exit;
			} else {
				StatusMessages::Instance()->set( $server_id->get_error_message(), "error", true );
			}
		} else {
			StatusMessages::Instance()->set( "<strong>".__("The submitted data did not validate.", "atr-server-status")."</strong><br>".__("This could either be you have entered incorrect values, or someone, somewhere is doing something really nasty, like a CSRF attack.", "atr-server-status"),
										"error", true );
		}
	} );

	/**
	* Updates the sorting for server rows
	*/
	add_action( "wp_ajax_ass_sort_server", function() {
		if( ass_current_user_has_access() ) {
			$update = wp_update_post( ["ID" => $_POST["ID"], "menu_order" => $_POST["weight"] ] );
		}
		exit;
	} );

	/**
	* Remove a server from the list
	*/	
	add_action( "wp_ajax_ass_remove_server", function() {
		if(wp_verify_nonce($_POST["_wpnonce"], "ass-remove-server")) {
			$remove = ass_remove_server($_POST["server_id"]);
			if( is_wp_error($remove) ) {
				StatusMessages::Instance()->set( $remove->get_error_message(), "error", false );
			}
		}
	} );

	/**
	* Validates the availability of a given server
	*/	
	add_action( "wp_ajax_nopriv_ass_check_server", "ass_check_server_availability" );
	add_action( "wp_ajax_ass_check_server", "ass_check_server_availability" );

	/**
	* Add the shortcode used for server checking
	*/
	add_shortcode( ASS_SERVER_STATUS_SHORTCODE, function( $atts ) {
		$atts = shortcode_atts( array(
			"id" => null,
			"exclude" => null
		), $atts );

		// Keep a record of all servers used on this page.
		if( ! isset( $GLOBALS["ass_shortcode_atts"] ) || empty( $GLOBALS["ass_shortcode_atts"] ) ) {
			$GLOBALS["ass_shortcode_atts"] = [];
		}

		if( $atts["id"] == null ) {
			$args = [];

			if( $atts["exclude"] != null ) {
				$args["post__not_in"] = explode( ',', $atts["exclude"] );
			}

			$servers = ass_get_all_servers( $args );
		} else if( strpos( $atts["id"], ',' ) !== false) {
			$server_ids = explode( ",", $atts["id"] );
			foreach( $server_ids as $ID ) {
				$servers[] = ass_get_server( $ID );
			}
		} else {
			$servers[0] = ass_get_server( $atts["id"] );
		}

		$GLOBALS["ass_shortcode_atts"] = array_merge( $GLOBALS["ass_shortcode_atts"], wp_list_pluck( $servers, "ID" ) );
		$GLOBALS["ass_shortcode_atts"] = array_unique( $GLOBALS["ass_shortcode_atts"] );

		ob_start();
		ass_include_template("view-servers", ["servers" => $servers, "atts" => $atts]);
		return ob_get_clean();
	} );

	add_action( "wp_footer", function() {
		ass_include_template("wp-footer", ["servers" => isset( $GLOBALS["ass_shortcode_atts"] ) ? $GLOBALS["ass_shortcode_atts"] : [], "settings" => \ATR\Settings::Instance()->get()]);
	} );

	/**
	* Save configurations upon submit
	*/
	add_action( "admin_post_ass_save_config", function() {
		$all = \ATR\Settings::Instance()->All();
		$submitted = $_POST["settings"];

		if( wp_verify_nonce( $_POST["_wpnonce"], "ass-save-configs" ) ) {
			if( \ATR\Settings::Instance()->Save($submitted) === true ) {
				StatusMessages::Instance()->set( __( "Configurations saved successfully.", "atr-server-status" ), "notice-success", true );
			} else {
				StatusMessages::Instance()->set( __( "Unfortunately, an error has occured while saving those values, perhaps you did not change anything?", "atr-server-status" ), "error", true );
			}
		} else {
			StatusMessages::Instance()->set( "<strong>".__("The submitted data did not validate.", "atr-server-status")."</strong><br>".__("This could either be you have entered incorrect values, or someone, somewhere is doing something really nasty, like a CSRF attack.", "atr-server-status"), "error", true );
		}
	} );

	/**
	* Display any messages generated.
	*/
	if( StatusMessages::Instance()->isEmpty() === false ) {
		add_action( "admin_notices", function() {
			ass_include_template( "session-messages" );
		} );
	}