<?php

/**
 * @param $settings
 * @return false|string
 */
function openemm_form( $settings ) {
	$hash = wp_hash( maybe_serialize( $settings ) );

	// form is submitted
	if ( $isSubmitted = isset($_POST) && isset($_POST['hash']) && $_POST['hash'] == $hash ) {
		// validate form
		$errors = $data = array();
		foreach ( array_filter( $settings['form'] ) as $field => $status ) {
			$_POST[$field] = trim($_POST[$field]);
			$data[$field] = $_POST[$field];

			if ( $status == 2 && !$_POST[$field] ) {
				$errors[$field] = sprintf( __( '%s is required.', 'openemm' ), __( 'This value', 'openemm' ) );
			}

			if ( $field == 'email' ) {
			    unset($data[$field]);

				if ( !isset($errors[$field]) && !is_email( $_POST[$field] ) ) {
					$errors[ $field ] = __( 'A valid email address is required.' );
				}
			}
		}

		// no errors aka form is valid ... do register/subscribe
		if ( $isValid = !$errors ) {
		    $message = openemm_add_subscriber( $_POST['email'], array_filter($data) );
        }
	}

	global $wp;

	ob_start(); ?>

<form class="openemm-form" method="post" action="<?php echo add_query_arg( array( 'destination' => $wp->request ), home_url( 'openemm/subscribe' ) ) ?>">
	<?php foreach ( array_filter( $settings['form'] ) as $field => $status ) {
		$options = array(
		    'required' => $status > 1,
            'error' => empty($errors[$field]) ? '' : $errors[$field],
            'value' => !empty($errors) ? $_POST[$field] : ''
        );

		switch ( $field ) {
			default:
				$type = 'text';
				break;

			case 'gender':
				$type = 'select';
				$options['label'] = __( 'Gender', 'openemm' );
				$options['options'] = array(
					__( 'Mr.', 'openemm' ),
					__( 'Mrs.', 'openemm' ),
					__( 'Unknown' )
				);
				break;

			case 'firstname':
				$type = 'text';
				$options['label'] = __( 'First Name', 'openemm' );
				break;

			case 'lastname':
				$type = 'text';
				$options['label'] = __( 'Last Name', 'openemm' );
				break;

			case 'mailtype':
				$type = 'select';
				$options['options'] = array(
					__( 'Text', 'openemm' ),
					__( 'HTML' ),
					__( 'Offline-HTML', 'openemm' )
				);
				break;
		}

		openemm_form_item( $field, $type, $options );
	} ?>

    <input type="hidden" name="form" value="<?php echo (new OpenEMMCrypt)->encrypt(maybe_serialize($settings['form'])); ?>" />
    <input type="hidden" name="hash" value="<?php echo $hash; ?>" />

    <button>
        <?php _e( 'Subscribe', 'openemm' ); ?>
        <span class="spinner"></span>
    </button>

    <?php if ( !empty($message) ): ?>
        <p class="message <?php echo $message['type']; ?>"><?php echo $message['message']; ?></p>
    <?php endif; ?>
</form>

<?php $form = ob_get_contents();
	ob_end_clean();

	return $form;
}

function openemm_form_item( $name, $type, $options = array() ) { echo openemm_get_form_item(  $name, $type, $options ); }
function openemm_get_form_item( $name, $type, $options = array() ) {
	if ( function_exists( $theme = "openemm_{$type}" ) ) {
		$options += array( 'required' => false, 'label' => openemm_get_label( $name, false ) );

		ob_start(); ?>

		<div class="form-item form-item form-item-<?php echo $type; ?> form-item__<?php echo $name; ?>
            <?php echo $options['required'] ? 'required' : ''; ?>
            <?php echo $options['error'] ? 'error' : ''; ?>">
			<label><?php echo $options['label']; ?></label>
			<?php $theme( $name, $options ); ?>
            <?php if ( $options['error'] ): ?>
                <p class="note error"><?php echo $options['error']; ?></p>
            <?php endif;?>
		</div>

	<?php $item = ob_get_contents();
		ob_end_clean();
	}

	return isset($item) ? $item : '';
}

function openemm_text( $name, $options = array() ) { echo openemm_get_text( $name, $options ); }
function openemm_get_text( $name, $options = array() ) {
	return '<input type="text" name="' . $name . '" title="' . $options['label'] . '" value="' . $options['value'] . '" />';
}

function openemm_select( $name, $options = array() ) { echo openemm_get_select( $name, $options ); }
function openemm_get_select( $name, $options = array() ) {
	$options += array( 'options' => array() );

	$select = '<select name="' . $name . '" title="' . $options['label'] . '">';
	foreach ( $options['options'] as $value => $label ) {
		$select .= '<option value="' . $value . '">' . $label . '</option>';
	}
	$select .= '</select>';
	return $select;
}
