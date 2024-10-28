<?php
	class StatusMessages {	
		public static $option_key = "atr-server-status-messages";

		private static $instance = null;

		private $messages = [];

		public function __construct() {
			$messages = get_transient( self::$option_key );

			if( ! is_array( $messages ) ) {
				$messages = [];
			}

			$this->messages = $messages;
		}

		public function __destruct() {
			set_transient( self::$option_key, $this->messages, 60 * 60 );
		}

		public static function Instance() {
			if ( self::$instance == null ) {
				self::$instance = new static();
			}

			return self::$instance;
		}

		public function set($message, $type, $redirect_back = false) {
			$this->messages[] = (object) ["message" => $message, "type" => $type];

			if( $redirect_back == true ) {
				wp_redirect( $_SERVER["HTTP_REFERER"] ); exit;
			}
		}

		public function getAll() {
			$messages = $this->messages;
			$this->messages = [];

			return $messages;
		}

		public function isEmpty($type = "all") {
			return empty( $this->messages );
		}
	}