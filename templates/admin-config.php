<?php
	$settings = \ATR\Settings::Instance()->All();
?>
<div class="ass-admin-page-padding">
	<h1><?php print __( "Server Status Configuration", "atr-server-status" ); ?></h1>
	<form method="POST" action="<?php print esc_url( admin_url( "admin-post.php" ) ); ?>">
		<table class="form-table">
			<?php foreach( $settings as $key => $args ) { ?>
				<?php $saved_value = \ATR\Settings::Instance()->Get($key); ?>
				<tr>
					<th scope="row">
						<?php print $args["label"]; ?>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php print $args["label"]; ?></legend>
							<?php if( $args["type"] == "radio" ) { ?>
								<?php foreach( $args["options"] as $value => $name ) { ?>
									<label>
										<input<?php if($saved_value == $value) { print ' checked="checked"'; } ?> type="radio" name="settings[<?php print $key; ?>]" value="<?php print $value; ?>" id="<?php print $key; ?>-yes" /><span><?php print $name; ?></span>
									</label>
								<?php } ?>
							<?php } else if( $args["type"] == "text" || $args["type"] == "number" ) { ?>
								<input min="0" type="<?php print $args["type"]; ?>" name="settings[<?php print $key; ?>]" value="<?php print $saved_value; ?>" id="<?php print $key; ?>" />
							<?php } else if( $args["type"] == "bool" ) { ?>
								<input type="checkbox" name="settings[<?php print $key;?>]" value="1"<?php if( $saved_value == 1 ) { print "checked"; } ?> /> <?php print __( "Yes", "atr-server-status" ); ?>
							<?php } ?>
							<p><?php print $args["description"]; ?></p>
						</fieldset>
					</td>
				</tr>	
			<?php } ?>
			<tr>
				<th colspan="2">
					<?php wp_nonce_field( "ass-save-configs" ); ?>
					<input type="hidden" name="action" value="ass_save_config" />
					<input type="submit" name="save-config" value="<?php print __( "Save configurations", "atr-server-status" ); ?>" class="button button-primary" />
				</th>
			</tr>
		</table>
	</form>
</div>