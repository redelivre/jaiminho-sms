<h1><?php _e('Manage Data Fields', 'wp-sms'); ?></h1>
<h3><?php _e('Available Extra Fields', 'wp-sms'); ?></h3>
<p><?php printf(__('%d total', 'wp-sms'), sizeof($fields)); ?></p>
<ol>
	<?php
		foreach ($fields as $field) {
			echo '<li>' . htmlentities($field['name']) . '</li>';
		}
?>
</ol>

<h3><?php _e('Add Field', 'wp-sms'); ?></h3>
<form method="post">
	<label for="wp_sms_field_name"><?php
		_e('Field name:', 'wp-sms');
	?></label>
	<input type="text" size="20" name="wp_sms_field_name">
	<br>
	<input type="submit" name="wp_sms_new_data_field"
		value="<?php _e('Add', 'wp-sms'); ?>">
</form>
