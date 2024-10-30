<?php
/**
 * Display register form
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/form/register.php.
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
<form class="jlt-form register-form" action="" method="post" id="registerform">

	<?php do_action( 'jlt_register_form_before' ); ?>

	<?php
	$fields = array(
		'user_login'       => array(
			'name'     => 'user_login',
			'label'    => __( 'Username', 'job-listings' ),
			'type'     => 'text',
			'value'    => isset( $_REQUEST[ 'user_login' ] ) ? wp_unslash( $_REQUEST[ 'user_login' ] ) : '',
			'required' => true,
		),
		'user_email'       => array(
			'name'     => 'user_email',
			'label'    => __( 'Email', 'job-listings' ),
			'type'     => 'email',
			'value'    => isset( $_REQUEST[ 'user_email' ] ) ? wp_unslash( $_REQUEST[ 'user_email' ] ) : '',
			'required' => true,
		),
		'user_password'    => array(
			'name'     => 'user_password',
			'label'    => __( 'Password', 'job-listings' ),
			'type'     => 'password',
			'value'    => '',
			'required' => true,
		),
		'user_password_re' => array(
			'name'     => 'cuser_password',
			'label'    => __( 'Repeat password', 'job-listings' ),
			'type'     => 'password',
			'value'    => '',
			'required' => true,
		),
	);

	if ( 'both' == jlt_check_allow_register() ) :
		$fields[ 'user_role' ] = array(
			'name'     => 'user_role',
			'label'    => __( 'User role', 'job-listings' ),
			'type'     => 'select',
			'value'    => array(
				'employer'  => 'Employer',
				'candidate' => 'Candidate',
			),
			'required' => true,
		);

	endif;

	$fields = apply_filters( 'jlt_register_fields', $fields );

	foreach ( $fields as $field ) {
		jlt_render_form_field( $field );
	}

	?>
	<?php do_action( 'jlt_register_form_field' ); ?>

	<fieldset class="fieldset">
		<div class="field">

			<button type="submit" class="jlt-btn"><?php echo esc_html__( 'Sign Up', 'job-listings' ); ?></button>

			<?php if ( ! empty( jlt_term_of_use_link() ) ) : ?>
				<div class="checkbox account-reg-term">
					<?php _e( 'By signing up, you agree to our', 'job-listings' ) ?> <a
						href="<?php echo esc_url(jlt_term_of_use_link()); ?>"
						target="_blank"><?php _e( 'Terms of use', 'job-listings' ) ?></a>
				</div>
			<?php endif; ?>

			<div class="login-form-links">
				<span><?php echo sprintf( __( 'Already have an account? <a href="%s" class="member-login-link" >Login Now</a>', 'job-listings' ), jlt_member_login_url() ) ?></span>
			</div>

		</div>
	</fieldset>

	<input type="hidden" class="redirect_to" name="redirect_to" value="<?php echo esc_url( apply_filters( 'jlt_register_redirect_url', $redirect_to ) ); ?>"/>
	<input type="hidden" name="action" value="jlt-register">
	<?php jlt_form_nonce( 'jlt-register' ) ?>

	<?php do_action( 'jlt_register_form_after' ); ?>

</form>