<?php foreach(StatusMessages::Instance()->getAll() as $message) { ?>
	<div class="ass-server-status-message <?php print $message->type; ?> notice">
	 	<p><?php print $message->message; ?></p>
    </div>
<?php } ?>