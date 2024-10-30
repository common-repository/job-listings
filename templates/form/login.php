<?php
/**
 * Display job login form
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/form/login.php.
 *
 * HOWEVER, on occasion NooTheme will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NooTheme
 * @version     0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<?php

$redirect_to = isset( $_GET[ 'redirect_to' ] ) && ! empty( $_GET[ 'redirect_to' ] ) ? $_GET[ 'redirect_to' ] : jlt_current_url();
$redirect_to = esc_url( apply_filters( 'jlt_login_redirect', add_query_arg( array( 'logged_in' => 1 ), $redirect_to ) ) ); // This parameter help resolve the issue with Cache plugins

$rememberme = ! empty( $_REQUEST[ 'rememberme' ] );

?>

<form method="post" class="jlt-form login-form" action="">

	<?php do_action( 'jlt_login_form_before' ); ?>

	<?php

	$fields = array(
		'log_field' => array(
			'name'     => 'log',
			'label'    => __( 'Username', 'job-listings' ),
			'type'     => 'text',
			'value'    => isset( $_REQUEST[ 'log' ] ) ? wp_unslash( $_REQUEST[ 'log' ] ) : '',
			'required' => true,
		),
		'pwd_field' => array(
			'name'     => 'pwd',
			'label'    => __( 'Password', 'job-listings' ),
			'type'     => 'password',
			'value'    => '',
			'required' => true,
		),
	);

	$fields = apply_filters( 'jlt_login_fields', $fields );

	foreach ( $fields as $field ) {
		jlt_render_form_field( $field );
	}

	?>

	<?php do_action( 'jlt_login_form_field' ); ?>


	<fieldset class="fieldset">

		<div class="field">
			<label class="checkbox">
				<input type="checkbox" class="jlt-checkbox" name="rememberme" <?php checked( $rememberme ); ?>
				       value="forever"> <?php _e( 'Remember Me', 'job-listings' ); ?>
			</label>
		</div>

		<div class="field">
			<button type="submit" class="jlt-btn"><?php echo __( 'Sign In', 'job-listings' ); ?></button>
		</div>

		<div class="field">
			<div class="login-form-links">
            <span>
	            <a href="<?php echo wp_lostpassword_url() ?>">
		            <?php _e( 'Forgot Password?', 'job-listings' ) ?>
	            </a>
            </span>
				<?php if ( jlt_member_can_register() ): ?>
					<span>
						<?php echo sprintf( __( 'Don\'t have an account yet? <a href="%s" class="member-register-link" >Register Now</a>', 'job-listings' ), jlt_member_register_url() ) ?>
					</span>
				<?php endif; ?>
			</div>
		</div>

	</fieldset>

	<?php do_action( 'jlt_login_form_after' ); ?>

	<input type="hidden" name="action" value="jlt_login">
	<input type="hidden" class="redirect_to" name="redirect_to"
	       value="<?php echo esc_url( urldecode( $redirect_to ) ); ?>"/>
	<?php jlt_form_nonce( 'jlt-login' ) ?>

</form>