<?php
/**
 * user-menu.php
 *
 * @package:
 * @since  : 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$user_id = get_current_user_id();
$avatar  = jlt_is_logged_in() ? jlt_get_avatar( $user_id, 30 ) : '';
?>

<li id="menu-item-jlt-user-menu" class="menu-item menu-item-has-children menu-item-jlt-user-menu">
	<a class="jlt-member-menu-avatar"
	   href="<?php echo jlt_member_page_url(); ?>"><?php echo $avatar; ?><?php _e( 'Member', 'job-listings' ); ?></a>
	<ul class="sub-menu">
		<?php if ( ! jlt_is_logged_in() ): ?>
			<?php
			/**
			 * Menu for not login member.
			 */
			?>
			<li id="menu-item-jlt-user-login" class="menu-item menu-item-jlt-user-login">
				<a href="<?php echo jlt_member_login_url(); ?>"><?php _e( 'Login', 'job-listings' ); ?></a>
			</li>
			<?php if ( 'none' != jlt_check_allow_register() ): ?>
				<li id="menu-item-jlt-user-register" class="menu-item menu-item-jlt-user-register">
					<a href="<?php echo jlt_member_register_url(); ?>"><?php _e( 'Register', 'job-listings' ); ?></a>
				</li>
			<?php endif; ?>
			<?php do_action( 'jlt_menu_member_not_logged' ); ?>

		<?php else: ?>
			<?php
			/**
			 * Menu for member logged.
			 */
			?>
			<?php if ( jlt_is_employer() ): ?>
				<?php foreach ( jlt_get_employer_menu() as $endpoint ) : ?>

					<li id="menu-item-jlt-employer" class="menu-item">
						<a href="<?php echo esc_url( jlt_get_member_endpoint_url( $endpoint[ 'url' ] ) ); ?>"><?php echo esc_html( $endpoint[ 'text' ] ); ?></a>
					</li>

				<?php endforeach; ?>

				<?php do_action( 'jlt_menu_member_employer' ); ?>

			<?php else: ?>
				<?php foreach ( jlt_get_candidate_menu() as $endpoint ) : ?>

					<li id="menu-item-jlt-candidate" class="menu-item">
						<a href="<?php echo esc_url( jlt_get_member_endpoint_url( $endpoint[ 'url' ] ) ); ?>"><?php echo esc_html( $endpoint[ 'text' ] ); ?></a>
					</li>

				<?php endforeach; ?>

				<?php do_action( 'jlt_menu_member_candidate' ); ?>

			<?php endif; ?>
			<?php
			/**
			 * Logout link
			 */
			?>
			<li id="menu-item-jlt-logout" class="menu-item">
				<a href="<?php echo jlt_member_logout_url(); ?>"><?php _e( 'Lougout', 'job-listings' ); ?></a>
			</li>
		<?php endif; ?>
	</ul>

</li>