<?php
/**
 * @wordpress-plugin
 * Plugin Name:       NEX-Forms Activator
 * Plugin URI:        https://github.com/wp-activators/nex-forms-activator
 * Description:       NEX-Forms Plugin Activator
 * Version:           1.2.0
 * Requires at least: 3.1.0
 * Requires PHP:      7.2
 * Author:            mohamedhk2
 * Author URI:        https://github.com/mohamedhk2
 **/

defined( 'ABSPATH' ) || exit;
const NEX_FORMS_ACTIVATOR_NAME   = 'NEX-Forms Activator';
const NEX_FORMS_ACTIVATOR_DOMAIN = 'nex-forms-activator';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
$directory = is_dir( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'nex-forms' ) ? 'nex-forms' : 'nex-forms-express-wp-form-builder';
if (
	activator_admin_notice_ignored()
	|| activator_admin_notice_plugin_install( "$directory/main.php", 'nex-forms-express-wp-form-builder', 'NEX-Forms - Ultimate', NEX_FORMS_ACTIVATOR_NAME, NEX_FORMS_ACTIVATOR_DOMAIN )
	|| activator_admin_notice_plugin_activate( "$directory/main.php", NEX_FORMS_ACTIVATOR_NAME, NEX_FORMS_ACTIVATOR_DOMAIN )
) {
	return;
}
update_option( 'nf_activated', true );
add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) {
	switch ( $url ) {
		case 'https://basixonline.net/activate-license-new-api-v3':
			switch ( true ) {
				#license checking
				case $parsed_args['body']['check_key'] ?? false :
					return activator_json_response( [
						'client_info'  => [
							'purchase_code'    => 'free4all',
							'envato_user_name' => 'byMHK',
							'license_type'     => 'PRO',
							'for_site'         => 'ALL',
							'date_puchased'    => date( 'Y-m-d' ),
						],
						'license_info' => [
							'supported_until' => date( 'Y-m-d', strtotime( '+1000 year' ) ),
						],
						'ver'          => 'true',
					] );
				# license verification
				case $parsed_args['body']['verify-2'] ?? false :
					return activator_json_response( [
						'error'   => 0,
						'key'     => 'license',
						'pc'      => 'free4all',
						'message' => 'License Activated Successfully',
					] );
			}
	}

	return $pre;
}, 99, 3 );
