<?php
/**
 * Manage Job Page.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/manage-job.php.
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
$package_data        = jlt_get_job_posting_info();
$remain_featured_job = jlt_get_feature_job_remain();

$bulk_actions = jlt_job_bulk_actions();

do_action( 'jlt_member_manage_job_before' );

?>
	<div class="member-manage">

		<ul class="member-manage-notices">
			<li>
				<?php if ( $list_jobs->found_posts ) : ?>
					<?php echo sprintf( _n( "We found %s job", "We found %s jobs", $list_jobs->found_posts, 'job-listings' ), $list_jobs->found_posts ); ?>
				<?php else : ?>
					<?php _e( "No jobs found", 'job-listings' ) ?>
				<?php endif; ?>
			</li>
			<li>
				<?php _e( 'Expired listings will be removed from public view.', 'job-listings' ) ?>
			</li>
			<?php if ( $remain_featured_job > 0 ) : ?>
				<li>
					<?php echo sprintf( _n( 'You can set %d more job to be featured. Featured jobs cannot be reverted.', 'You can set %d more jobs to be featured. Featured jobs cannot be reverted.', $remain_featured_job, 'job-listings' ), $remain_featured_job ); ?>
				</li>
			<?php endif; ?>

		</ul>
		<form method="get">
			<div class="jlt-toolbar jlt-toolbar-filter">
				<ul>
					<li>
						<strong><?php _e( 'Action:', 'job-listings' ) ?></strong>
						<select class="jlt-form-control" name="action">
							<option selected="selected" value=""><?php _e( '-Bulk Actions-', 'job-listings' ) ?></option>
							<?php foreach ( $bulk_actions as $action => $label ): ?>
								<option
									value="<?php echo esc_attr( $action ) ?>"><?php echo esc_html( $label ) ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="jlt-btn"><?php _e( 'Go', 'job-listings' ) ?></button>
					</li>
					<li>
						<strong><?php _e( 'Filter:', 'job-listings' ) ?></strong>
						<select class="jlt-form-control" name="status">
							<option value=""><?php _e( 'All Status', 'job-listings' ) ?></option>
							<?php foreach ( $list_status as $key => $status ): ?>
								<option
									value="<?php echo esc_attr( $key ) ?>" <?php selected( $current_status, $key ) ?> ><?php echo $status; ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="jlt-btn"><?php _e( 'Go', 'job-listings' ) ?></button>
					</li>
				</ul>
			</div>
			<div class="member-manage-table">
				<ul class="jlt-list jlt-list-jobs">
					<li>
						<div class="col-check jlt-col-5"><input class="jlt-checkbox" type="checkbox" id="select_all"/>
						</div>
						<div class="col-job-title jlt-col-40"><strong><?php _e( 'Title', 'job-listings' ) ?></strong></div>
						<div class="col-job-featured jlt-col-15"><strong><?php _e( 'Featured', 'job-listings' ) ?></strong></div>
						<div class="col-apps jlt-col-10"><strong><?php _e( 'Apps', 'job-listings' ) ?></strong></div>
						<div class="col-status jlt-col-10"><strong><?php _e( 'Status', 'job-listings' ) ?></strong></div>
						<div class="col-actions jlt-col-20"><strong><?php _e( 'Action', 'job-listings' ) ?></strong></div>
					</li>
					<?php if ( $list_jobs->have_posts() ) : ?>

						<?php while ( $list_jobs->have_posts() ): $list_jobs->the_post();

							global $post, $job;
							$status = jlt_job_status( $post );

							?>

							<li class="job-<?php the_ID() ?>">
								<div class="col-check jlt-col-5">
									<input type="checkbox" class="jlt-checkbox" name="ids[]" value="<?php the_ID() ?>">
								</div>
								<div class="col-job-title jlt-col-40">
									<?php if ( 'publish' == $status[ 'class' ] ) : ?>

										<a href="<?php the_permalink() ?>">
											<strong><?php the_title() ?></strong>
										</a>

										<?php $notify_email = get_post_meta( get_the_ID(), '_application_email', true );

										if ( ! empty( $notify_email ) && $notify_email != $current_user->user_email ) : ?>
											<div><?php echo sprintf( __( 'Notify email: %s', 'job-listings' ), $notify_email ); ?></div>

										<?php endif; ?>

									<?php else : ?>

										<a href="<?php echo jlt_job_edit_url(); ?>"><strong><?php the_title() ?></strong></a>

									<?php endif; ?>

									<?php
									if ( ! empty( $job->closing() ) ) :
										?>
										<span>
											<i class="jlt-icon jltfa-calendar"></i>&nbsp;<em><?php echo $job->closing(); ?></em>
										</span>

									<?php else : ?>

										<span>
											<i class="jlt-icon jltfa-calendar"></i>&nbsp;<?php echo __( 'Equal to expired date', 'job-listings' ); ?>
										</span>

									<?php endif; ?>
								</div>
								<div class="col-job-featured jlt-col-15">
									<?php

									if ( 'yes' === $job->featured() ) :
										echo '<span class="jlt-job-feature"><i class="jlt-icon jltfa-star"></i></span>';
									elseif ( jlt_can_set_feature_job() ) :
										?>
										<a href="<?php echo jlt_job_featured_url(); ?>">
											<i class="jlt-icon jltfa-star-o"></i>
										</a>
									<?php else : ?>
										<i class="jlt-icon jltfa-star-o"></i>
									<?php endif; ?>
								</div>

								<div class="col-apps jlt-col-10">
									<span> <?php echo $job->applications_count(); ?></span>
								</div>

								<div class="col-status jlt-col-10">
									<span class="jlt-status jlt-status-<?php echo esc_attr( $status[ 'class' ] ); ?>">
										<?php echo esc_html( $status[ 'text' ] ) ?>
									</span>
								</div>
								<div class="col-actions jlt-col-20">

									<?php if ( jlt_job_can_edit_status() ): ?>

										<?php

										if ( $status[ 'class' ] == 'publish' ): ?>

											<a href="<?php echo jlt_job_unpublish_url(); ?>" class="jlt-btn-link"
											   title="<?php esc_attr_e( 'Unpublish Job', 'job-listings' ) ?>">
												<i class="jlt-icon jltfa-toggle-on"></i>
											</a>

										<?php else: ?>
											<a href="<?php echo jlt_job_publish_url(); ?>" class="jlt-btn-link"
											   title="<?php esc_attr_e( 'Publish Job', 'job-listings' ) ?>">
												<i class="jlt-icon jltfa-toggle-off"></i>
											</a>
										<?php endif; ?>
									<?php endif; ?>

									<?php if ( jlt_job_can_edit() ): ?>

										<a href="<?php echo jlt_job_edit_url(); ?>" class="jlt-btn-link"
										   title="<?php esc_attr_e( 'Edit Job', 'job-listings' ) ?>">
											<i class="jlt-icon jltfa-pencil"></i>
										</a>

									<?php endif; ?>

									<?php if ( $status[ 'class' ] == 'expired' ) : ?>
										<a href="javascript:void(0)" class="jlt-btn-link action-no-link"
										   title="<?php esc_attr_e( 'Expired Job', 'job-listings' ) ?>">
											<i class="jlt-icon jltfa-clock-o"></i>
										</a>
									<?php endif; ?>

									<a href="<?php echo jlt_job_delete_url(); ?>" class="jlt-btn-link"
									   title="<?php esc_attr_e( 'Delete Job', 'job-listings' ) ?>">
										<i class="jlt-icon jltfa-trash-o"></i>
									</a>

								</div>
							</li>

						<?php endwhile; ?>

					<?php else: ?>

						<li>
							<div class="jlt-not-found"><?php _e( 'No job found.', 'job-listings' ) ?></div>
						</li>

					<?php endif; ?>
					</tbody>
				</ul>
			</div>

			<?php jlt_member_pagination( $list_jobs ) ?>

			<?php jlt_form_nonce( 'job-manage-action' ) ?>
		</form>
	</div>
<?php
do_action( 'jlt_member_manage_job_after' );
wp_reset_query();