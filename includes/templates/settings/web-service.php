
<style>
	p.register{
		float: <?php echo is_rtl() == true? "right":"left"; ?> 
	}
</style>

<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a href="?page=jaiminho-sms/setting" class="nav-tab<?php if($_GET['tab'] == '') { echo " nav-tab-active";} ?>"><?php _e('General', 'wp-sms'); ?></a>
		<a href="?page=jaiminho-sms/setting&tab=web-service" class="nav-tab<?php if($_GET['tab'] == 'web-service') { echo " nav-tab-active"; } ?>"><?php _e('Web Service', 'wp-sms'); ?></a>
		<a href="?page=jaiminho-sms/setting&tab=newsletter" class="nav-tab<?php if($_GET['tab'] == 'newsletter') { echo " nav-tab-active"; } ?>"><?php _e('Newsletter', 'wp-sms'); ?></a>
		<a href="?page=jaiminho-sms/setting&tab=features" class="nav-tab<?php if($_GET['tab'] == 'features') { echo " nav-tab-active"; } ?>"><?php _e('Features', 'wp-sms'); ?></a>
		<a href="?page=jaiminho-sms/setting&tab=notification" class="nav-tab<?php if($_GET['tab'] == 'notification') { echo " nav-tab-active"; } ?>"><?php _e('Notification', 'wp-sms'); ?></a>
		<a href="?page=jaiminho-sms/setting&tab=quota" class="nav-tab<?php if($_GET['tab'] == 'quota') { echo " nav-tab-active"; } ?>"><?php _e('Quota', 'wp-sms'); ?></a>
	</h2>
	
	<form method="post" action="options.php" name="form">
		<table class="form-table">
			<?php wp_nonce_field('update-options');?>
			<tr>
				<th><?php _e('Web Service', 'wp-sms'); ?>:</th>
				<td>
					<select name="wp_webservice" id="wp-webservice" >
						<option value=""><?php _e('Select your Web Service', 'wp-sms'); ?></option>
						
							<option value="clickatell" <?php selected(get_option('wp_webservice'), 'clickatell'); ?>>
								&nbsp;&nbsp;-&nbsp;
								<?php echo sprintf(__('Web Service (%s)', 'wp-sms'), 'Clickatell.com'); ?>
							</option>

					</select>
					
					<?php if(!get_option('wp_webservice')) { ?>
					<p class="description"><?php echo sprintf(__('If you do not have a web service, <a href="%s" target="_blank">click here.</a>', 'wp-sms'), 'http://www.parandhost.com/sms/webservice-for-wordpress-sms-plugin/'); ?></p>
					<p class="description"><?php echo sprintf(__('If your Web service is not on the top list, <a href="%s" target="_blank">click here.</a>', 'wp-sms'), $sms_page['about']); ?></p>
					<?php } ?>
				</td>
			</tr>

			<?php if(get_option('wp_webservice')) { ?>
			<tr>
				<th><?php _e('Username', 'wp-sms'); ?>:</th>
				<td>
					<input type="text" dir="ltr" style="width: 200px;" name="wp_username" value="<?php echo get_option('wp_username'); ?>"/>
					<p class="description"><?php _e('Your username in', 'wp-sms'); ?>: <?php echo get_option('wp_webservice'); ?></p>
					
					<?php if(!get_option('wp_username')) { ?>
						<p class="register"><?php echo sprintf(__('If you do not have a username for this service <a href="%s">click here..</a>', 'wp-sms'), $sms->tariff) ?></p>
					<?php } ;?>
				</td>
			</tr>

			<tr>
				<th><?php _e('Password', 'wp-sms'); ?>:</th>
				<td>
					<input type="password" dir="ltr" style="width: 200px;" name="wp_password" value="<?php echo get_option('wp_password'); ?>"/>
					<p class="description"><?php _e('Your password in', 'wp-sms'); ?>: <?php echo get_option('wp_webservice'); ?></p>
					
					<?php if(!get_option('wp_password')) { ?>
						<p class="register"><?php echo sprintf(__('If you do not have a password for this service <a href="%s">click here..</a>', 'wp-sms'), $sms->tariff) ?></p>
					<?php } ?>
				</td>
			</tr>

			<tr>
				<th><?php _e('Number', 'wp-sms'); ?>:</th>
				<td>
					<input type="text" dir="ltr" style="width: 200px;" name="wp_number" value="<?php echo get_option('wp_number'); ?>"/>
					<p class="description"><?php _e('Your SMS sender number in', 'wp-sms'); ?>: <?php echo get_option('wp_webservice'); ?></p>
				</td>
			</tr>

			<tr>
				<th><?php _e('Credit', 'wp-sms'); ?>:</th>
				<td>
				<?php global $sms; echo $sms->GetCredit() . " " . $sms->unit; ?>
				</td>
			</tr>

			<tr>
				<th><?php _e('Status', 'wp-sms'); ?>:</th>
				<td>
					<?php if($sms->GetCredit() > 0) { ?>
						<img src="<?php echo WP_SMS_DIR_PLUGIN; ?>assets/images/1.png" alt="Active" align="absmiddle"/><span style="font-weight: bold;"><?php _e('Active', 'wp-sms'); ?></span>
					<?php } else { ?>
						<img src="<?php echo WP_SMS_DIR_PLUGIN; ?>assets/images/0.png" alt="Deactive" align="absmiddle"/><span style="font-weight: bold;"><?php _e('Deactive', 'wp-sms'); ?></span>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			
			<tr>
				<td>
					<p class="submit">
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="page_options" value="wp_webservice,wp_username,wp_password,wp_number" />
						<input type="submit" class="button-primary" name="Submit" value="<?php _e('Update', 'wp-sms'); ?>" />
					</p>
				</td>
			</tr>
		</table>
	</form>	
</div>
