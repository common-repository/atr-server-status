<?php
	namespace ATR {

		/**
		* Configuration handling for ATR Server Status
		*/
		class Settings {
			private $settings = [];
			private $optionKey = "ass-settings";

			protected function __construct() {}

			/**
			* Returns a filtered (hooked) list of all settings
			*/
			public function All() {
				return apply_filters( "atr_default_settings", [
					"request-execution-order" => [
						"type" => "radio",
						"label" => __( "Request Execution Order", "atr-server-status" ),
						"description" => __( "Synchronous execution will wait for the preceeding request to finish before moving on to the next request, slows down the process, but great for performance or slow clients.<br>Asynchronous execution will allow all servers defined to be pinged at once upon request.", "atr-server-status" ),
						"options" => [
							"async" => "Asynchronous",
							"sync" => "Synchronous"
						],
						"default" => "async"
					],
					"attempt-prevent-cache" => [
						"type" => "radio",
						"label" => __( "Attempt to bypass cached results", "atr-server-status" ),
						"description" => __( "By appending a random generated string to the internal request urls, you may, depending on server setup, bypass server side cache of the results. <br>Preferably this should only be enabled if you have any sorts of cache on your server/site.", "atr-server-status" ),
						"options" => [
							"yes" => "Yes",
							"no" => "No"
						],
						"default" => "no"
					],
					"follow-location" => [
						"type" => "radio",
						"label" => __( "Follow Locations Headers", "atr-server-status" ),
						"description" => __( "If a request is answered with a redirecting header <span class='ass-pre'>&nbsp;Location: newdomain.tld&nbsp;</span>, a new request will be made at the new location according to the configured protocol.", "atr-server-status" ),
						"options" => [
							1 => "Yes",
							0 => "No"
						],
						"default" => 1
					],
					"max-redirs" => [
						"type" => "number",
						"label" => __( "Maximum Redirects", "atr-server-status" ),
						"description" => __( "Only used for HTTP(S) connections, and requires 'Follow Location Headers' to be enabled.", "atr-server-status" ),
						"default" => 5
					],
					"ssl-verifypeer" => [
						"type" => "radio",
						"label" => __( "SSL Verify Peer", "atr-server-status" ),
						"description" => __( "Let cURL verify the peer's certificate, useful in debugging scenarios, or when a certificate has expired.<div class='danger-setting'><span>Warning:</span> Disabling this option poses a security risk between your server and the peer.</div>" ),
						"options" => [
							1 => "Yes",
							0 => "No"
						],
						"default" => 1
					],
					"ssl-verifyhost" => [
						"type" => "radio",
						"label" => __( "SSL Verify Host", "atr-server-status" ),
						"description" => __( "Check the existence of a common name and also verify that it matches the hostname provided, useful in debugging scenarios or when a certificate has expired. <div class='danger-setting'><span>Warning:</span> Disabling this option poses a security risk, effectively allowing  MiTM (man in the middle) attacks between your server and the peer.</div>" ),
						"options" => [
							1 => "Yes",
							0 => "No"
						],
						"default" => 1
					],
					"hide-promotions" => [
						"type" => "bool",
						"label" => __( "I don't like promotions", "atr-server-status" ),
						"description" => __( "Don't we all hate ads? It's ok, i'll respect your descision and hide any ads this plugin would otherwise display." ),
						"default" => 0
					]
				] );
			}

			/**
			* Loads saved settings, and applies default values if they don't exist yet.
			*/
			public function Load() {
				$all = [];
				foreach( $this->All() as $key => $args ) { $all[$key] = $args["default"]; }

				$settings = wp_parse_args( get_option( $this->optionKey ), $all );
				$this->settings = apply_filters( "atr_settings_values", $settings );
			}

			/**
			* Saves submitted values
			*/
			public function Save( $settings ) {
				$settings = $this->FilterBadValues( $settings );

				return update_option( $this->optionKey, $settings );
			}

			/**
			* Get the value of a desired setting, null if it doesn't exists
			*/
			public function Get( $setting = null) {
				return isset( $this->settings[$setting] ) ? $this->settings[$setting] : $this->settings;
			}

			/**
			* Call this to get the class instance
			*/
			public static function Instance() {
				static $instance = false;

				if( $instance === false ) {
					$instance = new static(); // Late static binding (PHP 5.3+)
				}

				return $instance;
			}

			/**
			* Make sure only values defined are present.
			*/
			private function FilterBadValues( $settings ) {
				$all = $this->All();

				// Set it to the default value, if a given value isn't present.
				foreach( $settings as $key => $value ) {
					if( $all[$key]["type"] == "radio" ) {
						if( isset($all[$key]["options"][$value]) !== true ) {
							$settings[$key] = $all[$key]["default"];
						}
					} else if( $all[$key] == "text" ) {
						$settings[$key] = sanitize_text_field($value);
					} else if ( $all[$key] == "number" ) {
						$settings[$key] = (int) $value;
					}
				}

				return $settings;
			}
		}
	}