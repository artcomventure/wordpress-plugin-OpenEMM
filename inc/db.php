<?php

global $openemm_db_version;
$openemm_db_version = '1.1';

/**
 * Install db scheme.
 * https://codex.wordpress.org/Creating_Tables_with_Plugins
 */
//register_activation_hook( OPENEMM_PLUGIN_FILE, 'openemm_install' );
function openemm_install( $db_version = 0 ) {
	global $wpdb, $openemm_db_version;
	$db_version = $db_version ?: $openemm_db_version;

    $table_name = openemm_get_table_name();
	$charset_collate = $wpdb->get_charset_collate();

    switch ( $db_version ) {
	    case 1:
		    $sql = "CREATE TABLE " . $table_name . " (
                id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                email VARCHAR(100) NOT NULL,
                hash VARCHAR(55) DEFAULT '' NOT NULL,
                registered datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                confirmed datetime NOT NULL default '0000-00-00 00:00:00',
                UNIQUE KEY id (id),
                PRIMARY KEY (id)
            ) $charset_collate;";
	    	break;

	    // add 'data' column
	    case 1.1:
		    $sql = "CREATE TABLE " . $table_name . " (
                id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
                email VARCHAR(100) NOT NULL,
                data longtext DEFAULT '' NOT NULL,
                hash VARCHAR(55) DEFAULT '' NOT NULL,
                registered datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                confirmed datetime NOT NULL default '0000-00-00 00:00:00',
                UNIQUE KEY id (id),
                PRIMARY KEY (id)
            ) $charset_collate;";

		    // 'convert' options
		    $settings = openemm_get_settings();
		    $settings['mailinglist'] = get_option( 'openemm_mailing_list_id', $settings['mailinglist'] );
		    $settings['webservice'] = array(
		    	'wsdlUrl' => get_option( 'openemm_url', $settings['webservice']['wsdlUrl'] ),
		    	'username' => get_option( 'openemm_username', $settings['webservice']['username'] ),
		    	'password' => get_option( 'openemm_password', $settings['webservice']['password'] ),
		    );
		    $settings['email'] = array(
		    	'sender' => get_option( 'openemm_email_sender', $settings['email']['sender'] ),
			    'subject' => get_option( 'openemm_email_subject', $settings['email']['subject'] ),
			    'body' => get_option( 'openemm_email_message', $settings['email']['body'] )
		    );

		    // save option
		    update_option('openemm', $settings );

		    // cleanup
		    delete_option( 'openemm_mailing_list_id' );
		    delete_option( 'openemm_url' );
		    delete_option( 'openemm_username' );
		    delete_option( 'openemm_password' );
		    delete_option( 'openemm_email_sender' );
		    delete_option( 'openemm_email_subject' );
		    delete_option( 'openemm_email_message' );
	    	break;
    }

    if ( isset($sql) ) {
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    dbDelta( $sql );
    }

	update_option( 'openemm_db_version', $db_version );
}

/**
 * Db updates.
 */
add_action( 'plugins_loaded', 'openemm_db_update' );
function openemm_db_update() {
	global $openemm_db_version;
	if ( openemm_get_db_version() != $openemm_db_version ) {
		openemm_install( $openemm_db_version );
//		flush_rewrite_rules();
	}
}

/**
 * Get OpenEMM table name.
 * @return string
 */
function openemm_get_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'openemm';
}

/**
 * Get current installed db version.
 * @return mixed
 */
function openemm_get_db_version() {
	return get_site_option( 'openemm_db_version', 0 );
}

/**
 * @param $needle
 * @param string $column
 * @return object|null
 */
function openemm_get_subscriber( $needle, $column = '' ) {
	global $wpdb;
	$table_name = openemm_get_table_name();

	if ( !$column ) {
		$column = 'hash';
		if ( is_email($needle) ) $column = 'email';
		elseif ( is_numeric($needle) ) $column = 'id';
	}

	if ( $subscriber = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE {$column} = '%s' LIMIT 1", $needle)) ) {
		$subscriber = $subscriber[0];

		if ( !($subscriber->data = maybe_unserialize( $subscriber->data )) || !is_array( $subscriber->data ) ) {
			$subscriber->data = array();
		}
	}

	return $subscriber;
}

/**
 * @param string $email
 * @param array $data
 * @return bool|array
 */
function openemm_add_subscriber( $email, $data = array() ) {
    // no valid email
    if ( !is_email($email) ) return array(
    	'type' => 'error',
	    'message' => __( 'A valid email address is required.' )
    );

	global $wpdb;
	$table_name = openemm_get_table_name();

    if ( $subscriber = openemm_get_subscriber( $email, 'email' ) ) {
        // email is already confirmed
        if ( $subscriber->confirmed != '0000-00-00 00:00:00' )
            return array(
                'type' => 'success',
                'message' => openemm_get_message( 'already_subscribed' ),
            );
        else {
        	// (maybe) update subscriber data
	        $wpdb->update( $table_name, array(
		        'data' => serialize($data + $subscriber->data)
	        ), array( 'hash' => ($hash = $subscriber->hash) ), array( '%s' ), array( '%s' ) );
        }
    } else $wpdb->insert( $table_name, array(
	    'email' => $email,
	    'hash' => ($hash = wp_hash(current_time( 'mysql' ) . '|' . $email)),
	    'data' => serialize($data)
    ) );

	// send confirmation email
    if ( ($settings = openemm_get_settings())['doubleoptin'] ) {
    	if ( !$subject = $settings['email']['subject'] ) $subject = sprintf( __( 'Confirm your newsletter subscription from %s', 'openemm' ), get_option( 'home' ) );
	    $message = $settings['email']['body'];
	    if ( strpos( $message, '[openemm_confirmation_link]' ) === false ) $message .= "\n\n[openemm_confirmation_link]";
	    $message = str_replace('[openemm_confirmation_link]', add_query_arg( array( 'hash' => $hash ), home_url( 'openemm/confirm' ) ), $message);

	    $headers = 'From: ' . ($settings['email']['subject'] ? $settings['email']['subject'] : get_bloginfo( 'title' ) . '<' . get_option( 'admin_email' ) . '>' ) . "\r\n";

	    wp_mail( $email, $subject, trim($message), $headers );

	    return array(
		    'type' => 'success',
		    'message' => openemm_get_message( 'double_opt_in' ),
	    );
    }
    // no double opt in ... immediately confirm
    else return openemm_confirm_subscriber( $hash );
}

/**
 * @param string $hash
 * @return array
 */
function openemm_confirm_subscriber( $hash ) {
	$OpenEMM = OpenEMM();

	// broken hash
	if ( !$subscriber = openemm_get_subscriber( $hash, 'hash' ) ) {
		return array(
			'type' => 'error',
			'message' => openemm_get_message( 'broken_confirmation_link' ),
		);
	}
	// already subscribed
	elseif ( $subscriber->confirmed != '0000-00-00 00:00:00' ) {
		return array(
			'type' => 'success',
			'message' => openemm_get_message( 'already_subscribed' ),
		);
	}
	else {
		global $wpdb;
		$table_name = openemm_get_table_name();

		if ( $OpenEMM ) {
			$OpenEMM->setSubscription( $subscriber->email );

			$wpdb->update( $table_name, array(
				'confirmed' => current_time( 'mysql' )
			), array( 'hash' => $hash ), array( '%s' ), array( '%s' ) );
		}
		else return array(
			'type' => 'error',
			'message' => openemm_get_message( 'error' ),
		);

		return array(
			'type' => 'success',
			'message' => openemm_get_message( 'confirmed' ),
		);
	}
}
