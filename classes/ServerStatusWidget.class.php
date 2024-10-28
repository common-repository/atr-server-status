<?php
class ServerStatusWidget extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct( false, "Server Status", [
			"description" => __( "Display one or more server status blocks", "atr-server-status" )
		] );
	}

	function widget( $args, $instance ) {
		$title = apply_filters( "widget_title", $instance["title"] );
 
		print $args["before_widget"];

		if ( ! empty( $title ) ) {
			print $args["before_title"] . $title . $args["after_title"];
		}
		 
		if( trim( $instance["servers_selected"] ) != '' ) {
			print do_shortcode( '[server-status id="'.$instance["servers_selected"].'"]' );
		}

		print $args["after_widget"];
	}

	function update( $new_instance, $old_instance ) {
		$instance = [];
		$instance["title"] = ( ! empty( $new_instance["title"] ) ) ? strip_tags( $new_instance["title"] ) : '';

		$instance["servers_selected"] = implode( ',', array_map( "intval",  $new_instance["servers_selected"] ) );

		return $instance;
	}

	function form( $instance ) {
		$servers = ass_get_all_servers();

		$title = '';
		if ( isset( $instance[ "title" ] ) ) {
			$title = $instance[ "title" ];
		}

		$selected = [];
		if( isset( $instance["servers_selected"] ) ) {
			$selected = explode( ',', $instance["servers_selected"] );
		}
		?>
			<p>
				<label for="<?php echo $this->get_field_id( "title" ); ?>"><?php _e( "Title:" ); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id( "title" ); ?>" name="<?php echo $this->get_field_name( "title" ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>

			<?php if( ! empty( $servers ) ) { ?>
				<p>
					<select name="<?php echo $this->get_field_name( "servers_selected[]" ); ?>" class="atr-server-widget-select" style="width: 100%; height: 150px;" multiple>
						<?php foreach( $servers as $server ) { ?>
							<option <?php print in_array( $server->ID, $selected ) ? "selected" : ''; ?> value="<?php print $server->ID; ?>"><?php print $server->humanname; ?></option>
						<?php } ?>
					</select>
					<small><?php print __( "hold down the <code>ctrl</code> or <code>shift</code> key while selecting.", "atr-server-status" ); ?></small>
				</p>
			<?php } else { ?>
				<?php print __( "You have not yet configured any servers, use the admin menu link to add some.", "atr-server-status" ); ?>
			<?php } ?>
		<?php
	}
}

