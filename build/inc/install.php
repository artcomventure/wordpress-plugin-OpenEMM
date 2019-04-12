<?php

// maybe run updates
add_action( 'plugins_loaded', function() {
	// check actual version vs target version
	if ( version_compare( openemm_get_version(), openemm_get_version( 'target' ) ) < 0 ) {
		openemm_install();
		// db and variables are up to date
		// so we set the actual version number to the current plugin's one
		update_option( 'openemm_version', openemm_get_version( 'target' ) );
	}
} );

/**
 * Install db scheme, update variables, ...
 * https://codex.wordpress.org/Creating_Tables_with_Plugins
 */
//register_activation_hook( OPENEMM_PLUGIN_FILE, 'openemm_install' );
function openemm_install() {
	global $wpdb;

	$table_name = openemm_get_table_name();
	$charset_collate = $wpdb->get_charset_collate();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// initial db version
	if ( version_compare( openemm_get_version(), '1.0.0' ) < 0 ) {
		// db scheme
		dbDelta( "CREATE TABLE " . $table_name . " (
			id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            hash VARCHAR(55) DEFAULT '' NOT NULL,
            registered datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            confirmed datetime NOT NULL default '0000-00-00 00:00:00',
            UNIQUE KEY id (id),
            PRIMARY KEY (id)
        ) $charset_collate;" );

		// set actual version number
		update_option( 'openemm_db_version', '1.0.0' );
	}

	// add 'data' column
	// new option structure
	if ( version_compare( openemm_get_version(), '1.1.0' ) < 0 ) {
		// db scheme
		dbDelta( "CREATE TABLE " . $table_name . " (
            id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            data longtext DEFAULT '' NOT NULL,
            hash VARCHAR(55) DEFAULT '' NOT NULL,
            registered datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            confirmed datetime NOT NULL default '0000-00-00 00:00:00',
            UNIQUE KEY id (id),
            PRIMARY KEY (id)
        ) $charset_collate;" );

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

		// save 'new' option
		update_option('openemm', $settings );

		// cleanup
		delete_option( 'openemm_mailing_list_id' );
		delete_option( 'openemm_url' );
		delete_option( 'openemm_username' );
		delete_option( 'openemm_password' );
		delete_option( 'openemm_email_sender' );
		delete_option( 'openemm_email_subject' );
		delete_option( 'openemm_email_message' );

		// mark rewrite rules to be flushed (see 'init' action)
		update_option( 'openemm_flush_rewrite_rules', 1 );

		// set actual version number
		update_option( 'openemm_db_version', '1.1.0' );
	}

	if ( version_compare( openemm_get_version(), '1.1.1' ) < 0 ) {
		delete_option( 'openemm_db_version' );
		// set actual version number
		update_option( 'openemm_version', '1.1.1' );
	}
}

// remove all traces
register_deactivation_hook( OPENEMM_PLUGIN_FILE, 'openemm_uninstall' );
function openemm_uninstall() {
	$table_name = openemm_get_table_name();

	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

	delete_option('openemm_db_version');
	delete_option('openemm_version'); // since version 1.1.1
	delete_option('openemm');
}
