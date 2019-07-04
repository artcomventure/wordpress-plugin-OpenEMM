<?php

/**
 * Plugin Name: OpenEMM
 * Plugin URI: https://github.com/artcomventure/wordpress-plugin-openemm
 * Description: OpenEMM Newsletter subscription.
 * Version: 1.3.0
 * Text Domain: openemm
 * Author: artcom venture GmbH
 * Author URI: http://www.artcom-venture.de/
 */

if ( ! defined( 'OPENEMM_PLUGIN_URL' ) ) define( 'OPENEMM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'OPENEMM_PLUGIN_DIR' ) ) define( 'OPENEMM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'OPENEMM_PLUGIN_FILE' ) ) define( 'OPENEMM_PLUGIN_FILE', __FILE__ );
if ( ! defined( 'OPENEMM_PLUGIN_BASENAME' ) ) define( 'OPENEMM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'openemm_scripts' );
function openemm_scripts() {
	wp_enqueue_script( 'openemm', OPENEMM_PLUGIN_URL . 'js/scripts.js', array(), '1.1.0', true );
	wp_enqueue_style( 'openemm', OPENEMM_PLUGIN_URL . 'css/styles.css', array( 'dashicons' ), '1.1.5' );
}

// i18n
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'openemm', OPENEMM_PLUGIN_DIR . 'languages' );
} );

/**
 * Get OpenEMM settings.
 * @param array $form (defaults)
 * @return array
 */
function openemm_get_settings( $form = array() ) {
	if ( !is_array($form) ) $form = array();
    $settings = get_option( 'openemm', array() );

    $defaults = array(
		'doubleoptin' => '0',
		'mailinglist' => '',

		'webservice' => array( 'wsdlUrl' => '', 'username' => '', 'password' => '' ),
		'form' => array( 'gender' => 0, 'title' => 0, 'email' => 0, 'firstname' => 0, 'lastname' => 0, 'mailtype' => 0, 'button' => '' ),
		'messages' => array( 'double_opt_in' => '', 'broken_confirmation_link' => '', 'already_subscribed' => '', 'confirmed' => '' ),
		'email' => array( 'sender' => '', 'subject' => '', 'body' => '' ),
		'notification' => array( 'recipient' => '', 'subject' => '' )
	);

    // allow custom fields but make sure to keep default ones
    $defaults['form'] = apply_filters( 'openemm_form_fields', $defaults['form'] ) + $defaults['form'];

    // merge defaults
	$settings += array( 'form' => array() );
	$settings['form'] = $form + $settings['form'];
	foreach ( $defaults as $group => $value ) {
		if ( is_array($value) ) {
			if ( !isset($settings[$group]) ) $settings[$group] = array();
			$settings[$group] += $value;
		}
		elseif ( !isset($settings[$group]) ) $settings[$group] = $value;
	}

	// only allow items mentioned in $defaults
    foreach ( $settings as $group => $fields ) {
    	if ( !key_exists( $group, $defaults ) ) {
    		unset($settings[$group]);
	    }
    	elseif ( is_array($fields) ) {
		    foreach ( $fields as $field => $fields ) {
			    if ( !key_exists( $field, $defaults[$group] ) ) {
				    unset($settings[$group][$field]);
			    }
		    }
	    }
    }

    // 'mailinglist' is array of IDs
//    $settings['mailinglist'] = array_filter( array_map( 'trim', explode( ',', $settings['mailinglist'] ) ) );

	// email is mandatory
    $settings['form']['email'] = 2;

    // wrong form field value => disable
	$settings['form'] = array_combine( array_keys($settings['form']), array_map( function( $value, $field ) {
		if ( $field == 'button' ) return $value;
		return in_array( intval($value), array(0,1,2) ) ? intval($value) : 0;
	}, $settings['form'], array_keys($settings['form']) ));

    return $settings;
}

// get specific settings
function openemm_get_setting( $setting, $form = array() ) {
	$settings = openemm_get_settings( $form );

	if ( !is_array($setting) ) $setting = array( $setting );

	foreach ( $setting as $key ) {
		if ( !isset( $settings[$key] ) ) {
			$settings = null;
			break;
		}

		$settings = $settings[ $key ];
	}

	return $settings;
}

/**
 * @param $key
 * @param bool $default
 * @return mixed|string|void
 */
