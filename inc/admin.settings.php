<?php

// register settings
add_action( 'admin_init', function() {
    register_setting( 'openemm', 'openemm' );
} );

// styles and scripts
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_script( 'openemm-admin', OPENEMM_PLUGIN_URL . 'js/admin.settings.js', array( 'jquery-ui-sortable' ), '20190404', true );
    wp_enqueue_style( 'openemm-admin', OPENEMM_PLUGIN_URL . 'css/admin.settings.css', array(), '20190404' );
} );

// register admin menu item and settings page
add_action( 'admin_menu', function() {
    add_options_page(
        __( 'OpenEMM', 'openemm' ),
        __( 'OpenEMM', 'openemm' ),
        'manage_options',
        'openemm-settings',
        function() { ?>

<div class="wrap">
    <h2><?php _e( 'OpenEMM Settings', 'openemm' ); ?></h2>

    <form id="openemm-settings-form" method="post" action="options.php">
        <?php settings_fields( 'openemm' );
        $settings = openemm_get_settings();
        $OpenEMM = OpenEMM( true ); // test webservice ?>

        <div class="nav-tab-wrapper hide-if-no-js">
            <a href="#openemm-general-settings" class="nav-tab<?php echo empty($settings['mailinglist']) ? ' error' : '' ?>"><?php _e( 'General' ); ?></a>
            <a href="#openemm-webservice-settings" class="nav-tab<?php echo isset($OpenEMM) && is_string($OpenEMM) ? ' error' : ''; ?>"><?php _e( 'Webservice', 'openemm' ); ?></a>
            <a href="#openemm-form-settings" class="nav-tab"><?php _e( 'Form', 'openemm' ); ?></a>
            <a href="#openemm-messages-settings" class="nav-tab"><?php _e( 'Messages', 'openemm' ); ?></a>
            <a href="#openemm-email-settings" class="nav-tab"><?php _e( 'Double Opt In Email', 'openemm' ); ?></a>
        </div>

	    <?php // error messages
        if ( empty($settings['mailinglist']) ) printf(
		    '<div class="notice notice-%1$s inline"><p>%2$s</p></div>',
		    esc_attr( 'error' ), sprintf( __( '%s is required.', 'openemm' ), '<code>' . __( 'Mailing List ID', 'openemm' ) . '</code>' )
	    );

	    if ( isset($OpenEMM) && is_string($OpenEMM) ) printf(
		    '<div class="notice notice-%1$s inline"><p>%2$s</p></div>',
		    esc_attr( 'error' ), $OpenEMM
	    ); ?>

        <div id="openemm-general-settings">
            <h2 class="title hide-if-js"><?php _e( 'General' ); ?></h2>

            <table id="general" class="form-table">
                <tbody>

                <tr valign="top">
                    <th scope="row"><label for="openemm-general-mailinglist-setting" class="required"><?php _e( 'Mailing List ID' ) ?></label></th>
                    <td>
                        <input id="openemm-general-mailinglist-setting" type="text" class="regular-text" name="openemm[mailinglist]"
                               value="<?php echo $settings['mailinglist']; ?>" />
                        <?php if ( false ): ?>
                        <p class="description"><?php _e( 'For subscription to multiple mailing lists separate IDs with comma.', 'openemm' ); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e( 'Double Opt In' ) ?></th>
                    <td>
                        <label><input type="checkbox" name="openemm[doubleoptin]"
                            value="1"<?php checked( '1', $settings['doubleoptin'] ); ?> />
	                        <?php _e( 'User must confirm subscription by confirm email.', 'openemm' ); ?>
                        </label>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>

        <div id="openemm-webservice-settings">
            <h2 class="title hide-if-js"><?php _e( 'Webservice', 'openemm' ); ?></h2>

            <table id="webservice-settings" class="form-table">
                <tbody>

		        <?php foreach ( $settings['webservice'] as $field => $value ): ?>
                    <tr valign="top">
                        <th scope="row"><label for="openemm-webservice-<?php echo $field; ?>-setting" class="required"><?php openemm_get_label( $field ); ?></label></th>
                        <td>
                            <input id="openemm-webservice-<?php echo $field; ?>-setting" type="text" class="regular-text" name="openemm[webservice][<?php echo $field; ?>]"
                                   value="<?php echo $settings['webservice'][$field]; ?>" />

					        <?php if ( $field == 'wsdlUrl' ): ?>
                                <p class="description"><?php _e( 'URL to the OpenEMM webservice file.', 'openemm' ); ?></p>
					        <?php endif; ?>
                        </td>
                    </tr>
		        <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <div id="openemm-form-settings">
            <h2 class="title hide-if-js"><?php _e( 'Form', 'openemm' ); ?> <span>(<?php _e( 'Default' ); ?>)</span></h2>

            <p>
		        <?php _e( 'Determine the fields (and their order) that the user can/must fill in for his newsletter subscription.', 'openemm' ); ?>
            </p><p>
		        <?php printf(
			        __( 'To display the subscription form insert the shortcode %s into the editor e.g. by using the %s.', 'openemm' ),
			        '<code>[openemm]</code>',
			        '<a href="' . admin_url( 'widgets.php' ) . '">' . __( 'Text Widget', 'openemm' ) . '</a>'
		        ); ?>
		        <?php if ( false ) _e( '<i>All</i> the following values can be overridden wherever you insert the shortcode. Even the mailing <code>list=""</code>.', 'openemm' ); ?>
            </p>

            <?php if ( false ): ?>
            <p class="description">
		        <?php printf(
		                __( 'Possible values: %s', 'openemm' ),
                        '<code>0</code> = ' . __( 'disabled', 'openemm' ) . ', <code>1</code> = ' . __( 'optional', 'openemm' ) . ', <code>2</code> = ' . __( 'required', 'openemm' )
                ); ?>
            </p>
            <?php endif; ?>

            <table id="form-settings" class="form-table">
                <tbody>

		        <?php foreach ( $settings['form'] as $field => $option ): ?>

                    <tr valign="top">
                        <th scope="row">
                            <span class="dashicons dashicons-move"></span>
					        <?php openemm_get_label( $field ); ?>
                        </th>
                        <td>
                            <select name="openemm[form][<?php echo $field; ?>]"<?php echo $field == 'email' ? ' class="disabled" readonly="readonly"' : ''; ?>>
						        <?php foreach ( array( 'disabled', 'optional', 'required' ) as $value => $label ): ?>
                                    <option value="<?php echo $value; ?>"<?php selected( $value, $option ) ?>>
								        <?php _e( $label, 'openemm' ); ?>
                                    </option>
						        <?php endforeach; ?>
                            </select>
                        </td>
                        <?php if ( false ): ?>
                        <td>
					        <?php if ( $field != 'email' ): ?>
                                <code><?php echo $field ?>=""</code>
					        <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
		        <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <div id="openemm-messages-settings">
            <h2 class="title hide-if-js"><?php _e( 'Messages', 'openemm' ); ?></h2>

            <table class="form-table">
                <tbody>

                <?php foreach ( $settings['messages'] as $action => $message ): ?>
                    <tr id="openemm-messages-<?php echo $action ?>" valign="top">
                        <th scope="row"><label><?php _e( implode( ' ', array_map( 'ucfirst', explode( '_', $action ) ) ), 'openemm' ); ?></label></th>
                        <td>
                            <input type="text" class="large-text" name="openemm[messages][<?php echo $action; ?>]"
                                   value="<?php echo $settings['messages'][$action ]; ?>"
                                   placeholder="<?php echo openemm_get_message( $action, true ); ?>" />
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <div id="openemm-email-settings">
            <h2 class="title hide-if-js"><?php _e( 'Double Opt In Email', 'openemm' ); ?></h2>

            <table class="form-table">
                <tbody>

                    <tr valign="top">
                        <th scope="row"><label><?php _e( 'Sender', 'openemm' ); ?></label></th>
                        <td>
                            <input type="text" class="large-text" name="openemm[email][sender]"
                                   value="<?php echo $settings['email']['sender']; ?>"
                                   placeholder="<?php echo get_bloginfo( 'title' ) . '<' . get_option( 'admin_email' ) . '>'; ?>" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php _e( 'Subject', 'openemm' ); ?></label></th>
                        <td>
                            <input type="text" class="large-text" name="openemm[email][subject]"
                                   value="<?php echo $settings['email']['subject']; ?>"
                                   placeholder="<?php printf( __( 'Confirm your newsletter subscription from %s', 'openemm' ), get_option( 'home' ) ); ?>" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label><?php _e( 'Body', 'openemm' ); ?></label></th>
                        <td>
                            <textarea class="large-text" rows="10" name="openemm[email][body]" placeholder="[openemm_confirmation_link]"><?php
                                echo $settings['email']['body'];
                            ?></textarea>
                            <p class="description">
                                <?php printf( __( 'Use %s to add the (surprise, surprise ;) confirmation link. Otherwise its added at the end of the email.', 'openemm' ), '<code>[openemm_confirmation_link]</code>' ); ?>
                            </p>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <?php submit_button(); ?>

    </form>
</div>

        <?php }
    );
} );
