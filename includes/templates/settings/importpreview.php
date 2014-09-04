<div class="wrap">
	<h2><?php _e('Import', 'wp-sms'); ?></h2>
	<span>
		<?php _e(sprintf(__('%d rows found', 'wp-sms'), $total)); ?>
	</span>
	<form method="post">
		<table border="2">
			<tr>
				<?php
					for ($i = 0; $i < $cols; $i++) {
						echo '<td>';
						echo "<select name=\"wps_field[$i]\">";
						foreach ($fields as $k => $v) {
							echo "<option value=\"$k\">$v</option>";
						}
						echo "</select>";
						echo '</td>';
					}
				?>
			</tr>
			<?php
				foreach ($data as $row) {
					echo '<tr>';
					foreach ($row as $col) {
						echo "<td>$col</td>";
					}
					for ($i = 0; $i < $cols - sizeof($row); $i++) {
						echo '<td></td>';
					}
					echo '</tr>';
				}
			?>
		</table>
		<input type="hidden" name="wps_tmpid" value="<?php echo $tmpid; ?>">
		<input type="submit" name="wps_import2"
			value="<?php _e('Import', 'wp-sms'); ?>">
		<input type="submit" value="<?php _e('Cancel', 'wp-sms'); ?>">
	</form>
</div>
