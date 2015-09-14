<?php /**
 * @package Mailing_group_module
 * @version 1.0
 */
/*
Plugin Name: WP Mailing Groups
Plugin URI: http://andres.codes
Description:  WPMailman is a plugin for managing electronic mail discussion and discussion/workgroups groups. WP Mailman is integrated with the web, making it easy for users to manage their accounts and for list owners to administer their lists. WP Mailman supports built-in archiving, content filtering, and more.
Author: Andres Escobar
Version: 1.0
*/
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * Indicates that a clean exit occured. Handled by set_exception_handler
 */
if ( ! class_exists( 'E_Clean_Exit' ) ) {
	class E_Clean_Exit extends RuntimeException {
	}
}
define( "WPMG_PLUGIN_URL", plugin_dir_url( __FILE__ ) );
define( "WPMG_PRODUCT_ITEM_NAME", "WP Mailman" );
define( "WPMG_STORE_URL", "" );

/* Class to be used in complete plugin for all db requests */
require_once( "lib/mailinggroupclass.php" );
$objMem = new mailinggroupClass();
global $objMem;

require_once( "lib/receivemail.class.php" );
$obj = new receiveMail( '', '', '', $mailserver = '', $servertype = '', $port = '', $ssl = '' );
global $obj;


/*Define global variable to be used in plugin*/
global $wpdb, $table_name_group, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails, $table_name_emails_attachments, $table_name_sent_emails, $table_name_users, $table_name_usermeta;
$visibilityArray = array(
	'Public'     => '1',
	'Invitation' => '2',
	'Private'    => '3'
);

$WPMG_SETTINGS = get_option( "WPMG_SETTINGS" );
global $WPMG_SETTINGS;

$table_name_users    = $wpdb->prefix . "users";
$table_name_usermeta = $wpdb->prefix . "usermeta";

add_action( 'init', 'Mailing_Groups' );
add_action( 'add_meta_boxes', 'add_custom_meta_box' );
add_action( 'save_post', 'save_custom_meta', 10, 2 );

