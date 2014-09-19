<?php
/*
Plugin Name: Wordpress SMS
Plugin URI: http://mostafa-soufi.ir/blog/wordpress-sms
Description: Send a SMS via WordPress, Subscribe for sms newsletter and send an SMS to the subscriber newsletter.
Version: 2.5.2
Author: Mostafa Soufi
Author URI: http://mostafa-soufi.ir/
Text Domain: wp-sms
License: GPL2
*/

	define('WP_SMS_VERSION', '2.5.2');
	define('WP_SMS_DIR_PLUGIN', plugin_dir_url(__FILE__));
	
	include_once dirname( __FILE__ ) . '/install.php';
	include_once dirname( __FILE__ ) . '/upgrade.php';
	
	register_activation_hook(__FILE__, 'wp_sms_install');
	
	load_plugin_textdomain('wp-sms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
	__('Send a SMS via WordPress, Subscribe for sms newsletter and send an SMS to the subscriber newsletter.', 'wp-sms');

	global $wp_sms_db_version, $wpdb;
	
	$date = date('Y-m-d H:i:s' ,current_time('timestamp',0));

	function wp_sms_page() {

		if (function_exists('add_options_page')) {

			add_menu_page(__('Wordpress SMS', 'wp-sms'), __('Wordpress SMS', 'wp-sms'), 'manage_options', __FILE__, 'wp_sendsms_page');
			add_submenu_page(__FILE__, __('Send SMS', 'wp-sms'), __('Send SMS', 'wp-sms'), 'manage_options', __FILE__, 'wp_sendsms_page');
			add_submenu_page(__FILE__, __('Posted SMS', 'wp-sms'), __('Posted', 'wp-sms'), 'manage_options', 'jaiminho-sms/posted', 'wp_posted_sms_page');
			add_submenu_page(__FILE__, __('Members Newsletter', 'wp-sms'), __('Newsletter subscribers', 'wp-sms'), 'manage_options', 'jaiminho-sms/subscribe', 'wp_subscribes_page');
			add_submenu_page(__FILE__,
                            __('Manage Data Fields', 'wp-sms'),
                            __('Data Fields', 'wp-sms'),
                            'manage_options',
                            'jaiminho-sms/data_fields',
                            'wp_manage_sms_fields');
			if (is_super_admin()) {
				add_submenu_page(__FILE__, __('Setting', 'wp-sms'), __('Setting', 'wp-sms'), 'manage_options', 'jaiminho-sms/setting', 'wp_sms_setting_page');
			}
			add_submenu_page(__FILE__, __('About', 'wp-sms'), __('About', 'wp-sms'), 'manage_options', 'jaiminho-sms/about', 'wp_sms_about_page');
		}

	}
	add_action('admin_menu', 'wp_sms_page');

	function wp_sms_filter_options() {
		// Prevent malicious users from changing super_admin settings
		if (!is_super_admin()) {
			$super_admin_options = array('wp_admin_mobile',
					'wp_sms_mcc',
					'wp_webservice',
					'wp_subscribes_status',
					'wp_subscribes_activation',
					'wp_subscribes_send_sms',
					'wp_call_jquery',
					'wp_suggestion_status',
					'wp_add_mobile_field',
					'wp_subscribes_send',
					'wp_notification_new_wp_version',
					'wpsms_nrnu_stats',
					'wpsms_gnc_stats',
					'wpsms_ul_stats',
					'wps_add_wpcf7',
					'wpsms_wc_no_stats',
					'wpsms_edd_no_stats',
					'wpsms_quota');

			foreach ($super_admin_options as $option) {
				add_filter('pre_update_option_' . $option,
						function ($new) use ($option) {
							return get_option($option);
				});
			}
		}
		else {
			// wpsms_quota works a little different, it's added, not defined
			add_filter('pre_update_option_wpsms_quota',
					function ($value) {
						return max(0, get_option('wpsms_quota') + (int) $value);
			});
		}
	}
	add_action('init', 'wp_sms_filter_options');
	
	function wp_sms_icon() {
	
		global $wp_version;
		
		if( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) {
			wp_enqueue_style('wps-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', true, '1.0');
		} else {
			wp_enqueue_style('wps-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin-old.css', true, '1.0');
		}
	}
	add_action('admin_head', 'wp_sms_icon');
	
	if(get_option('wp_webservice')) {

		$webservice = get_option('wp_webservice');
		include_once dirname( __FILE__ ) . "/includes/classes/wp-sms.class.php";
		include_once dirname( __FILE__ ) . "/includes/classes/webservice/{$webservice}.class.php";

		$sms = new $webservice;
		
		$sms->username = get_option('wp_username');
		$sms->password = get_option('wp_password');
		$sms->from = get_option('wp_number');
		$sms->custom_values = array();
		for ($i = 0; $i < sizeof($sms->GetCustomFields); $i++) {
			$sms->custom_values[$i] = get_option("wp_custom_value_$i");
		}

		if($sms->unitrial == true) {
			$sms->unit = __('Rial', 'wp-sms');
		} else {
			$sms->unit = __('SMS', 'wp-sms');
		}
	}
	
	if( !get_option('wp_sms_mcc') )
		update_option('wp_sms_mcc', '09');

	if(!is_numeric(get_option('wpsms_quota')))
		update_option('wpsms_quota', 0);
	
	function wp_subscribes() {
	
		global $wpdb, $table_prefix;
		
		$get_group_result = $wpdb->get_results("SELECT * FROM `{$table_prefix}sms_subscribes_group`");
		
		include_once dirname( __FILE__ ) . "/includes/templates/wp-sms-subscribe-form.php";
	}
	add_shortcode('subscribe', 'wp_subscribes');
	
	function wp_sms_loader(){
	
		wp_enqueue_style('wpsms-css', plugin_dir_url(__FILE__) . 'assets/css/style.css', true, '1.1');
		
		if( get_option('wp_call_jquery') )
			wp_enqueue_script('jquery');
	}
	add_action('wp_enqueue_scripts', 'wp_sms_loader');

	function wp_sms_adminbar() {
	
		global $wp_admin_bar;
		$get_last_credit = get_option('wp_last_credit');
		
		if(is_super_admin()) {
		
			if($get_last_credit) {
				global $sms;
				$wp_admin_bar->add_menu(array(
					'id'		=>	'wp-credit-sms',
					'title'		=>	 sprintf(__('Your Credit: %s %s', 'wp-sms'), number_format($get_last_credit), $sms->unit),
					'href'		=>	get_bloginfo('url').'/wp-admin/admin.php?page=jaiminho-sms/setting'
				));
			}
			
			$wp_admin_bar->add_menu(array(
				'id'		=>	'wp-send-sms',
				'parent'	=>	'new-content',
				'title'		=>	__('SMS', 'wp-sms'),
				'href'		=>	get_bloginfo('url').'/wp-admin/admin.php?page=jaiminho-sms/wp-sms.php'
			));
		} else {
			return false;
		}
	}
	add_action('admin_bar_menu', 'wp_sms_adminbar');

	function wp_sms_rightnow_discussion() {
		global $sms;
		echo "<tr><td class='b'><a href='".get_bloginfo('url')."/wp-admin/admin.php?page=jaiminho-sms/wp-sms.php'>".number_format(get_option('wp_last_credit'))."</a></td><td><a href='".get_bloginfo('url')."/admin.php?page=jaiminho-sms/wp-sms.php'>".__('Credit', 'wp-sms')." (".$sms->unit.")</a></td></tr>";
	}
	add_action('right_now_discussion_table_end', 'wp_sms_rightnow_discussion');

	function wp_sms_rightnow_content() {
		global $wpdb, $table_prefix;
		$usernames = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}sms_subscribes");
		echo "<tr><td class='b'><a href='".get_bloginfo('url')."/wp-admin/admin.php?page=jaiminho-sms/subscribe'>".$usernames."</a></td><td><a href='".get_bloginfo('url')."/wp-admin/admin.php?page=jaiminho-sms/subscribe'>".__('Newsletter Subscriber', 'wp-sms')."</a></td></tr>";
	}
	add_action('right_now_content_table_end', 'wp_sms_rightnow_content');
	
	function wp_sms_glance() {
	
		global $wpdb, $table_prefix;
		
		$admin_url = get_bloginfo('url')."/wp-admin";
		$subscribe = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}sms_subscribes");
		
		echo "<li class='wpsms-subscribe-count'><a href='{$admin_url}/admin.php?page=jaiminho-sms/subscribe'>".sprintf(__('%s Subscriber', 'wp-sms'), $subscribe)."</a></li>";
		echo "<li class='wpsms-credit-count'><a href='{$admin_url}/admin.php?page=jaiminho-sms/setting&tab=web-service'>".sprintf(__('%s SMS Credit', 'wp-sms'), number_format(get_option('wp_last_credit')))."</a></li>";
	}
	add_action('dashboard_glance_items', 'wp_sms_glance');
	
	function wp_sms_enable() {
	
		$get_bloginfo_url = get_admin_url() . "admin.php?page=jaiminho-sms/setting&tab=web-service";
		echo '<div class="error"><p>'.sprintf(__('Please check the <a href="%s">SMS credit</a> the settings', 'wp-sms'), $get_bloginfo_url).'</p></div>';
	}

	// Don't bother the users, they can't change the settings
	/*
	if(!get_option('wp_username') || !get_option('wp_password'))
		add_action('admin_notices', 'wp_sms_enable');
	*/
	
	function wp_sms_widget() {
	
		wp_register_sidebar_widget('wp_sms', __('Subscribe to SMS', 'wp-sms'), 'wp_subscribe_show_widget', array('description'	=>	__('Subscribe to SMS', 'wp-sms')));
		wp_register_widget_control('wp_sms', __('Subscribe to SMS', 'wp-sms'), 'wp_subscribe_control_widget');
	}
	add_action('plugins_loaded', 'wp_sms_widget');
	
	function wp_subscribe_show_widget($args) {
	
		extract($args);
			echo $before_title . get_option('wp_sms_widget_name') . $after_title;
			wp_subscribes();
	}

	function wp_subscribe_control_widget() {
	
		if($_POST['wp_sms_submit_widget']) {
			update_option('wp_sms_widget_name', $_POST['wp_sms_widget_name']);
		}
		
		include_once dirname( __FILE__ ) . "/includes/templates/wp-sms-widget.php";
	}
	
	function wp_sms_pointer($hook_suffix) {
	
		wp_enqueue_style('wp-pointer');
		wp_enqueue_script('wp-pointer');
		wp_enqueue_script('utils');
	}
	add_action('admin_enqueue_scripts', 'wp_sms_pointer');
	
	function wp_sendsms_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix;
		
		wp_enqueue_style('wpsms-css', plugin_dir_url(__FILE__) . 'assets/css/style.css', true, '1.1');
		$get_group_result = $wpdb->get_results("SELECT * FROM `{$table_prefix}sms_subscribes_group`");
		
		include_once dirname( __FILE__ ) . "/includes/templates/settings/send-sms.php";
	}
	
	function wp_posted_sms_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix;
		
		wp_enqueue_style('pagination-css', plugin_dir_url(__FILE__) . 'assets/css/pagination.css', true, '1.0');
		include_once dirname( __FILE__ ) . '/includes/classes/pagination.class.php';
		
		if($_POST['doaction']) {
		
			if($_POST['column_ID'])
				$get_IDs = implode(",", $_POST['column_ID']);
			
			$check_ID = $wpdb->query($wpdb->prepare("SELECT * FROM {$table_prefix}sms_send WHERE ID IN (%s)", $get_IDs));

			switch($_POST['action']) {
			
				case 'trash':
					if($check_ID) {
					
						foreach($_POST['column_ID'] as $items) {
							$wpdb->delete("{$table_prefix}sms_send", array('ID' => $items) );
						}
						
						echo "<div class='updated'><p>" . __('With success was removed', 'wp-sms') . "</div></p>";
					} else {
						echo "<div class='error'><p>" . __('Not Found', 'wp-sms') . "</div></p>";
					}
				break;
			}
		}
		
		$total = $wpdb->query($wpdb->prepare("SELECT * FROM `{$table_prefix}sms_send`", false));
		
		include_once dirname( __FILE__ ) . "/includes/templates/settings/posted.php";
	}
	
	function wp_subscribes_page() {
	
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix, $date;
		
		if($_GET['group']) {
			$total = $wpdb->query($wpdb->prepare("SELECT * FROM `{$table_prefix}sms_subscribes` WHERE `group_ID` = '%s'", $_GET['group']));
		} else {
			$total = $wpdb->query($wpdb->prepare("SELECT * FROM `{$table_prefix}sms_subscribes`", false));
		}
		
		if($_POST['search']) {
			$search_query = "%" . $_POST['s'] . "%";
			$total = $wpdb->query($wpdb->prepare("SELECT * FROM `{$table_prefix}sms_subscribes` WHERE `name` LIKE '%s' OR `mobile` LIKE '%s'", $search_query, $search_query));
		}
		
		$get_group_result = $wpdb->get_results("SELECT * FROM `{$table_prefix}sms_subscribes_group`");
		
		/* Pagination */
		wp_enqueue_style('pagination-css', plugin_dir_url(__FILE__) . 'assets/css/pagination.css', true, '1.0');
		include_once dirname( __FILE__ ) . '/includes/classes/pagination.class.php';
		
		// Instantiate pagination smsect with appropriate arguments
		$pagesPerSection = 10;
		$options = array(25, "All");
		$stylePageOff = "pageOff";
		$stylePageOn = "pageOn";
		$styleErrors = "paginationErrors";
		$styleSelect = "paginationSelect";

		$Pagination = new Pagination($total, $pagesPerSection, $options, false, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);

		$start = $Pagination->getEntryStart();
		$end = $Pagination->getEntryEnd();
		/* Pagination */
		
		if($_POST['doaction']) {
		
			if($_POST['column_ID'])
				$get_IDs = implode(",", $_POST['column_ID']);
			
			$check_ID = $wpdb->query($wpdb->prepare("SELECT * FROM {$table_prefix}sms_subscribes WHERE ID IN (%s)", $get_IDs));

			switch($_POST['action']) {
				case 'trash':
					if($check_ID) {
					
						foreach($_POST['column_ID'] as $items) {
							$wpdb->delete("{$table_prefix}sms_subscribes", array('ID' => $items) );
						}
						
						echo "<div class='updated'><p>" . __('With success was removed', 'wp-sms') . "</div></p>";
					} else {
						echo "<div class='error'><p>" . __('Not Found', 'wp-sms') . "</div></p>";
					}
				break;
				
				case 'active':
					if($check_ID) {
						
						foreach($_POST['column_ID'] as $items) {
							$wpdb->update("{$table_prefix}sms_subscribes", array('status' => '1'), array('ID' => $items) );
						}
						
						echo "<div class='updated'><p>" . __('User is active.', 'wp-sms') . "</div></p>";
					} else {
						echo "<div class='error'><p>" . __('Not Found', 'wp-sms') . "</div></p>";
					}
				break;
				
				case 'deactive':
					if($check_ID) {
					
						foreach($_POST['column_ID'] as $items) {
							$wpdb->update("{$table_prefix}sms_subscribes", array('status' => '0'), array('ID' => $items) );
						}
						
						echo "<div class='updated'><p>" . __('User is Deactive..', 'wp-sms') . "</div></p>";
					} else {
						echo "<div class='error'><p>" . __('Not Found', 'wp-sms') . "</div></p>";
					}
				break;
			}
		}
		
		$name	= trim($_POST['wp_subscribe_name']);
		$mobile	= trim($_POST['wp_subscribe_mobile']);
		$group	= trim($_POST['wpsms_group_name']);
		
		if(isset($_POST['wp_add_subscribe'])) {

			if(!isset($group)) {
				$group = null;
			}
		
			if($name && $mobile) {
			
				if (is_numeric($mobile)) {
				
					$check_mobile = $wpdb->query($wpdb->prepare("SELECT * FROM `{$table_prefix}sms_subscribes` WHERE `mobile` = '%s'", $mobile));
					
					if(!$check_mobile) {
					
						$check = $wpdb->insert(
							"{$table_prefix}sms_subscribes", 
							array(
								'date'		=> $date,
								'name'		=> $name,
								'mobile'	=> $mobile,
								'status'	=> '1',
								'group_ID'	=> $group,
							)
						);
						
						if($check) {
							echo "<div class='updated'><p>" . sprintf(__('username <strong>%s</strong> was added successfully.', 'wp-sms'), $name) . "</div></p>";
						}
						
					} else {
						echo "<div class='error'><p>" . __('Phone number is repeated', 'wp-sms') . "</div></p>";
					}
				} else {
					echo "<div class='error'><p>" . __('Please enter a valid mobile number', 'wp-sms') . "</div></p>";
				}
			} else {
				echo "<div class='error'><p>" . __('Please complete all fields', 'wp-sms') . "</div></p>";
			}
			
		}
		
		if(isset($_POST['wpsms_add_group'])) {
		
			if($group) {
			
				$check_group = $wpdb->query($wpdb->prepare("SELECT * FROM `{$table_prefix}sms_subscribes_group` WHERE `name` = '%s'", $group));
				
				if(!$check_group) {
				
					$check = $wpdb->insert(
						"{$table_prefix}sms_subscribes_group", 
						array(
							'name'	=> $group
						)
					);
					
					if($check) {
						echo "<div class='updated'><p>" . sprintf(__('Group <strong>%s</strong> was added successfully.', 'wp-sms'), $group) . "</div></p>";
					}
					
				} else {
					echo "<div class='error'><p>" . __('Group name is repeated', 'wp-sms') . "</div></p>";
				}
			} else {
				echo "<div class='error'><p>" . __('Please complete field', 'wp-sms') . "</div></p>";
			}
		}
		
		if(isset($_POST['wpsms_delete_group'])) {
		
			if($group) {
				
				$check_group = $wpdb->query($wpdb->prepare("SELECT * FROM `{$table_prefix}sms_subscribes_group` WHERE `ID` = '%s'", $group));
				
				if($check_group) {
				
					$group_name = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$table_prefix}sms_subscribes_group` WHERE `ID` = '%s'", $group));
					$check = $wpdb->delete("{$table_prefix}sms_subscribes_group", array('ID' => $group) );
					
					if($check) {
						echo "<div class='updated'><p>" . sprintf(__('Group <strong>%s</strong> was successfully removed.', 'wp-sms'), $group_name->name) . "</div></p>";
					}
					
				}
			} else {
				echo "<div class='error'><p>" . __('Nothing found!', 'wp-sms') . "</div></p>";
			}
			
		}
		
		if(isset($_POST['wp_edit_subscribe'])) {
		
			if($name && $mobile && $group) {
				if (is_numeric($mobile)) {
					$check = $wpdb->update("{$table_prefix}sms_subscribes",
						array(
							'name'		=> $name,
							'mobile'	=> $mobile,
							'status'	=> $_POST['wp_subscribe_status'],
							'group_ID'	=> $group
						),
						array(
							'ID'		=> $_GET['ID']
						)
					);
					$valid_fields = $wpdb->get_col(
							"SELECT ID FROM {$table_prefix}sms_fields");

					foreach ($valid_fields as $f) {
						if (array_key_exists($f, $_POST['wp_extra_field'])) {
							$wpdb->query($wpdb->prepare(
										"INSERT INTO {$table_prefix}sms_values "
										. "(subscriber, field, value) VALUES "
										. "(%d, %d, %s) "
										. "ON DUPLICATE KEY UPDATE "
										. "value=VALUES(value)",
										$_GET['ID'], $f, $_POST['wp_extra_field'][$f]));
						}
					}
					
					if($check) {
						echo "<div class='updated'><p>" . sprintf(__('username <strong>%s</strong> was update successfully.', 'wp-sms'), $name) . "</div></p>";
					}
					
				} else {
					echo "<div class='error'><p>" . __('Please enter a valid mobile number', 'wp-sms') . "</div></p>";
				}
			} else {
				echo "<div class='error'><p>" . __('Please complete all fields', 'wp-sms') . "</div></p>";
			}
		
			if(!$get_group_result) {
				add_action('admin_print_footer_scripts', 'wpsms_group_pointer');
			}
		}
		
		if($_GET['action'] == 'import') {
			include_once dirname( __FILE__ ) . "/includes/classes/excel-reader.class.php";
			$matching = false;
			$importing = false;
			$filename = null;
			$tmpid = null;

			/* Delete old (>= .5 hour) import files */
			foreach (glob(sys_get_temp_dir() . '/wp-sms-i-*') as $f) {
				if (time() - filemtime($f) >= 1800) {
					unlink($f);
				}
			}
			
			if (isset($_POST['wps_import2'])) {
				if (!array_key_exists('wps_tmpid', $_POST)
						|| !array_key_exists('wps_field', $_POST)
						|| !is_array($_POST['wps_field'])) {
					echo "<div class='error'><p>"
						. __('Invalid file', 'wp-sms') . "</div></p>";
				}
				else {
					$tmpid = $_POST['wps_tmpid'];
					$filename = sys_get_temp_dir() . '/wp-sms-i-' .  $tmpid;
					$file = fopen($filename, 'r');
					if ($file !== false) {
						$fields = $_POST['wps_field'];
						$count = array();
						$associations = array();
						foreach ($fields as $k => $v) {
							if (is_numeric($k)) {
								$count[$v]++;
								$associations[$v] = $k;
							}
						}

						// -3 is None
						unset($count[-3]);
						$matching_ok = true;
						if (array_key_exists(-1, $count) && array_key_exists(-2, $count)) {
							foreach ($count as $c) {
								if ($c != 1) {
									$matching_ok = false;
									break;
								}
							}
						}
						else {
							$matching_ok = false;
						}
						if (!$matching_ok) {
							$matching = true;

							echo "<div class='error'><p>"
								. __('Each field should be matched exactly once. '
										. 'Name and mobile are required.', 'wp-sms')
								. "</div></p>";
						}
						else {
							$importing = true;
						}
						fclose($file);
					}
					else {
					echo "<div class='error'><p>"
						. __('Importing session expired', 'wp-sms') . "</div></p>";
					}
				}
			}

			if(isset($_POST['wps_import']) && !$_FILES['wps-import-file']['error']) {
				$finfo = finfo_open(FILEINFO_MIME);
				$mime = explode(';',
						finfo_file($finfo, $_FILES['wps-import-file']['tmp_name']), 2);
				$m = explode('=', $mime[1], 2);
				$mime[1] = $m[1];
				finfo_close($finfo);

				$tmpid = uniqid($more_entropy=true);
				$filename = sys_get_temp_dir() . '/wp-sms-i-' . $tmpid;
				$output = fopen($filename, 'w');
				$rows = array();

				if ($mime[0] === 'application/vnd.ms-excel') {
					$sheet = new
						Spreadsheet_Excel_Reader($_FILES['wps-import-file']['tmp_name']);
					$rows = $sheet->sheets[0]['cells'];
				}
				else if ($mime[1] !== 'binary') {
					$f = fopen($_FILES['wps-import-file']['tmp_name'], "r");
					while (($row = fgetcsv($f, $escape='"')) !== false) {
						$rows[] = $row;
					}
					fclose($f);
				}
				else {
					echo "<div class='error'><p>"
						. __('Invalid file type', 'wp-sms') . "</div></p>";
				}

				if (!empty($rows)) {
					if (sizeof($rows) <= 1024) {
						foreach ($rows as $row) {
							fputcsv($output, $row);
						}
						$matching = true;
					}
					else {
						echo "<div class='error'><p>"
							. __('You can only import 1024 subscribers at a time', 'wp-sms')
							. "</div></p>";
					}
				}

				fclose($output);
			}

			if ($matching) {
				$query = 'SELECT * FROM ' . $wpdb->prefix . 'sms_fields';
				$results = $wpdb->get_results($query, 'ARRAY_A');
				$fields = array();
				foreach ($results as $r) {
					$fields[$r['ID']] = $r['name'];
				}
				$fields[-3] = __('None', 'wp-sms');
				$fields[-2] = __('Name', 'wp-sms');
				$fields[-1] = __('Mobile', 'wp-sms');
				ksort($fields);
				$data = array();
				$total = 0;
				$cols = 0;

				$f = fopen($filename, "r");
				while (($row = fgetcsv($f, $escape='"')) !== false) {
					$total++;
					$cols = max($cols, sizeof($row));
					if (sizeof($data) >= 8) {
						array_pop($data);
						if (sizeof($data) == 7) {
							$data[] = array('...');
						}
					}
					$data[] = $row;
				}
				fclose($f);

				include_once dirname( __FILE__ )
					. "/includes/templates/settings/importpreview.php";
			}
			else {
				if ($importing) {
					$get_mobile = $wpdb->get_col($wpdb->prepare(
								"SELECT `mobile` FROM {$table_prefix}sms_subscribes", false));
					$valid_fields = $wpdb->get_col(
							"SELECT ID FROM {$table_prefix}sms_fields");
					$data = array();

					$f = fopen($filename, "r");
					while (($row = fgetcsv($f, $escape='"')) !== false) {
						$data[] = $row;
					}
					fclose($f);

					$duplicate = 0;
					$invalid = 0;
					foreach($data as $row) {
						if (!array_key_exists($associations[-2], $row)
								|| !array_key_exists($associations[-1], $row)) {
							$invalid++;
							continue;
						}

						$name = $row[$associations[-2]];
						$mobile = trim($row[$associations[-1]]);

						if (!is_numeric($mobile)) {
							$invalid++;
							continue;
						}

						// Check and count duplicate items
						if(in_array($mobile, $get_mobile)) {
							$duplicate += 1;
							continue;
						}
						$get_mobile[] = $mobile;
						
						$wpdb->insert("{$table_prefix}sms_subscribes",
							array(
								'date' => date('Y-m-d H:i:s' ,current_time('timestamp', 0)),
								'name' => $name,
								'mobile' => $mobile,
								'status' => '1',
								'group_ID' => $_POST['wpsms_group_name']
							)
						);
						$subscriber_id = $wpdb->insert_id;
						
						foreach ($valid_fields as $f) {
							if (array_key_exists($f, $associations)
									&& array_key_exists($associations[$f], $row)) {
								$wpdb->insert("{$table_prefix}sms_values",
										array(
											'subscriber' => $subscriber_id,
											'field' => $f,
											'value' => $row[$associations[$f]]));
							}
						}
					}

					if($invalid + $duplicate < sizeof($data)) {
						echo "<div class='updated'><p>"
							. sprintf(__(
										'<strong>%s</strong> items were successfully added.',
										'wp-sms'), sizeof($data) - $duplicate - $invalid)
							. "</div></p>";
					}
					
					if ($duplicate) {
						echo "<div class='error'><p>" . sprintf(__(
									'<strong>%s</strong> mobile numbers were duplicates.',
									'wp-sms'), $duplicate) .
						"</div></p>";
					}

					if ($invalid) {
						echo "<div class='error'><p>" . sprintf(__(
									'<strong>%s</strong> mobile numbers were invalid.',
									'wp-sms'), $invalid) .
						"</div></p>";
					}
				}

				if (!$matching) {
					include_once dirname( __FILE__ ) . "/includes/templates/settings/import.php";
				}
			}
		} else if($_GET['action'] == 'export') {
			include_once dirname( __FILE__ ) . "/includes/templates/settings/export.php";
		} else {
			include_once dirname( __FILE__ ) . "/includes/templates/settings/subscribes.php";
		}
		
	}
	
	function wp_sms_setting_page() {
	
		global $sms;
		
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
			
			settings_fields('wp_sms_options');
		}
		
		wp_enqueue_style('css', plugin_dir_url(__FILE__) . 'assets/css/style.css', true, '1.0');
		
		$sms_page['about'] = get_bloginfo('url') . "/wp-admin/admin.php?page=jaiminho-sms/about";
		
		switch($_GET['tab']) {
			case 'web-service':
				include_once dirname( __FILE__ ) . "/includes/templates/settings/web-service.php";
				
				if(get_option('wp_webservice'))
					update_option('wp_last_credit', $sms->GetCredit());
					
				break;
			
			case 'newsletter':
				include_once dirname( __FILE__ ) . "/includes/templates/settings/newsletter.php";
				break;
			
			case 'features':
				include_once dirname( __FILE__ ) . "/includes/templates/settings/features.php";
				break;
			
			case 'notification':
				include_once dirname( __FILE__ ) . "/includes/templates/settings/notification.php";
				break;
			
			case 'quota':
				include_once dirname( __FILE__ )
					. "/includes/templates/settings/quota.php";
				break;

			default:
				include_once dirname( __FILE__ ) . "/includes/templates/settings/setting.php";
				break;
		}
	}
	
	function wp_sms_about_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		include_once dirname( __FILE__ ) . "/includes/templates/settings/about.php";
	}
	
	include_once dirname( __FILE__ ) . '/includes/admin/wp-sms-newslleter.php';
	include_once dirname( __FILE__ ) . '/includes/admin/wp-sms-features.php';
	include_once dirname( __FILE__ ) . '/includes/admin/wp-sms-notifications.php';

	function wp_manage_sms_fields() {
		global $wpdb;

		if (array_key_exists('wp_sms_new_data_field', $_POST)) {
			if (empty($_POST['wp_sms_field_name'])) {
				echo "<div class='error'><p>"
					. __('Please complete all fields', 'wp-sms') . "</div></p>";
			}
			else {
				$r = $wpdb->insert($wpdb->prefix . 'sms_fields',
						array('name' => $_POST['wp_sms_field_name']));

				if ($r === false) {
					echo "<div class='error'><p>"
						. __('Field already exists', 'wp-sms') . "</div></p>";
				}
				else {
					echo "<div class='updated'><p>"
						. __('Field successfully added', 'wp-sms') . "</div></p>";
				}
			}
		}

		$query = 'SELECT * FROM ' . $wpdb->prefix . 'sms_fields';
		$fields = $wpdb->get_results($query, 'ARRAY_A');

		include_once dirname(__FILE__) . '/includes/admin/wp-sms-datafields.php';
	}
