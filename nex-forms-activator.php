<?php
/**
 * @wordpress-plugin
 * Plugin Name:       NEX-Forms Activator
 * Plugin URI:        https://github.com/wp-activators/nex-forms-activator
 * Description:       NEX-Forms Plugin Activator ✨ (Let's Play a Game)
 * Version:           1.3.0
 * Requires at least: 5.9.0
 * Requires PHP:      7.2
 * Author:            mohamedhk2
 * Author URI:        https://github.com/mohamedhk2
 **/

defined( 'ABSPATH' ) || exit;

$NEX_FORMS_ACTIVATOR_NAME   = 'NEX-Forms Activator';
$NEX_FORMS_ACTIVATOR_DOMAIN = 'nex-forms-activator';
$functions                  = require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
extract( $functions );
$directory = is_dir( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'nex-forms' ) ? 'nex-forms' : 'nex-forms-express-wp-form-builder';
if (
	$activator_admin_notice_ignored()
	|| $activator_admin_notice_plugin_install( "$directory/main.php", 'nex-forms-express-wp-form-builder', 'NEX-Forms - Ultimate', $NEX_FORMS_ACTIVATOR_NAME, $NEX_FORMS_ACTIVATOR_DOMAIN )
	|| $activator_admin_notice_plugin_activate( "$directory/main.php", $NEX_FORMS_ACTIVATOR_NAME, $NEX_FORMS_ACTIVATOR_DOMAIN )
) {
	return;
}
update_option( 'nf_activated', true );
add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) use ( $activator_json_response ) {
	switch ( $url ) {
		case 'https://basixonline.net/activate-license-new-api-v3':
			switch ( true ) {
				#license checking
				case $parsed_args['body']['check_key'] ?? false :
					return $activator_json_response( [
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
					return $activator_json_response( [
						'error'   => 0,
						'key'     => 'license',
						'pc'      => 'free4all',
						'message' => 'License Activated Successfully',
					] );
			}
	}

	return $pre;
}, 99, 3 );
add_filter( 'pre_update_option_nf_activated', $cb = function ( $value = null, $old_value = null, $option = null ) {
	return true;
}, 99, 3 );
add_filter( 'pre_option_nf_activated', $cb, 99, 3 );
add_filter( 'http_request_args', function ( $parsed_args, $url ) {
	global $wp_filter, $directory;
	$plugin_path = realpath( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR );
	foreach ( $wp_filter['pre_http_request'] as $priority => $filter ) {
		foreach ( $filter ?? [] as $value ) {
			$reflection = new ReflectionFunction( $value['function'] );
			$file_path  = realpath( $reflection->getFileName() );
			if ( str_contains( $file_path, $plugin_path ) ) {
				remove_filter( 'pre_http_request', $value['function'], $priority );
			}
		}
	}

	return $parsed_args;
}, 99, 2 );
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Patcher.php';

use \NexFormsActivator\Patcher;

$I_WANT_TO_PLAY_A_GAME = [
	'/main.php',
	'/includes/load.php',
	'/includes/classes/class.db.php',
	'/includes/classes/class.googlefonts.php',
	'/includes/classes/class.icons.php',
	'/includes/classes/class.install.php',
	'/includes/classes/class.preferences.php',
];
foreach ( $I_WANT_TO_PLAY_A_GAME as $a_game ) {
	try {
		$path    = realpath( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $directory . $a_game );
		$patcher = new Patcher( $path );
		$patcher->setSearch( '/\$check_directory \= is_dir\( WP_PLUGIN_DIR \. DIRECTORY_SEPARATOR \. \'nex\-forms\-activator\-main\' \)\;/' )
		        ->setCheck( Patcher::CHECK_NOT )
		        ->setReplace( '$check_directory = false;' )
		        ->setEol( null );
		if ( $patcher->canModified() && ! $patcher->isModified() ) {
			$new_content = $patcher->makeChange();
			if ( $patcher->isSuccessful() && $new_content ) {
				$file_put_contents = file_put_contents( $path, $new_content );
			}
		}
	} catch ( \Exception $ex ) {
	}
}
