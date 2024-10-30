<?php
/**
 * Manage Job Applied Page.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/manage-job-applied.php.
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
do_action( 'jlt_member_manage_application_before' );
$title_text = $count_jobs ? sprintf( _n( "You've applied for %s job", "You've applied for %s jobs", $count_jobs, 'job-listings' ), $count_jobs ) : __( "You haven't applied for any job", 'job-listings' );
?>
	<div class="member-manage">
		<p><?php echo esc_html($title_text); ?></p>
		<form method="post">
			<div class="member-manage-table">
				<ul class="jlt-list jlt-job-applied">
					<li>
						<div class="jlt-col-35 col-job-title"><?php _e( 'Applied job', 'job-listings' ) ?></div>
						<div class="jlt-col-20"><?php _e( 'Employer\'s message', 'job-listings' ) ?></div>
						<div class="jlt-col-20 col-attachment"><?php _e( 'Attachment', 'job-listings' ) ?></div>
						<div class="jlt-col-10 col-status"><?php _e( 'Status', 'job-listings' ) ?></div>
						<div class="jlt-col-15 col-actions"><?php _e( 'Action', 'job-listings' ) ?></div>
					</li>
					<?php if ( $list_jobs->have_posts() ): ?>
						<?php
						while ( $list_jobs->have_posts() ): $list_jobs->the_post();
							global $post;
							$job = get_post( $post->post_parent );

							if ( empty( $job ) && $post->post_status != 'inactive' ) {
								$post->post_status = 'inactive';
								wp_update_post( array( 'ID' => $post->ID, 'post_status' => $post->post_status ) );
							}
							$company_id             = jlt_get_job_company( $job );
							$company_logo           = JLT_Company::get_company_logo( $company_id, 'medium' );
							$employer_message_title = jlt_get_post_meta( $post->ID, '_employer_message_title', '' );
							$employer_message_body  = jlt_get_post_meta( $post->ID, '_employer_message_body', '' );

							$status       = $post->post_status;
							$status_class = $status;
							$statuses     = JLT_Application::get_application_status();
							if ( isset( $statuses[ $status ] ) ) {
								$status = $statuses[ $status ];
							} else {
								$status       = __( 'Inactive', 'job-listings' );
								$status_class = 'inactive';
							}
							?>
							<li>
								<div class="jlt-col-35 col-job-title">
									<?php
									if ( $job && $job->post_type === 'job' ) :
										?>
										<div class="job-title">
											<a href="<?php echo get_permalink( $job->ID ); ?>"><?php echo esc_html( $job->post_title ); ?></a>
										</div>
										<?php
									else :
										echo( '<span class="na">&ndash;</span>' );
									endif;
									?>
									<i class="jlt-icon jltfa-calendar"></i><em> <?php _e( 'Applied Date: ', 'job-listings' ) ?>
										<span><?php echo date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) ?>
									</em>
									</span>
								</div>
								<div class="jlt-col-20">
									<?php if ( $post->post_status == 'rejected' || $post->post_status == 'publish' ) : ?>

										<a class="jlt-btn-link employer-message" href="javascript:void(0)"
										   data-application-id="<?php echo esc_attr($post->ID); ?>">
											<i class="jlt-icon jltfa-comment"></i>
											<?php _e( 'View message', 'job-listings' ); ?>
										</a>

									<?php endif; ?>
								</div>
								<div class="jlt-col-20">
									<?php
									$attachment = jlt_correct_application_attachment( $post->ID );

									if ( ! empty( $attachment ) ) :
										if ( is_string( $attachment ) && strpos( $attachment, 'linkedin' ) ) : ?>
											<a class="jlt-btn-link application-attachment"
											   data-application-id="<?php echo esc_attr($post->ID); ?>"
											   href="<?php echo esc_url( $attachment ); ?>" data-toggle="tooltip"
											   title="<?php echo esc_attr__( 'LinkedIn profile', 'job-listings' ); ?>"
											   target="_blank"></a>
										<?php else :
											$attachment = ! is_array( $attachment ) ? array( $attachment ) : $attachment;
											foreach ( $attachment as $atm ) :
												$atm = jlt_json_decode( $atm );
												$file     = $atm[ 0 ];
												$file_url = jlt_get_file_upload( $file );
												?>
												<?php $file_name = basename( $file ); ?>

												<a class="application-attachment"
												   href="<?php echo esc_url( $file_url ); ?>"
												   title="<?php echo esc_attr( $file_name ) ?>">
													<i class="jlt-icon jltfa-cloud-download"></i>
												</a>

											<?php endforeach;

										endif;
									endif;
									?>
									<?php do_action( 'jlt_manage_application_attachment', $post ); ?>
								</div>
								<div class="jlt-col-10 col-status">
									<span
										class="jlt-status jlt-status-<?php echo sanitize_html_class( $status_class ) ?>">
									<?php echo esc_html( $status ) ?>
									</span>
								</div>
								<div class="jlt-col-15 col-actions">

									<?php do_action( 'jlt-manage-job-applied-action', get_the_ID() ); ?>

									<?php if ( $post->post_status == 'pending' ) : ?>

										<a href="<?php echo jlt_apply_withdraw_url(); ?>" class="jlt-btn-link"
										   title="<?php esc_attr_e( 'Withdraw', 'job-listings' ) ?>">
											<i class="jlt-icon jltfa-history"></i>
										</a>

									<?php elseif ( $post->post_status == 'inactive' ) : ?>

										<a href="<?php echo jlt_apply_delete_url(); ?>" class="jlt-btn-link"
										   title="<?php esc_attr_e( 'Delete Application', 'job-listings' ) ?>">
											<i class="jlt-icon jltfa-trash-o"></i>
										</a>

									<?php endif; ?>

								</div>
							</li>
						<?php endwhile; ?>
					<?php else: ?>
						<li>
							<div class="jlt-not-found"><?php _e( 'No Applications.', 'job-listings' ) ?></div>
						</li>
					<?php endif; ?>
				</ul>
			</div>

			<?php jlt_member_pagination( $list_jobs ) ?>

			<?php jlt_form_nonce( 'application-manage-action' ) ?>
		</form>
	</div>
<?php
do_action( 'jlt_member_manage_application_after' );
wp_reset_query();