function openemm_get_message( $key, $default = false ) {
	switch ( $key ) {
		default:
			$message = '';
			break;

		case 'error':
			$message = 'An error has occurred. That should not have happened! Please try again.';
			break;

		case 'double_opt_in':
			$message = 'Please check your email mailbox to confirm your newsletter subscription.';
			break;

		case 'broken_confirmation_link':
			$message = 'The confirmation link is broken. Please subscribe to our newsletter again.';
			break;

		case 'already_subscribed':
			$message = 'This email address already subscribed to our newsletter.';
			break;

		case 'confirmed':
			$message = 'You successfully confirmed your newsletter subscription. Thank you for subscribing our newsletter!';
			break;
	}

	$param_arr = array( __( $message, 'openemm' ) );
	if ( isset($args) ) $param_arr = array_merge( $param_arr, $args );
	$message = call_user_func_array( 'sprintf', $param_arr );

	if ( !$default ) {
		$settings = openemm_get_settings();

		if ( !empty($settings['messages'][$key]) ) {
			$message = $settings['messages'][$key];
		}
	}

	return $message;
}

/**
 * @param $field
 * @param bool $echo
 * @return string
 */
function openemm_get_label( $field, $echo = true ) {
	switch ( $field ) {
		default:
			$label = __( ucfirst($field), 'openemm' );
			break;

		case 'title':
		case 'username':
		case 'password':
			$label = __( ucfirst($field) );
			break;

		case 'email':
			$label = __( 'Email Address', 'openemm' );
			break;

		case 'firstname':
			$label = __( 'First Name', 'openemm' );
			break;

		case 'lastname':
			$label = __( 'Last Name', 'openemm' );
			break;

		case 'mailtype':
			$label = __( 'Mail Type', 'openemm' );
			break;

		case 'wsdlUrl':
			$label = __( 'WSDL URL', 'openemm' );
			break;
	}

	// allow custom labels ... but only for form
	if ( array_key_exists( $field, openemm_get_settings()['form'] ) ) {
		$label = apply_filters( 'openemm_label', $label, $field );
	}

	if ( !$echo ) return $label;
	echo $label;
}

/**
 * @param bool $message
 * @return OpenEMM|string
 */
function OpenEMM( $message = false ) {
	$settings = openemm_get_settings();

	try {
		include_once( OPENEMM_PLUGIN_DIR . 'inc/OpenEMM.class.php' );
		$OpenEMM = new OpenEMM( $settings['webservice']['wsdlUrl'], $settings['webservice']['username'], $settings['webservice']['password'] );

		// check credentials by test call
		try { $OpenEMM->subscriberExists( 'just@some.email' ); }
		catch ( SoapFault $e ) {
			throw new Exception( sprintf( __( "Wrong credentials. Please check webservice's %s and %s.", 'openemm' ),
					'<code>' . __( 'Username' ) . '</code>',
					'<code>' . __( 'Password' ) . '</code>')
			);
		}
	}
	catch ( Exception $e ) { $OpenEMM = $e->getMessage(); }

	if ( !$message && is_string($OpenEMM) ) return false;
	return $OpenEMM;
}

/**
 * @return bool
 */
function openemm_doing_ajax() {
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// includes
include( OPENEMM_PLUGIN_DIR . 'inc/install.php' );
include( OPENEMM_PLUGIN_DIR . 'inc/cypher.php' );
include( OPENEMM_PLUGIN_DIR . 'inc/rewrite.php' );
include( OPENEMM_PLUGIN_DIR . 'inc/db.php' );
include( OPENEMM_PLUGIN_DIR . 'inc/admin.settings.php' );
include( OPENEMM_PLUGIN_DIR . 'inc/theme.php' );
include( OPENEMM_PLUGIN_DIR . 'inc/shortcode.php' );

// maybe flush rewrite rules
add_action( 'init', function() {
	if ( get_option( 'openemm_flush_rewrite_rules', 0 ) ) {
		delete_option( 'openemm_flush_rewrite_rules' );
		flush_rewrite_rules();
	}
} );

// remove (maybe) update notification
add_filter( 'site_transient_update_plugins', function( $value ) {
	if ( isset( $value->response[OPENEMM_PLUGIN_BASENAME] ) ) {
		unset( $value->response[OPENEMM_PLUGIN_BASENAME] );
	}

	return $value;
} );

// change details link to GitHub repository
add_filter( 'plugin_row_meta', function( $links, $file ) {
	if ( OPENEMM_PLUGIN_BASENAME == $file ) {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $file );

		$links[2] = '<a href="' . $plugin_data['PluginURI'] . '">' . __( 'Plugin-Seite aufrufen' ) . '</a>';
	}

	return $links;
}, 10, 2 );
