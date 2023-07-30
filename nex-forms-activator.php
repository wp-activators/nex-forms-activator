<?php
/**
 * @wordpress-plugin
 * Plugin Name:       NEX-Forms Activator
 * Plugin URI:        https://github.com/wp-activators/nex-forms-activator
 * Description:       NEX-Forms Plugin Activator
 * Version:           1.0.0
 * Requires at least: 5.3.0
 * Requires PHP:      7.4
 * Author:            mohamedhk2
 * Author URI:        https://github.com/mohamedhk2
 **/

defined( 'ABSPATH' ) || exit;
const NEX_FORMS_ACTIVATOR_NAME   = 'NEX-Forms Activator';
const NEX_FORMS_ACTIVATOR_DOMAIN = 'nex-forms-activator';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
if (
	activator_admin_notice_ignored()
	|| activator_admin_notice_plugin_install( 'nex-forms-express-wp-form-builder/main.php', 'nex-forms-express-wp-form-builder', 'NEX-Forms - Ultimate', NEX_FORMS_ACTIVATOR_NAME, NEX_FORMS_ACTIVATOR_DOMAIN )
	|| activator_admin_notice_plugin_activate( 'nex-forms-express-wp-form-builder/main.php', NEX_FORMS_ACTIVATOR_NAME, NEX_FORMS_ACTIVATOR_DOMAIN )
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
					return [
						'response' => [ 'code' => 200, 'message' => 'OK' ],
						'body'     => json_encode( [
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
						] )
					];
				# license verification
				case $parsed_args['body']['verify-2'] ?? false :
					return [
						'response' => [ 'code' => 200, 'message' => 'OK' ],
						'body'     => json_encode( [
							'error'   => 0,
							'key'     => 'license',
							'pc'      => 'free4all',
							'message' => 'License Activated Successfully',
						] )
					];
			}
	}

	return $pre;
}, 99, 3 );