function Mailing_Groups() {

	$labels = array(
		'name'               => _x( 'Mailing Groups', 'Post Type General Name', 'text_domain' ),
		'singular_name'      => _x( 'Mailing Group', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'          => __( 'Mailing Groups', 'text_domain' ),
		'parent_item_colon'  => __( 'Parent Item:', 'text_domain' ),
		'all_items'          => __( 'All Items', 'text_domain' ),
		'view_item'          => __( 'View Item', 'text_domain' ),
		'add_new_item'       => __( 'New Mailing Group', 'text_domain' ),
		'add_new'            => __( 'New Mailing Group', 'text_domain' ),
		'edit_item'          => __( 'Adjust Settings', 'text_domain' ),
		'update_item'        => __( 'Update Item', 'text_domain' ),
		'search_items'       => __( 'Search Item', 'text_domain' ),
		'not_found'          => __( 'Not found', 'text_domain' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'text_domain' )
	);
	$args   = array(
		'label'               => __( 'mg_groups', 'text_domain' ),
		'description'         => __( 'Mailing Groups', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'author', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 1,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page'
	);
	register_post_type( 'mg_groups', $args );

	$labels = array(
		'name'               => _x( 'Group Threads', 'Post Type General Name', 'text_domain' ),
		'singular_name'      => _x( 'Group Thread', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'          => __( 'Group Threads', 'text_domain' ),
		'parent_item_colon'  => __( 'Parent Item:', 'text_domain' ),
		'all_items'          => __( 'All Items', 'text_domain' ),
		'view_item'          => __( 'View Item', 'text_domain' ),
		'add_new_item'       => __( 'New Thread', 'text_domain' ),
		'add_new'            => __( 'New Group Thread', 'text_domain' ),
		'edit_item'          => __( 'Adjust Email Display', 'text_domain' ),
		'update_item'        => __( 'Update Item', 'text_domain' ),
		'search_items'       => __( 'Search Item', 'text_domain' ),
		'not_found'          => __( 'Not found', 'text_domain' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'text_domain' )
	);
	$args   = array(
		'label'               => __( 'mg_threads', 'text_domain' ),
		'description'         => __( 'Group Threads', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'page-attributes' ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 1,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'mg_threads', $args );
}

// Add the Meta Box
function add_custom_meta_box() {
	add_meta_box(
		'mg_group_fields', // $id
		'Mailing Group Options', // $title
		'show_custom_meta_box', // $callback
		'mg_groups', // $page
		'normal', // $context
		'high',
		mg_group_custom_meta_fields()
	);

	add_meta_box(
		'mg_group_thread_fields',
		'Thread Content',
		'show_custom_meta_box',
		'mg_threads',
		'normal',
		'high',
		mg_thread_custom_meta_fields()
	);
}

//Group Custom Fields
function mg_group_custom_meta_fields() {
	$prefix             = 'mg_group_';
	$custom_meta_fields = array(
		array(
			'label' => 'Use Title In Subject?',
			'desc'  => 'When sending out emails,append group title to subject line',
			'id'    => $prefix . 'use_in_subject',
			'type'  => 'checkbox'
		),
		array(
			'label' => 'Group Email Address',
			'id'    => $prefix . 'email',
			'type'  => 'text'
		),
		array(
			'label' => 'Password:',
			'id'    => $prefix . 'password',
			'type'  => 'password'
		),
		array(
			'label'   => 'Access Mailbox via :',
			'id'      => $prefix . 'server_type',
			'type'    => 'radio',
			'options' => array(
				'one' => array(
					'label' => 'POP3',
					'value' => 'pop3'
				),
				'two' => array(
					'label' => 'IMAP',
					'value' => 'imap'
				)
			)
		),
		array(
			'label' => 'Incoming Mail Server :',
			'id'    => $prefix . 'server',
			'type'  => 'text'
		),
		array(
			'label' => 'Port:',
			'id'    => $prefix . 'server_port',
			'type'  => 'text'
		),
		array(
			'label' => 'User/Pass Required?',
			'id'    => $prefix . 'up_required',
			'type'  => 'checkbox'
		),
		array(
			'label' => 'Username:',
			'id'    => $prefix . 'mail_username',
			'type'  => 'text'
		),
		array(
			'label' => 'Password:',
			'id'    => $prefix . 'mail_password',
			'type'  => 'password'
		),
		array(
			'label' => 'SSL/Secure',
			'id'    => $prefix . 'pop_ssl',
			'type'  => 'checkbox'
		),
		array(
			'label'   => 'Choose Mailing Function :',
			'id'      => $prefix . 'mail_type',
			'type'    => 'radio',
			'options' => array(
				'one'   => array(
					'label' => 'WP Mail',
					'value' => 'wp'
				),
				'two'   => array(
					'label' => 'SMTP Mail',
					'value' => 'smtp'
				),
				'three' => array(
					'label' => 'PHP Mail(No Attachments)',
					'value' => 'php'
				)
			)
		),
		array(
			'label' => 'SMTP Server:',
			'id'    => $prefix . 'smtp_server',
			'type'  => 'text'
		),
		array(
			'label' => 'Port:',
			'id'    => $prefix . 'smtp_port',
			'type'  => 'text'
		),
		array(
			'label' => 'SSL/Secure Connection',
			'id'    => $prefix . 'smtp_ssl',
			'type'  => 'checkbox'
		),
		array(
			'label' => 'Username:',
			'id'    => $prefix . 'smtp_username',
			'type'  => 'text'
		),
		array(
			'label' => 'Password:',
			'id'    => $prefix . 'smtp_password',
			'type'  => 'password'
		),
		array(
			'label' => 'Public Archive:',
			'id'    => $prefix . 'archive',
			'type'  => 'checkbox'
		),
		array(
			'label'   => 'Auto-delete old messages:',
			'id'      => $prefix . 'auto_delete',
			'type'    => 'radio',
			'options' => array(
				'one' => array(
					'label' => 'No',
					'value' => 'no'
				),
				'two' => array(
					'label' => 'Yes',
					'value' => 'yes'
				)
			)
		),
		array(
			'label' => 'Days',
			'id'    => $prefix . 'auto_delete_limit',
			'type'  => 'text'
		),
		array(
			'label'   => 'Footer text for emails:',
			'id'      => $prefix . 'footer_text',
			'type'    => 'textarea',
			'default' => '-- -- -- --
This message was sent to <b>{%name%}</b> at <b>{%email%}</b> by the <a href="{%site_url%}">{%site_url%}</a> website using WP Mailman.
{%archive_url%}
<b><a href="{%unsubscribe_url%}">Unsubscribe</a></b> | <a href="{%profile_url%}">Update Profile</a>'
		),
		array(
			'label'   => 'Available Variables',
			'type'    => 'description_block',
			'example' => '<code>
			{%name%} = Name of the receiving member<br>
			{%email%} = Email of the receiving member<br>
			{%site_url%} = Sites URL<br>
			{%archive_url%} = Message Archive page URL<br>
			{%profile_url%} = User profile URL<br>
			{%unsubscribe_url%} = Unsubscribe URL</code>'
		),
		array(
			'label'      => 'Settings for Subscription Request messages',
			'label_type' => 'header'
		),
		array(
			'label' => 'Sender name:',
			'id'    => $prefix . 'sender_name',
			'type'  => 'text'
		),
		array(
			'label' => 'Sender email:',
			'id'    => $prefix . 'sender_email',
			'type'  => 'text'
		),
		array(
			'label'      => 'Mailing Group Status',
			'label_type' => 'header'
		),
		array(
			'label'   => 'Status:',
			'id'      => $prefix . 'status',
			'type'    => 'select',
			'options' => array(
				'one'   => array(
					'label' => 'Select Status',
					'value' => 0
				),
				'two'   => array(
					'label' => 'Inactive',
					'value' => 1
				),
				'three' => array(
					'label' => 'Active',
					'value' => 2
				)
			)
		),
		array(
			'label'   => 'Visibility:',
			'id'      => $prefix . 'visibility',
			'type'    => 'select',
			'options' => array(
				'one'   => array(
					'label' => 'Public',
					'value' => 1
				),
				'two'   => array(
					'label' => 'Invitation',
					'value' => 2
				),
				'three' => array(
					'label' => 'Private',
					'value' => 3
				)
			)
		),
		array(
			'label' => 'Old Archived URL:',
			'id'    => $prefix . 'old_archive_url',
			'type'  => 'text'
		)
	);

	return $custom_meta_fields;

}

//Threads Custom Fields
function mg_thread_custom_meta_fields() {
	$prefix             = 'mg_thread_';
	$custom_meta_fields = array(
		array(
			'label' => 'Type',
			'id'    => $prefix . 'type',
			'type'  => 'text'
		),
		array(
			'label' => 'Unique Header ID',
			'id'    => $prefix . 'UID',
			'type'  => 'text'
		),
		array(
			'label' => 'References',
			'id'    => $prefix . 'references',
			'type'  => 'text'
		),
		array(
			'label' => 'Parent ID',
			'id'    => $prefix . 'parent_id',
			'type'  => 'text'
		),
		array(
			'label' => 'Email Bounced',
			'id'    => $prefix . 'email_bounced',
			'type'  => 'text'
		),
		array(
			'label' => 'Email From',
			'id'    => $prefix . 'email_from',
			'type'  => 'text'
		),
		array(
			'label' => 'Email From Name',
			'id'    => $prefix . 'email_from_name',
			'type'  => 'text'
		),
		array(
			'label' => 'Email To',
			'id'    => $prefix . 'email_to',
			'type'  => 'text'
		),
		array(
			'label' => 'Email To Name',
			'id'    => $prefix . 'email_to_name',
			'type'  => 'text'
		),
		array(
			'label' => 'Email Subject',
			'id'    => $prefix . 'email_subject',
			'type'  => 'text'
		),
		array(
			'label'    => 'Email Content',
			'readonly' => $prefix . 'email_content',
			'type'     => 'email'
		),
		array(
			'label' => 'Email Group ID',
			'id'    => $prefix . 'email_group_id',
			'type'  => 'text'
		),
		array(
			'label' => 'Date',
			'id'    => $prefix . 'date',
			'type'  => 'text'
		),
		array(
			'label' => 'Status',
			'id'    => $prefix . 'email_status',
			'type'  => 'text'
		),
		array(
			'label' => 'Status Error',
			'id'    => $prefix . 'email_status_error',
			'type'  => 'textarea'
		)
	);

	return $custom_meta_fields;
}

//Show Boxes
function show_custom_meta_box( $post, $metabox ) {
	// Field Array
	$custom_meta_fields = $metabox['args'];

	// Use nonce for verification
	echo '<input type="hidden" name="custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

	// Begin the field table and loop
	echo '<div class="form-table">';
	foreach ( $custom_meta_fields as $field ) {
		// get value of this field if it exists for this post
		$meta = get_post_meta( $post->ID, $field['id'], true );
		// begin a table row with

		switch ( $field['label_type'] ) {
			case '':
				echo '<div class="' . $field['type'] . '" id="' . $field['id'] . 'Contain"><label for="' . $field['id'] . '">' . $field['label'] . '</label>';
				break;
			case 'header':
				echo '<div class="' . $field['label_type'] . '"><h3>' . $field['label'] . '</h3></div>';
				break;
		}
		switch ( $field['type'] ) {
			// case items will go here
			// text
			case 'text':
				echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="30" />
        <br /><span class="description">' . $field['desc'] . '</span></div>';
				break;
			// password
			case 'password':
				echo '<input type="password" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="30" />
        <br /><span class="description">' . $field['desc'] . '</span></div>';
				break;
			// textarea
			case 'textarea':
				$meta = ( $meta != '' ) ? $meta : $field['default'];
				echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="50" rows="5" >' . $meta . '</textarea>
        <br /><span class="description">' . $field['desc'] . '</span></div>';
				break;
			// Email Content
			case 'email':
				echo '<div class="' . $field['type'] . 'Contain"> ' . get_post_meta( $post->ID, $field['readonly'], true ) . '</div></div>';
				break;
			// description_block
			case 'description_block':
				echo '' . $field['example'] . '</div>';
				break;
			// checkbox
			case 'checkbox':
				echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '" ', $meta ? ' checked="checked"' : '', '/><p class="checkbox" for="' . $field['id'] . '">' . $field['desc'] . '</p></div>';
				break;
			// select
			case 'select':
				echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
				foreach ( $field['options'] as $option ) {
					echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="' . $option['value'] . '">' . $option['label'] . '</option></div>';
				}
				echo '</select><br /><span class="description">' . $field['desc'] . '</span></div>';
				break;
			// radio
			case 'radio':
				foreach ( $field['options'] as $option ) {
					echo '<input type="radio" name="' . $field['id'] . '" id="' . $option['value'] . '" value="' . $option['value'] . '" ', $meta == $option['value'] ? ' checked="checked"' : '', ' />
                <label class="radio" for="' . $option['value'] . '">' . $option['label'] . '</label>';
				}
				echo '</div>';
				break;
		} //end switch
	} // end foreach
	echo '</div>'; // end table
}

// Save the Data
function save_custom_meta( $post_id, $post ) {
	$custom_meta_fields = '';

	if ( $post->post_type == 'mg_groups' ) {
		$custom_meta_fields = mg_group_custom_meta_fields();
	}

	if ( $post->post_type == 'mg_threads' ) {
		$custom_meta_fields = mg_thread_custom_meta_fields();
	}

	// verify nonce
	if ( ! wp_verify_nonce( $_POST['custom_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}
	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	// check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// loop through fields and save the data
	foreach ( $custom_meta_fields as $field ) {
		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];
		if ( $new && $new != $old ) {
			update_post_meta( $post_id, $field['id'], $new );
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	} // end foreach
}

add_filter( 'cron_schedules', 'cron_add_weekly' );
function cron_add_weekly( $schedules ) {
	// Adds once weekly to the existing schedules.
	/*$schedules['wpmg_two_minute']     = array(
		'interval' => 120,
		'display'  => __( 'Every Two Minutes', 'mailing-group-module' )
	);
	$schedules['wpmg_five_minute']    = array(
		'interval' => 300,
		'display'  => __( 'Every Five Minutes', 'mailing-group-module' )
	);
	$schedules['wpmg_fifteen_minute'] = array(
		'interval' => 900,
		'display'  => __( 'Every Fifteen Minutes', 'mailing-group-module' )
	);*/

	//Testing purposes
	$schedules['wpmg_two_minute']     = array(
		'interval' => 30,
		'display'  => __( 'Every Two Minutes' )
	);
	$schedules['wpmg_five_minute']    = array(
		'interval' => 35,
		'display'  => __( 'Every Five Minutes' )
	);
	$schedules['wpmg_fifteen_minute'] = array(
		'interval' => 900,
		'display'  => __( 'Every Fifteen Minutes' )
	);

	return $schedules;
}


add_action( 'init', 'do_output_buffer' );
function do_output_buffer() {
	ob_start();
}

/* Install Plugin */
register_activation_hook( __FILE__, 'wpmg_add_mailing_group_plugin' );
function wpmg_add_mailing_group_plugin() {
	/* Class to be used in complete plugin for all db requests */
	require_once( "lib/mailinggroupclass.php" );
	$objMem = new mailinggroupClass();

	/* ADD CONFIG OPTION TO OPTION TABLE*/

	if ( ! wp_next_scheduled( 'wpmg_cron_task_send_email' ) ) {
		wp_schedule_event( time(), 'wpmg_two_minute', 'wpmg_cron_task_send_email' );
	}
	if ( ! wp_next_scheduled( 'wpmg_cron_task_parse_email' ) ) {
		wp_schedule_event( time(), 'wpmg_five_minute', 'wpmg_cron_task_parse_email' );
	}
	if ( ! wp_next_scheduled( 'wpmg_cron_task_bounced_email' ) ) {
		wp_schedule_event( time(), 'wpmg_fifteen_minute', 'wpmg_cron_task_bounced_email' );
	}

	$wpmg_setting = array(
		"MG_WEBSITE_URL"                      => "andres.codes",
		"MG_VERSION_NO"                       => "1.0",
		"MG_SUBSCRIPTION_REQUEST_CHECK"       => "1",
		"MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL" => "e.g. your-mail@example.com",
		"MG_BOUNCE_CHECK"                     => "0",
		"MG_BOUNCE_CHECK_ALERT_TIMES"         => "2",
		"MG_BOUNCE_CHECK_ALERT_EMAIL"         => "e.g. your-mail@example.com",
		"MG_CUSTOM_STYLESHEET"                => "",
		"MG_CONTACT_ADDRESS"                  => "",
		"MG_SUPPORT_EMAIL"                    => "",
		"MG_SUPPORT_PHONE"                    => "1800-123-1234"
	);

	update_option( "WPMG_SETTINGS", $wpmg_setting );

	if(!get_option('wp_mailman_admin_emails')){
		add_option( 'wp_mailman_admin_emails', $objMem->admin_emails );
	}

	if(!get_option('wp_mailman_custom_emails')){
		add_option( 'wp_mailman_custom_emails', $objMem->custom_default_emails );
	}
}


/* Uninstall Plugin */
register_uninstall_hook( __FILE__, "wpmg_mailing_group_uninstall" );
function wpmg_mailing_group_uninstall() {
	return true;
}

/* Initialize menu */
add_action( 'admin_menu', 'wpmg_mailinggroup_Menu' );
function wpmg_mailinggroup_Menu() {
	$admin_level = 10;
	$user_level  = 0;
	/* Adding menus */
	if ( current_user_can( 'manage_options' ) ) {
		add_menu_page( __( 'Mailing Group Manager', 'mailing-group-module' ), __( 'Mailing Group Manager', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_intro', 'wpmg_mailinggroup_intro' );
		add_submenu_page( 'wpmg_mailinggroup_intro', __( 'General Settings', 'mailing-group-module' ), __( 'General Settings', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_intro', 'wpmg_mailinggroup_intro' );
		add_submenu_page( 'wpmg_mailinggroup_intro', __( 'Mailing Groups', 'mailing-group-module' ), __( 'Mailing Groups', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_list', 'wpmg_mailinggroup_list' );

		add_submenu_page( 'null', __( 'Member Manager', 'mailing-group-module' ), __( 'Member Manager', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_memberlist', 'wpmg_mailinggroup_memberlist' );
		add_submenu_page( 'null', __( 'Add Member', 'mailing-group-module' ), __( 'Add Member', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_memberadd', 'wpmg_mailinggroup_memberadd' );
		add_submenu_page( 'wpmg_mailinggroup_intro', __( 'Subscription Requests', 'mailing-group-module' ), __( 'Subscription Requests', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_requestmanagerlist', 'wpmg_mailinggroup_requestmanagerlist' );
		add_submenu_page( 'wpmg_mailinggroup_intro', __( 'Add Subscribers', 'mailing-group-module' ), __( 'Add Subscribers', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_requestmanageradd', 'wpmg_mailinggroup_requestmanageradd' );
		add_submenu_page( 'null', __( 'Add Subscription Request', 'mailing-group-module' ), __( 'Add Subscription Request', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_requestmanageradd', 'wpmg_mailinggroup_requestmanageradd' );
		add_submenu_page( 'null', __( 'Send Message', 'mailing-group-module' ), __( 'Send Message', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_sendmessage', 'wpmg_mailinggroup_sendmessage' );
		add_submenu_page( 'null', __( 'Messages Manager', 'mailing-group-module' ), __( 'Messages Manager', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_messagelist', 'wpmg_mailinggroup_messagelist' );
		add_submenu_page( 'null', __( 'Messages Editor', 'mailing-group-module' ), __( 'Messages Editor', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_adminmessagelist', 'wpmg_mailinggroup_adminmessagelist' );
		add_submenu_page( 'null', __( 'Add Message', 'mailing-group-module' ), __( 'Add Message', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_messageadd', 'wpmg_mailinggroup_messageadd' );
		add_submenu_page( 'null', __( 'Add Admin Message', 'mailing-group-module' ), __( 'Add Admin Message', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_adminmessageadd', 'wpmg_mailinggroup_adminmessageadd' );
		add_submenu_page( 'null', __( 'Style Manager', 'mailing-group-module' ), __( 'Style Manager', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_style', 'wpmg_mailinggroup_style' );
		add_submenu_page( 'null', __( 'Contact Info', 'mailing-group-module' ), __( 'Contact Info', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_contact', 'wpmg_mailinggroup_contact' );
		add_submenu_page( 'null', __( 'Help', 'mailing-group-module' ), __( 'Help', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_help', 'wpmg_mailinggroup_help' );
		add_submenu_page( 'null', __( 'Add Message', 'mailing-group-module' ), __( 'Add Message', 'mailing-group-module' ), $admin_level, 'wpmg_mailinggroup_membergroups', 'wpmg_mailinggroup_membergroups' );

	} else {
		add_menu_page( __( 'Mailing Groups', 'mailing-group-module' ), __( 'Mailing Groups', 'mailing-group-module' ), $user_level, 'wpmg_mailinggroup_membergroups', 'wpmg_mailinggroup_membergroups' );

	}

	wp_register_style( 'demo_table.css', plugin_dir_url( __FILE__ ) . 'css/demo_table.css' );
	wp_enqueue_style( 'demo_table.css' );
	wp_register_script( 'jquery.dataTables.js', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.js', array(
		'jquery'
	) );
	wp_enqueue_script( 'jquery.dataTables.js' );


	wp_register_script( 'custom.js', plugin_dir_url( __FILE__ ) . 'js/custom.js', array(
		'jquery'
	) );
	wp_enqueue_script( 'custom.js' );

	wp_localize_script( 'custom.js', 'PT_Ajax', array(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'nextNonce' => wp_create_nonce( 'myajax-next-nonce' )
		)
	);
}

/* initialize languae loader */
function wpmg_mailinggroup_generalsettingtab() {
	include "template/mg_settingstab.php";
}

/* defining template of all pages used start */
function wpmg_mailinggroup_intro() {
	global $wpdb;
	include "template/mg_intro_text.php";
}

function wpmg_mailinggroup_help() {
	global $wpdb;
	include "template/mg_help.php";
}

function wpmg_mailinggroup_style() {
	global $wpdb;
	include "template/mg_formstyle.php";
}

function wpmg_mailinggroup_contact() {
	global $wpdb;
	include "template/mg_contact.php";
}

function wpmg_mailinggroup_list() {
	global $wpdb, $objMem, $table_name_group, $table_name_requestmanager, $table_name_requestmanager_taxonomy;
	include "template/mg_mailinggrouplist.php";
}


function wpmg_mailinggroup_messagelist() {
	global $wpdb, $objMem, $table_name_message;
	include "template/mg_messagelist.php";
}

function wpmg_mailinggroup_messageadd() {
	global $wpdb, $objMem, $table_name_message;
	include "template/mg_messageadd.php";
}

function wpmg_mailinggroup_sendmessage() {
	global $wpdb, $objMem, $table_name_group, $table_name_message, $table_name_requestmanager;
	include "template/mg_sendmessage.php";
}

function wpmg_mailinggroup_requestmanagerlist() {
	global $wpdb, $objMem, $table_name_group, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_message, $table_name_requestmanager;
	add_action( 'wp_enqueue_script', 'add_thickbox' );
	include "template/mg_mailingrequest.php";
}

function wpmg_mailinggroup_requestmanageradd() {
	global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_group, $table_name_requestmanager;
	include "template/mg_mailingrequestadd.php";
}

function wpmg_mailinggroup_memberlist() {
	global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_sent_emails, $table_name_group;
	include "template/mg_memberlist.php";
}

function wpmg_mailinggroup_memberadd() {
	global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_group;
	include "template/mg_memberadd.php";
}

function wpmg_mailinggroup_membergroups() {
	global $wpdb, $objMem, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_group;
	include "template/mg_membergroups.php";
}



function wpmg_mailinggroup_adminmessagelist() {
	global $wpdb, $objMem, $table_name_message;
	include "template/mg_adminmessagelist.php";
}

function wpmg_mailinggroup_adminmessageadd() {
	global $wpdb, $objMem, $table_name_message;
	include "template/mg_adminmessageadd.php";
}

/* defining template of all page s used end */
/* general function */
function wpmg_redirectTo( $page, $end = "admin" ) {
	$url = "admin.php?page=" . $page;
	if ( $end == 'front' ) {
		$url = $_SERVER['REQUEST_URI'] . $page;
	}
	if ( $end == 'abs' ) {
		$url = $page;
	}
	if ( headers_sent() ) {
		?>
		<html>
		<head>
			<script language="javascript" type="text/javascript">

				window.self.location = '<?php  echo $url; ?>';

			</script>
		</head>
		</html>
		<?php
		exit;
	} else {
		header( "Location: " . $url );
		exit;
	}
}

function wpmg_stringlength( $value, $length = 75 ) {
	return substr( $value, 0, $length ) . "...";
}

function wpmg_trimVal( $value, $by = "" ) {
	if ( $by == "" ) {
		return trim( $value );
	} else {
		return trim( $value, $by );
	}
}

function wpmg_showmessages( $type, $message ) {
	echo "<div class='" . $type . "' id='message'><p><strong>Mailing Group Manager: " . $message . "</strong></p></div>";
}

function wpmg_showmessages_ajax( $type, $message ) {
	return "<div class='" . $type . "' id='message'><p><strong>Mailing Group Manager: " . $message . "</strong></p></div>";
}

/* general function */
/* ajax requests */

add_action( 'wp_ajax_wpmg_sendmessage', 'wpmg_sendmessage_callback' );
add_action( 'wp_ajax_wpmg_checkusername', 'wpmg_checkusername_callback' );

/* Short codes for ajax requests */
/* callback function for above ajax requests */
function wpmg_sendmessage_callback() {
	global $wpdb, $objMem, $table_name_group, $table_name_message;
	include "template/mg_sendmessage.php";
	die();
}

function wpmg_checkusername_callback() {
	$username   = sanitize_text_field( $_REQUEST['username'] );
	$username_e = username_exists( $username );
	$email_e    = email_exists( $username );
	if ( $username_e || $email_e ) {
		$available = "no";
	} else {
		$available = "yes";
	}

	echo $available;
	wp_die();
}

//User requests fro groups
add_action( 'wp_ajax_wpmg_request_group', 'wpmg_request_group_callback' );
add_action( 'wp_ajax_wpmg_cancel_request', 'wpmg_cancel_request_callback' );
add_action( 'wp_ajax_wpmg_leave_group', 'wpmg_leave_group_callback' );
add_action( 'wp_ajax_wpmg_update_group_format', 'wpmg_update_group_format_callback' );

function wpmg_request_group_callback() {
	// check nonce
	$nonce = $_POST['nextNonce'];

	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}

	$user_id      = $_POST['user_id'];
	$email        = $_POST['email'];
	$group_id     = $_POST['group_id'];
	$group_format = $_POST['group_format'];

	$request = array(
		'post_title'  => $email . '-' . $group_id,
		'post_type'   => 'mg_requests',
		'post_status' => 'publish'
	);

	$request_id = wp_insert_post( $request );

	$new_request = array( 'group_format' => $group_format, 'request_id' => $request_id );

	$old_request = get_user_meta( $user_id, 'mg_user_requested_groups', true );

	if ( empty( $old_request ) ) {
		$requested_groups[ $group_id ] = $new_request;
		update_user_meta( $user_id, 'mg_user_requested_groups', $requested_groups );
	} else {
		$requested_groups              = $old_request;
		$requested_groups[ $group_id ] = $new_request;
		update_user_meta( $user_id, 'mg_user_requested_groups', $requested_groups );
	}

	add_post_meta( $request_id, 'mg_request_user_id', $user_id, true );
	add_post_meta( $request_id, 'mg_request_group_id', $group_id, true );


	/*$wp_sent = wp_mail( 'aescobar@isda.org', 'New Subscribtion Request', 'A user has requested to update their subscription' );

	if ( $wp_sent ) {
		wpmg_showmessages(
"error", __( "Your request is being processed", 'mail
ing-group-module' ) );
		exit;
	} else {
		wpmg_showmessages( "error", __( "Your request is being processed, please confirm with the admin as the email of the request was unable to send", 'mailing-group-module' ) );
	}*/

	$response = json_encode( $_POST );
	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die();
}

function wpmg_cancel_request_callback() {
	// check nonce
	$nonce = $_POST['nextNonce'];

	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}

	$user_id    = $_POST['user_id'];
	$group_id   = $_POST['group_id'];
	$request_id = $_POST['request_id'];


	$delete_request = wp_delete_post( $request_id, true );

	if ( $delete_request ) {

		$old_request = get_user_meta( $user_id, 'mg_user_requested_groups', true );

		if ( empty( $old_request ) ) {
			error_log( print_r( 'does not exist', true ) );
		} else {
			$requested_groups = $old_request;

			unset( $requested_groups[ $group_id ] );

			update_user_meta( $user_id, 'mg_user_requested_groups', $requested_groups, $old_request );
		}

	} else {
		error_log( print_r( 'does not exist', true ) );
	}

	$response = json_encode( $_POST );
	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die();
}

function wpmg_leave_group_callback() {
	// check nonce
	$nonce = $_POST['nextNonce'];

	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}

	$user_id  = $_POST['user_id'];
	$group_id = $_POST['group_id'];

	$groups_subscribed = get_user_meta( $user_id, 'mg_user_group_subscribed', true );
	$groups_array      = array();


	if ( array_key_exists( $group_id, $groups_subscribed ) ) {

		unset( $groups_subscribed[ $group_id ] );
		$new_subscribtions = $groups_subscribed;

		foreach ( $new_subscribtions as $group => $format ) {
			$groups_array[] = (string) $group;
		}

		update_user_meta( $user_id, 'mg_user_group_subscribed', $new_subscribtions );
		update_user_meta( $user_id, 'mg_user_group_sub_arr', $groups_array );


	} else {
		error_log( print_r( 'Remove did not work, not in array', true ) );

	}

	$response = json_encode( $_POST );
	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die();
}

function wpmg_update_group_format_callback() {
	// check nonce
	$nonce = $_POST['nextNonce'];

	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}

	$user_id          = $_POST['user_id'];
	$group_id         = $_POST['group_id'];
	$new_group_format = $_POST['new_group_format'];
	$request_id       = $_POST['request_id'];

	if ( empty( $request_id ) ) {
		$groups_subscribed = get_user_meta( $user_id, 'mg_user_group_subscribed', true );
		$group_changed     = array( $group_id => $new_group_format );
		$updated_groups    = array_replace( $groups_subscribed, $group_changed );
		update_user_meta( $user_id, 'mg_user_group_subscribed', $updated_groups, $groups_subscribed );
	} else {

		$groups_requested = get_user_meta( $user_id, 'mg_user_requested_groups', true );

		$group_sub_changed = array(
			$group_id => array(
				'group_format' => $new_group_format,
				'request_id'   => $request_id
			)
		);

		$updated_req_groups = array_replace( $groups_requested, $group_sub_changed );

		update_user_meta( $user_id, 'mg_user_requested_groups', $updated_req_groups, $groups_requested );
	}


	$response = json_encode( $_POST );
	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die();
}

//Request Manager
add_action( 'wp_ajax_wpmg_approve_group_request', 'wpmg_approve_group_request_callback' );
add_action( 'wp_ajax_wpmg_deny_group_request', 'wpmg_deny_group_request_callback' );

function wpmg_approve_group_request_callback() {
	// check nonce
	$nonce = $_POST['nextNonce'];

	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}

	$request_id = $_POST['request_id'];
	$user_id    = $_POST['user_id'];

	$groups_subscribed    = get_user_meta( $user_id, 'mg_user_group_subscribed', true );
	$groups_requested_arr = get_user_meta( $user_id, 'mg_user_requested_groups', true );
	$group_requested_id   = get_post_meta( $request_id, 'mg_request_group_id', true );
	$groups_array         = array();

	if ( empty( $groups_subscribed ) ) {
		$groups_subscribed = array();
	}

	if ( ! array_key_exists( $group_requested_id, $groups_subscribed ) ) {

		$new_groups_subscribed                        = $groups_subscribed;
		$new_groups_subscribed[ $group_requested_id ] = $groups_requested_arr[ $group_requested_id ]['group_format'];

		$groups_subscribed_arr = $new_groups_subscribed;
		foreach ( $groups_subscribed_arr as $group => $format ) {
			$groups_array[] = (string) $group;
		}

		update_user_meta( $user_id, 'mg_user_group_subscribed', $new_groups_subscribed, $groups_subscribed );
		update_user_meta( $user_id, 'mg_user_group_sub_arr', $groups_array );

		$updated_requested_groups = $groups_requested_arr;
		unset( $updated_requested_groups[ $group_requested_id ] );

		update_user_meta( $user_id, 'mg_user_requested_groups', $updated_requested_groups, $groups_requested_arr );
		update_user_meta( $user_id, 'mg_user_status', 1 );
		wp_delete_post( $request_id, true );

	} else {
		error_log( 'Group already subscrbed' );
	}


	$response = json_encode( $_POST );
	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die();
}

function wpmg_deny_group_request_callback() {
	// check nonce
	$nonce = $_POST['nextNonce'];

	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}

	$request_id           = $_POST['request_id'];
	$user_id              = $_POST['user_id'];
	$groups_subscribed    = get_user_meta( $user_id, 'mg_user_group_subscribed', true );
	$groups_requested_arr = get_user_meta( $user_id, 'mg_user_requested_groups', true );
	$group_requested_id   = get_post_meta( $request_id, 'mg_request_group_id', true );
//	$denied_requests      = get_user_meta( $user_id, 'mg_user_group_subscribed', true );

	if ( ! array_key_exists( $group_requested_id, $groups_subscribed ) ) {

		$updated_requested_groups = $groups_requested_arr;
		unset( $updated_requested_groups[ $group_requested_id ] );

		update_user_meta( $user_id, 'mg_user_requested_groups', $updated_requested_groups, $groups_requested_arr );
		wp_delete_post( $request_id, true );
//        update_post_meta($request_id,'mg_requested_denied','denied');

	} else {
		error_log( 'Group already subscribed' );
	}


	/*	if ( empty( $denied_requests ) ) {
			$denied_requests   = array();
			$denied_requests[] = $group_requested_id;
			update_user_meta( $user_id, 'mg_user_denied_request', $denied_requests );
			error_log( print_r( $denied_requests, true ) );

		} else {
			$updated_denied_request   = $denied_requests;
			$updated_denied_request[] = $group_requested_id;
			update_user_meta( $user_id, 'mg_user_denied_request', $updated_denied_request );
			error_log( print_r( $updated_denied_request, true ) );
		}*/


	$response = json_encode( $_POST );
	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die();
}

//Group Users Manager
add_action( 'wp_ajax_wpmg_remove_user', 'wpmg_remove_user_callback' );
add_action( 'wp_ajax_wpmg_add_user_to_current_group', 'wpmg_add_user_to_current_group_callback' );
function wpmg_remove_user_callback() {
	// check nonce
	$nonce = $_POST['nextNonce'];

	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}

	$groupID = $_POST['group_id'];
	$userID  = $_POST['user_id'];

	$groups_subscribed = get_user_meta( $userID, 'mg_user_group_subscribed', true );
	$groups_array      = array();

	if ( ! empty( $groups_subscribed ) ) {

		$updated_groups = $groups_subscribed;
		unset( $updated_groups[ $groupID ] );

		$groups_subscribed_arr = $updated_groups;
		foreach ( $groups_subscribed_arr as $group => $format ) {
			$groups_array[] = (string) $group;
		}
		update_user_meta( $userID, 'mg_user_group_subscribed', $updated_groups );
		update_user_meta( $userID, 'mg_user_group_sub_arr', $groups_array );
		$_POST['description'] = wpmg_showmessages_ajax( "updated", "Member has been removed successfully." );
		$_POST['action']      = 'success';

	} else {
		$_POST['description'] = wpmg_showmessages_ajax( "error", "Member has been removed unsuccessfully." );
		$_POST['action']      = 'error';
	}


	$response = json_encode( $_POST );
	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die();
}

function wpmg_add_user_to_current_group_callback() {
	// check nonce
	$nonce = $_POST['nextNonce'];

	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
		die ( 'Busted!' );
	}
	$groupID      = $_POST['group_id'];
	$email_format = $_POST['group_format'];
	$userID       = $_POST['user_id'];

	if ( empty( $email_format ) ) {
		$email_format = '1';
	}

	$groups_subscribed = get_user_meta( $userID, 'mg_user_group_subscribed', true );
	$groups_array      = array();

	if ( empty( $groups_subscribed ) ) {
		$groups_subscribed = array();
	}

	if ( ! array_key_exists( $groupID, $groups_subscribed ) ) {

		$new_groups_subscribed             = $groups_subscribed;
		$new_groups_subscribed[ $groupID ] = $email_format;

		foreach ( $new_groups_subscribed as $group => $format ) {
			$groups_array[] = (string) $group;
		}

		update_user_meta( $userID, 'mg_user_group_subscribed', $new_groups_subscribed );
		update_user_meta( $userID, 'mg_user_group_sub_arr', $groups_array );


		$_POST['description'] = wpmg_showmessages_ajax( "updated", "Member has been removed successfully." );
		$_POST['action']      = 'success';

	} else {
		$_POST['description'] = wpmg_showmessages_ajax( "error", "Member has been removed unsuccessfully." );
		$_POST['action']      = 'error';
	}


	$response = json_encode( $_POST );
	// response output
	header( "Content-Type: application/json" );
	echo $response;

	wp_die();
}

/* callback function for above ajax requests */
/* mail function used in plugin */
function wpmg_sendmessagetoSubscriber( $gid, $id, $info ) {
	global $wpdb, $objMem, $table_name_group, $table_name_requestmanager;
	$get_group   = $objMem->selectRows( $table_name_group, "", " where id='" . $gid . "'" );
	$group_name  = $get_group[0]->title;
	$get_user    = $objMem->selectRows( $table_name_requestmanager, "", " where id='" . $id . "'" );
	$sendToname  = $get_user[0]->name;
	$sendToemail = $get_user[0]->email;
	$subject     = stripslashes( $info['title'] );
	$message     = stripslashes( $info['description'] );
	$message     = str_replace( "{%name%}", $sendToname, $message );
	$message     = str_replace( "{%email%}", $sendToemail, $message );
	$message     = str_replace( "{%site_title%}", get_bloginfo( 'name' ), $message );
	$message     = str_replace( "{%site_email%}", get_bloginfo( 'admin_email' ), $message );
	$message     = str_replace( "{%site_url%}", get_site_url(), $message );
	$message     = str_replace( "{%group_name%}", $group_name, $message );
	$headers     = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
	wp_mail( $sendToemail, $subject, $message, $headers );
}

/* New subscription arrived message to admin specified email */
function wpmg_sendmessagetoAdmin( $name, $email, $grpsel ) {
	add_filter( 'wp_mail_content_type', 'wpmg_set_content_type' );

	global $wpdb, $objMem, $table_name_group, $table_name_message, $table_name_requestmanager, $WPMG_SETTINGS;

	$subscriptioncheck = $WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_CHECK"];
	$group_selected    = '';

	if ( $subscriptioncheck ) {
		$subscriptionemail = $WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_ALERT_EMAIL"];
		$get_group         = $objMem->selectRows( $table_name_group, "", " where id IN ($grpsel)" );
		foreach ( $get_group as $grp ) {
			$group_selected .= $grp->title . ",  ";
		}
		$siteGroupUrl   = admin_url( 'admin.php?page=wpmg_mailinggroup_intro' );
		$group_selected = wpmg_trimVal( $group_selected, ",  " );
		$subject        = "New Subscription Request: " . $group_selected;
		$siteTitle      = get_bloginfo( 'name' );
		$siteUrl        = home_url();
		$siteEmail      = get_bloginfo( 'admin_email' );
		$loginURL       = wp_login_url();
		$headers        = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$get_message     = $objMem->selectRows( $table_name_message, "", " where message_type = 'wpmg_sendmessagetoAdmin'" );
		$message_subject = stripslashes( $get_message[0]->message_subject );
		$dataMessage     = stripslashes( $get_message[0]->description );
		$message         = nl2br( str_replace( array(
			'{%name%}',
			'{%email%}',
			'{%site_title%}',
			'{%group_name%}',
			'{%group_url%}',
			'{%site_url%}',
			'{%site_email%}',
			'{%login_url%}'
		), array(
			$name,
			$email,
			$siteTitle,
			$group_selected,
			$siteGroupUrl,
			$siteUrl,
			$siteEmail,
			$loginURL
		), $dataMessage ) );
		$subject         = str_replace( array(
			'{%name%}',
			'{%email%}',
			'{%site_title%}',
			'{%group_name%}',
			'{%group_url%}',
			'{%site_url%}',
			'{%site_email%}',
			'{%login_url%}'
		), array(
			$name,
			$email,
			$siteTitle,
			$group_selected,
			$siteGroupUrl,
			$siteUrl,
			$siteEmail,
			$loginURL
		), $message_subject );
		wp_mail( $subscriptionemail, $subject, $message, $headers );
	}
}

/*  Redefine user notification function */
if ( ! function_exists( 'wp_new_user_notification' ) ) {
	function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
		global $wpdb, $objMem, $table_name_message;
		$siteTitle       = get_bloginfo( 'name' );
		$siteUrl         = home_url();
		$siteEmail       = get_bloginfo( 'admin_email' );
		$loginURL        = wp_login_url();
		$user            = new WP_User( $user_id );
		$user_login      = stripslashes( $user->user_login );
		$user_email      = stripslashes( $user->user_email );
		$get_message     = $objMem->selectRows( $table_name_message, "", " where message_type = 'RegistrationNotificationMailToAdmin'" );
		$dataMessage     = stripslashes( $get_message[0]->description );
		$message_subject = stripslashes( $get_message[0]->message_subject );
		$message         = nl2br( str_replace( array(
			'{%name%}',
			'{%email%}',
			'{%site_title%}',
			'{%group_name%}',
			'{%group_url%}',
			'{%site_url%}',
			'{%site_email%}',
			'{%login_url%}'
		), array(
			$user_login,
			$user_email,
			$siteTitle,
			$siteUrl,
			$siteEmail,
			$loginURL
		), $dataMessage ) );
		$subject         = str_replace( array(
			'{%name%}',
			'{%email%}',
			'{%site_title%}',
			'{%group_name%}',
			'{%group_url%}',
			'{%site_url%}',
			'{%site_email%}',
			'{%login_url%}'
		), array(
			$user_login,
			$user_email,
			$siteTitle,
			$siteUrl,
			$siteEmail,
			$loginURL
		), $message_subject );
		$headers         = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		@wp_mail( get_option( 'admin_email' ), $subject, $message, $headers );
		if ( empty( $plaintext_pass ) ) {
			return;
		}
		$get_message     = $objMem->selectRows( $table_name_message, "", " where message_type = 'RegistrationNotificationMailToMember'" );
		$dataMessage     = stripslashes( $get_message[0]->description );
		$message_subject = stripslashes( $get_message[0]->message_subject );
		$message         = nl2br( str_replace( array(
			'{%name%}',
			'{%email%}',
			'{%password%}',
			'{%site_title%}',
			'{%group_name%}',
			'{%group_url%}',
			'{%site_url%}',
			'{%site_email%}',
			'{%login_url%}'
		), array(
			$user_login,
			$user_email,
			$plaintext_pass,
			$siteTitle,
			$siteUrl,
			$siteEmail,
			$loginURL
		), $dataMessage ) );
		$subject         = str_replace( array(
			'{%name%}',
			'{%email%}',
			'{%password%}',
			'{%site_title%}',
			'{%group_name%}',
			'{%group_url%}',
			'{%site_url%}',
			'{%site_email%}',
			'{%login_url%}'
		), array(
			$user_login,
			$user_email,
			$plaintext_pass,
			$siteTitle,
			$siteUrl,
			$siteEmail,
			$loginURL
		), $message_subject );
		wp_mail( $user_email, $subject, $message, $headers );
	}
}
/*confirmation email for subscriber to verify account*/
function wpmg_sendConfirmationtoMember( $id, $groupArray ) {
	add_filter( 'wp_mail_content_type', 'wpmg_set_content_type' );
	global $wpdb, $objMem, $table_name_group, $table_name_message;
	$siteTitle    = get_bloginfo( 'name' );
	$siteUrl      = home_url();
	$siteEmail    = get_bloginfo( 'admin_email' );
	$loginURL     = wp_login_url();
	$user         = new WP_User( $id );
	$display_name = stripslashes( $user->display_name );
	$user_login   = stripslashes( $user->user_login );
	$user_email   = stripslashes( $user->user_email );
	$user_reg     = stripslashes( $user->user_registered );
	$grouplist    = '';

	if ( count( $groupArray ) > 0 ) {
		foreach ( $groupArray as $key => $group_id ) {

			$group_name = get_the_title( $group_id );
			$grouplist .= $group_name . ", ";
		}
		$grouplist = wpmg_trimVal( $grouplist, ", " );
	}

	$activationURL   = wpmg_activation_url( $id, $user_reg );
	$get_message     = $objMem->selectRows( $table_name_message, "", " where message_type = 'Confirmationemailforsubscribertoverifyaccount'" );
	$dataMessage     = stripslashes( $get_message[0]->description );
	$message_subject = stripslashes( $get_message[0]->message_subject );
	$message         = nl2br( str_replace( array(
		'{%displayname%}',
		'{%name%}',
		'{%email%}',
		'{%site_title%}',
		'{%group_name%}',
		'{%group_url%}',
		'{%site_url%}',
		'{%site_email%}',
		'{%activation_url%}',
		'{%login_url%}',
		'{%group_list%}'
	), array(
		$display_name,
		$user_login,
		$user_email,
		$siteTitle,
		$siteUrl,
		$siteEmail,
		$activationURL,
		$loginURL,
		$grouplist
	), $dataMessage ) );
	$subject         = str_replace( array(
		'{%displayname%}',
		'{%name%}',
		'{%email%}',
		'{%site_title%}',
		'{%group_name%}',
		'{%group_url%}',
		'{%site_url%}',
		'{%site_email%}',
		'{%activation_url%}',
		'{%login_url%}',
		'{%group_list%}'
	), array(
		$display_name,
		$user_login,
		$user_email,
		$siteTitle,
		$siteUrl,
		$siteEmail,
		$activationURL,
		$loginURL,
		$grouplist
	), $message_subject );
	$headers         = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	wp_mail( $user_email, $subject, $message, $headers );
}

function wpmg_set_content_type( $content_type ) {
	return 'text/html';
}

/*email user on successful registration of a group*/
function wpmg_sendGroupConfirmationtoMember( $id, $groupArray ) {
	add_filter( 'wp_mail_content_type', 'wpmg_set_content_type' );
	global $objMem, $table_name_group, $table_name_message;
	$siteTitle    = get_bloginfo( 'name' );
	$siteUrl      = home_url();
	$siteEmail    = get_bloginfo( 'admin_email' );
	$loginURL     = wp_login_url();
	$user         = new WP_User( $id );
	$display_name = stripslashes( $user->display_name );
	$user_login   = stripslashes( $user->user_login );
	$user_email   = stripslashes( $user->user_email );
	$user_reg     = stripslashes( $user->user_registered );
	$i            = 1;
	$grouplist    = '';
	if ( count( $groupArray ) > 0 ) {
		foreach ( $groupArray as $key => $value ) {
			$get_group  = $objMem->selectRows( $table_name_group, "", " where id='" . $key . "'" );
			$group_name = $get_group[0]->title;
			$grouplist .= $group_name . ", ";
		}
		$grouplist = wpmg_trimVal( $grouplist, ", " );
	}
	$get_message     = $objMem->selectRows( $table_name_message, "", " where message_type = 'Emailuseronsuccessfullregisterationofagroup'" );
	$dataMessage     = stripslashes( $get_message[0]->description );
	$message_subject = stripslashes( $get_message[0]->message_subject );
	$message         = nl2br( str_replace( array(
		'{%displayname%}',
		'{%name%}',
		'{%email%}',
		'{%site_title%}',
		'{%group_name%}',
		'{%group_url%}',
		'{%site_url%}',
		'{%site_email%}',
		'{%activation_url%}',
		'{%group_list%}',
		'{%login_url%}'
	), array(
		$display_name,
		$user_login,
		$user_email,
		$siteTitle,
		$siteUrl,
		$siteEmail,
		$grouplist,
		$loginURL
	), $dataMessage ) );
	$subject         = nl2br( str_replace( array(
		'{%displayname%}',
		'{%name%}',
		'{%email%}',
		'{%site_title%}',
		'{%group_name%}',
		'{%group_url%}',
		'{%site_url%}',
		'{%site_email%}',
		'{%activation_url%}',
		'{%group_list%}',
		'{%login_url%}'
	), array(
		$display_name,
		$user_login,
		$user_email,
		$siteTitle,
		$grouplist,
		$siteUrl,
		$siteEmail,
		$grouplist,
		$loginURL
	), $message_subject ) );
	$headers         = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	wp_mail( $user_email, $subject, $message, $headers );
}

/* mail function used in plugin */
function wpmg_activation_url( $user_id, $user_reg = "" ) {
	$md5Userid       = md5( $user_id );
	$md5UserRegister = md5( $user_reg );

	return get_bloginfo( 'wpurl' ) . "?activationkey=$md5Userid&nonce=$md5UserRegister&verify=1";
}

/* frontend shortcode call */
function wpmg_mailing_group_form_func() {
	//Updated Shortcode to properly display on front end and default to 'Public'
	ob_start();
	wp_register_style( 'demo_table.css', plugin_dir_url( __FILE__ ) . 'css/demo_table.css' );
	wp_enqueue_style( 'demo_table.css' );
	wp_register_style( 'mg_frontend.css', plugin_dir_url( __FILE__ ) . 'css/mg_frontend.css' );
	wp_enqueue_style( 'mg_frontend.css' );

	wp_register_script( 'user_form.js', plugin_dir_url( __FILE__ ) . 'js/user_form.js', array(
		'jquery'
	) );
	wp_enqueue_script( 'user_form.js' );

	wp_localize_script( 'user_form.js', 'PT_Ajax', array(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'nextNonce' => wp_create_nonce( 'myajax-next-nonce' )
		)
	);
	include "template/mg_user_form.php";

	return ob_get_clean();
}

add_shortcode( 'mailing_group_form', 'wpmg_mailing_group_form_func' );
add_filter( 'template_include', 'wpmg_check_user_activation_link', 1 );
/* frontend shortcode call */
/* user activation link check & verify */
function wpmg_check_user_activation_link( $template ) {
	global $wpdb, $objMem, $table_name_user_taxonomy;
	/* wpmg_activation_url(98, "2013-08-29 13:14:31"); */
	extract( $_GET );
	$error         = new WP_Error();
	$verify        = '';
	$activationkey = '';
	$nonce         = '';
	$unsubscribe   = '';
	$userid        = '';
	$group         = '';

	if ( $verify == '1' && $activationkey != '' && $nonce != '' ) {
		$result = $objMem->selectRows( $wpdb->users, "", " where MD5(ID) = '" . $activationkey . "' and MD5(user_registered) = '" . $nonce . "' order by id desc" );
		if ( $result[0] && is_array( $result ) ) {
			$user_status = $result[0]->user_status;
			if ( $user_status == '2' ) {
				$user_id = $result[0]->ID;
				$wpdb->query( "UPDATE $wpdb->users SET user_status = 0 WHERE ID =" . $user_id );
				update_user_meta( $user_id, "mg_user_status", 1 );
				$random_password = wp_generate_password( 12, false );
				wp_set_password( $random_password, $user_id );
				wp_new_user_notification( $user_id, $random_password );
				$gropArray   = get_user_meta( $user_id, "mg_user_group_subscribed", true );
				$arrayString = unserialize( $gropArray );
				wpmg_sendGroupConfirmationtoMember( $user_id, $arrayString );
				$error->add( 'verified_success', __( "<div align='center'>Thank you for your subscription.<br>Please check your email for your account login credentials, so you can update your preferences and profile.</div>", 'mailing-group-module' ) );
				echo $error->get_error_message( "verified_success" );
				/*  sleep(5);
					wpmg_redirectTo("wp-login.php","abs"); */
			} else {
				$error->add( 'already_verified', __( "<div align='center'><strong>Verified</strong>: Account already verified, Please <a href='wp-login.php'>login here</a>.</div>", 'mailing-group-module' ) );
				echo $error->get_error_message( "already_verified" );
				wpmg_redirectTo( "wp-login.php", "abs" );
			}
		} else {
			$error->add( 'invalid_request', __( "<div align='center'><strong>ERROR</strong>: Invalid verification request, Please contact administrator.</div>", 'mailing-group-module' ) );
			echo $error->get_error_message( "invalid_request" );
		}
	} else if ( $unsubscribe == '1' && $userid != '' && $group != '' ) {
		extract( $_GET );
		$group_arr_old = unserialize( get_user_meta( $userid, "mg_user_group_subscribed", true ) );
		unset( $group_arr_old[ $group ] );
		$grpserial = serialize( $group_arr_old );
		update_user_meta( $userid, "mg_user_group_subscribed", $grpserial );
		$objMem->updUserGroupTaxonomy( $table_name_user_taxonomy, $userid, $group_arr_old );
		$error->add( 'success_unsubscribe', __( "<div align='center'><strong>Success</strong>: You are successfully unsubscribed from the selected group.</div>", 'mailing-group-module' ) );
		echo $error->get_error_message( "success_unsubscribe" );
	} else {
		return $template;
	}
}

/* user activation link check & verify */
add_filter( 'authenticate', 'wpmg_user_signup_disable_inactive', 28 );
/* disable user with status 2 to login */
function wpmg_user_signup_disable_inactive( $user ) {
	/*  check to see if the $user has already failed logging in, if so return $user as-is */
	if ( is_wp_error( $user ) || empty( $user ) ) {
		return $user;
	}
	if ( is_a( $user, 'WP_User' ) && 2 == $user->user_status ) {
		return new WP_Error( 'invalid_username', __( "<strong>ERROR</strong>: You account has been deactivated.", 'mailing-group-module' ) );
	}

	return $user;
}


/*uninstall and deactivate code*/
/* hook to delete user taxonomy on deleting from wordpress */
/*add_action( 'delete_user', 'wpmg_delete_user_taxonomy' );
function wpmg_delete_user_taxonomy( $user_id ) {
	global $wpdb, $objMem, $table_name_user_taxonomy, $table_name_requestmanager, $table_name_requestmanager_taxonomy;
	$user_obj = get_userdata( $user_id );
	$email    = $user_obj->user_email;
	$wpdb->query( "delete from " . $table_name_user_taxonomy . " where user_id=" . $user_id );
	$get_subscription_taxonomy = $objMem->selectRows( $table_name_requestmanager, "", " where email = '" . $email . "'" );
	$subscriptoinid            = $get_subscription_taxonomy[0]->id;
	$wpdb->query( "delete from " . $table_name_requestmanager_taxonomy . " where user_id = " . $subscriptoinid );
	$wpdb->query( "delete from " . $table_name_requestmanager . " where id = " . $subscriptoinid );
}*/

function wpmg_custom_menu_hack() {
	/* Custom menu hack */
	$pagename  = sanitize_text_field( $_GET['page'] );
	$pageArray = array(
		"wpmg_mailinggroup_list",
		"wpmg_mailinggroup_adminmessagelist",
		"wpmg_mailinggroup_memberlist",
		"wpmg_mailinggroup_memberadd",
		"wpmg_mailinggroup_requestmanagerlist",
		"wpmg_mailinggroup_requestmanageradd",
		"wpmg_mailinggroup_sendmessage",
		"wpmg_mailinggroup_intro",
		"wpmg_mailinggroup_messagelist",
		"wpmg_mailinggroup_messageadd",
		"wpmg_mailinggroup_style",
		"wpmg_mailinggroup_contact"
	);
	if ( $pagename != "" && ( in_array( $pagename, $pageArray ) ) ) {
		wp_register_script( 'custommenu.js', plugin_dir_url( __FILE__ ) . 'js/custommenu.js', array(
			'jquery'
		) );
		wp_enqueue_script( 'custommenu.js' );
	}
}

add_action( 'admin_menu', 'wpmg_custom_menu_hack' );
function wpmg_print_message( $message, $is_error = false ) {
	if ( $is_error ) {
		echo '<div id="message" class="error">';
	} else {
		echo '<div id="message" class="updated fade">';
	}
	echo "<p><strong>Mailing Group Manager: $message</strong></p></div>";
}

function wpmg_get_user_role() {
	global $current_user;
	$user_roles = $current_user->roles;
	$user_role  = array_shift( $user_roles );

	return $user_role;
}

function wpmg_add_menu_icons_styles() {
	?>
	<style>
		#adminmenu .toplevel_page_mailinggroup_intro div.wp-menu-image:before {
			content: '\f237';
		}
	</style>
	<?php
}

add_action( 'admin_head', 'wpmg_add_menu_icons_styles' );
add_filter( 'authenticate', 'wpmg_bainternet_allow_email_login', 20, 3 );
function wpmg_bainternet_allow_email_login( $user, $username, $password ) {
	if ( is_email( $username ) ) {
		$user = get_user_by( 'email', $username );
		if ( $user ) {
			$username = $user->user_login;
		}
	}

	return wp_authenticate_username_password( null, $username, $password );
}


add_action( 'wpmg_cron_task_send_email', 'wpmg_cron_send_email' );
require_once( "crons/wpmg_cron_send_email.php" );

add_action( 'wpmg_cron_task_parse_email', 'wpmg_cron_parse_email' );
require_once( "crons/wpmg_cron_parse_email.php" );

add_action( 'wpmg_cron_task_bounced_email', 'wpmg_cron_bounced_email' );
require_once( "crons/wpmg_cron_bounced_email.php" );


