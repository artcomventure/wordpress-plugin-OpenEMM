<?php

/**
 * OpenEMM shortcode for inline use.
 */
add_shortcode( 'openemm', 'openemm_shortcode' );
function openemm_shortcode( $args = array() ) {
	return openemm_form( openemm_get_settings( $args ) );
}
