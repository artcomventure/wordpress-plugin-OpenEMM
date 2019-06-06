<?php

/**
 * Register 'openemm' query var.
 */
add_filter( 'query_vars', function( $public_query_vars ) {
	$public_query_vars[] = 'openemm';
	return $public_query_vars;
});

/**
 * Create 'openemm' rewrite rule.
 */
add_filter( 'generate_rewrite_rules', function( $wp_rewrite ) {
	$wp_rewrite->rules = array_merge(array (
		'openemm/([^/]+)' => 'index.php?openemm=$matches[1]',
	), $wp_rewrite->rules);
} );

/**
 * ...
 */
add_action( 'template_redirect', function() {
	global $wp_query;
	if ( empty( $wp_query->query_vars['openemm'] ) ) return;

	switch ( $wp_query->query_vars['openemm'] ) {
		case 'submit':
		case 'subscribe':
			// sth. wrong :/
			if ( empty($_POST['form']) || !openemm_doing_ajax() ) {
				if ( !openemm_doing_ajax() ) wp_redirect( home_url( isset($_GET['destination']) ? $_GET['destination'] : '' ) );
				exit;
			}

			$response = openemm_form( openemm_get_settings( (new OpenEMMCrypt())->decrypt( $_POST['form'] ) ) );
			if ( is_array( $response ) || is_object( $response ) ) {
				$response = json_encode( $response );
			}

			echo $response;
			exit;

		case 'confirm':
            set_transient( 'openemm', openemm_confirm_subscriber( $_GET['hash'] ?: '' ) );

			add_filter( 'template_include', function() {
				if ( !file_exists( $template = get_stylesheet_directory() . '/page--openemm.php' ) ) {
				    if ( !file_exists( $template = get_template_directory() . '/page--openemm.php' ) ) {
                        $template = OPENEMM_PLUGIN_DIR . 'page.php';
                    }
				}

				return $template;
			} );
			break;
	}
} );
