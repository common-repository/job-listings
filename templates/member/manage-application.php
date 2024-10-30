<?php
/**
 * Manage Application Page.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/manage-application.php.
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

$job_filter = isset( $_REQUEST[ 'job_id' ] ) ? absint( $_REQUEST[ 'job_id' ] ) : 0;

$jobs_list = get_posts( array(
	'post_type'        => 'job',
	'post_status'      => array( 'publish', 'pending', 'expired' ),
	'author'           => get_current_user_id(),
	'posts_per_page'   => - 1,
	'suppress_filters' => false,
) );

$job_ids = array_map( create_function( '$o', 'return $o->ID;' ), $jobs_list );

$status_filter = isset( $_REQUEST[ 'status' ] ) ? esc_attr( $_REQUEST[ 'status' ] ) : '';
$all_statuses  = JLT_Application::get_application_status();
unset( $all_statuses[ 'inactive' ] );

$paged = jlt_member_get_paged();

$args = array(
	'post_type'       => 'application',
	'paged'           => $paged,
	'post_parent__in' => array_merge( $job_ids, array( 0 ) ), // make sure return zero application if there's no job.
	'post_status'     => ! empty( $status_filter ) ? array( $status_filter ) : array(
		'publish',
		'pending',
		'rejected',
	),
);
if ( ! empty( $job_filter ) && in_array( $job_filter, $job_ids ) ) {
	$args[ 'post_parent__in' ] = array( $job_filter );
}
$applications            = new WP_Query( $args );
$bulk_actions = (array) apply_filters( 'jlt_application_bulk_actions', array(
	'approve' => __( 'Approve', 'job-listings' ),
	'reject'  => __( 'Reject', 'job-listings' ),
	'delete'  => __( 'Delete', 'job-listings' ),
) );

do_action( 'jlt_member_manage_application_before' );

?>
	<div class="member-manage">
		<ul class="member-manage-notices">
			<li>
				<?php if ( $applications->found_posts > 0 ) : ?>
					<?php echo sprintf( _n( "You've received %s application", "You've received %s applications", $applications->found_posts, 'job-listings' ), $applications->found_posts ); ?>
				<?php else : ?>
					<?php _e( "You've received no application", 'job-listings' ); ?>
				<?php endif; ?>
			</li>

		</ul>
		<form method="get">
			<div class="jlt-toolbar jlt-toolbar-filter">
				<ul>
					<li>
						<span><?php _e( 'Action:', 'job-listings' ) ?></span>
						<select class="jlt-form-control" name="action">
							<option selected="selected" value="-1"><?php _e( 'Bulk Actions', 'job-listings' ) ?></option>
							<?php foreach ( $bulk_actions as $action => $label ): ?>
								<option
									value="<?php echo esc_attr( $action ) ?>"><?php echo esc_html( $label ) ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="jlt-btn"><?php _e( 'Go', 'job-listings' ) ?></button>
					</li>
					<li>
						<span><?php _e( 'Filter:', 'job-listings' ) ?></span>
						<select class="jlt-form-control" name="job_id">
							<option value="0"><?php _e( 'All Jobs', 'job-listings' ) ?></option>
							<?php foreach ( $jobs_list as $a_job ): ?>
								<option
									value="<?php echo esc_attr( $a_job->ID ) ?>" <?php selected( $job_filter, $a_job->ID ) ?> ><?php echo $a_job->post_title ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="jlt-btn"><?php _e( 'Go', 'job-listings' ) ?></button>
					</li>
					<li>
						<span><?php _e( 'Filter:', 'job-listings' ) ?></span>
						<select class="jlt-form-control" name="status">
							<option value=""><?php _e( 'All Status', 'job-listings' ) ?></option>
							<?php foreach ( $all_statuses as $key => $status ): ?>
								<option
									value="<?php echo esc_attr( $key ) ?>" <?php selected( $status_filter, $key ) ?> ><?php echo $status; ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="jlt-btn"><?php _e( 'Go', 'job-listings' ) ?></button>
					</li>
				</ul>
			</div>
			<div class="member-manage-table">
				<ul class="jlt-list">
					<li>
						<div class="col-check jlt-col-5"><input class="jlt-checkbox" type="checkbox" id="select_all"/>
						</div>
						<div class="col-candidate jlt-col-30"><strong><?php _e( 'Candidate', 'job-listings' ) ?></strong></div>
						<div class="col-job jlt-col-30"><strong><?php _e( 'Applied job', 'job-listings' ) ?></strong></div>
						<div class="col-status jlt-col-15"><strong><?php _e( 'Status', 'job-listings' ) ?></strong></div>
						<div class="col-actions jlt-col-20"><strong><?php _e( 'Action', 'job-listings' ) ?></strong></div>
					</li>
					<?php if ( $applications->have_posts() ) : ?>

						<?php
						while ( $applications->have_posts() ): $applications->the_post();
							global $post;
							?>
							<li>
								<div class="col-check jlt-col-5">
									<input type="checkbox" class="jlt-checkbox" name="ids[]"
									       value="<?php echo get_the_ID() ?>">
								</div>
								<div class="col-candidate jlt-col-30">

									<div class="candidate-name">
										<?php echo get_the_title(); ?>
									</div>

									<a class="candidate-mail"
									   href="mailto:<?php echo job_apply_candidate_email( $post ); ?>">
										<i class="jlt-icon jltfa-envelope-o"></i> <?php _e( 'Send Email', 'job-listings' ); ?>
									</a>

									<a class="candidate-message" href="javascript:void(0)"
									   data-application-id="<?php echo get_the_ID(); ?>">
										<i class="jlt-icon jltfa-comment"></i> <?php _e( 'View Cover Letter', 'job-listings' ); ?>
									</a>
								</div>
								<div class="col-job jlt-col-30">

									<?php echo jlt_applied_job_title( $post ); ?>
									<p>
										<label><?php _e( 'Applied date:', 'job-listings' ); ?></label>
										<span><?php echo job_applied_date( $post ); ?></span>
									</p>
								</div>
								<div class="col-status jlt-col-15">

									<label
										class="jlt-status jlt-status-<?php echo jlt_application_status( $post ); ?>">
										<?php echo jlt_application_status_text( $post ); ?>
									</label>

									<?php

									$attachment           = jlt_correct_application_attachment( get_the_ID() );

									if ( ! empty( $attachment ) ) :
										if ( is_string( $attachment ) && strpos( $attachment, 'linkedin' ) ) : ?>

											<a class="application-attachment"
											   href="<?php echo esc_url( $attachment ); ?>" target="_blank">
												<i class="jlt-icon jltfa-linkedin"></i>
											</a>
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
								<div class="col-actions jlt-col-20">
									<?php if ( 'pending' == jlt_application_status( $post ) ) : ?>
										<a href="javascript:void(0)" class="jlt-btn-link approve-reject-action"
										   data-hander="approve" data-application-id="<?php echo esc_attr($post->ID); ?>"
										   title="<?php _e( 'Approve Application', 'job-listings' ); ?>">
											<i class="jlt-icon jltfa-check"></i>
										</a>
										<a href="javascript:void(0)" class="jlt-btn-link approve-reject-action"
										   data-hander="reject" data-application-id="<?php echo esc_attr($post->ID); ?>"
										   title="<?php _e( 'Reject Application', 'job-listings' ); ?>">
											<i class="jlt-icon jltfa-ban"></i>
										</a>
									<?php else: ?>

										<a href="javascript:void(0)" class="jlt-btn-link action-no-link"
										   title="<?php esc_attr_e( 'Approve Application', 'job-listings' ) ?>">
											<i class="jlt-icon jltfa-check"></i>
										</a>
										<a href="javascript:void(0)" class="jlt-btn-link action-no-link"
										   title="<?php esc_attr_e( 'Reject Application', 'job-listings' ) ?>">
											<i class="jlt-icon jltfa-ban"></i></a>
									<?php endif; ?>
									<?php do_action( 'jlt_manage_application_action', get_the_ID() ); ?>

									<a class="jlt-btn-link" href="<?php echo jlt_application_delete_url( $post ); ?>">
										<i class="jlt-icon jltfa-trash-o"></i>
									</a>

								</div>

							</li>
						<?php endwhile; ?>
					<?php else: ?>
						<li>
							<div class="jlt-not-found"><?php _e( 'No Applications', 'job-listings' ) ?></div>
						</li>
					<?php endif; ?>
				</ul>
			</div>

			<?php jlt_member_pagination( $applications ) ?>

			<?php jlt_form_nonce( 'application-manage-action' ) ?>
		</form>

	</div>
<?php
do_action( 'jlt_member_manage_application_after' );
wp_reset_query();