<?php foreach( $servers as $key => $server ) { ?>
	<?php
		if( $server == false ) {
			if( ass_current_user_has_access() ) {
				?>
					<div class="server-box checking server-not-found">
						<strong><?php print __( "This server is improperly configured." ); ?></strong>
						<div class="server-box-status"><?php print __( "Perhaps, you deleted the server and forgot to update the shortcode?" ); ?></div>
					</div>
				<?php
			}
		} else if( get_post_status( $server->ID ) == "publish" ) {
			?>
				<div class="server-box checking server-<?php print $server->ID; ?>">
					<strong><?php print htmlentities($server->humanname); ?></strong>
					<div class="server-box-status"><p class="server-loading"><?php print __( "Checking server status", "atr-server-status" ); ?> <span>.</span><span>.</span><span>.</span></p></div>
				</div>
			<?php
		}
	?>
<?php }; ?>