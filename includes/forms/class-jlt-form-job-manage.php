<?php

/**
 * Project: job-listings - class-jlt-form-job-manage.php
 * Author: Edgar
 * Website: nootheme.com
 */
class JLT_Form_Job_Manage {
	public function __construct() {
		add_action( 'init', array( $this, 'manage_job_action' ), 20 );
	}

	public static function current_action() {
		if ( isset( $_REQUEST[ 'action' ] ) && - 1 != $_REQUEST[ 'action' ] ) {
			return $_REQUEST[ 'action' ];
		}

		if ( isset( $_REQUEST[ 'action2' ] ) && - 1 != $_REQUEST[ 'action2' ] ) {
			return $_REQUEST[ 'action2' ];
		}
	}

	public static function manage_job_action() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$employer_id = get_current_user_id();
		$action      = self::current_action();
		if ( ! empty( $action ) && ! empty( $_REQUEST[ '_wpnonce' ] ) && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'job-manage-action' ) ) {
			if ( isset( $_REQUEST[ 'job_id' ] ) ) {
				$ids = explode( ',', $_REQUEST[ 'job_id' ] );
			} elseif ( ! empty( $_REQUEST[ 'ids' ] ) ) {
				$ids = array_map( 'intval', $_REQUEST[ 'ids' ] );
			}
			try {
				switch ( $action ) {
					case 'publish':
						$published = 0;
						foreach ( (array) $ids as $job_id ) {
							$job = get_post( $job_id );
							if ( $job->post_type !== 'job' ) {
								return;
							}
							if ( ! JLT_Member::can_change_job_state( $job_id, $employer_id ) ) {
								continue;
							}
							if ( $job->post_author != $employer_id && ! current_user_can( 'edit_post', $job_id ) ) {
								wp_die( __( 'You do not have sufficient permissions to access this page.', 'job-listings' ), '', array( 'response' => 403 ) );
							}
							$update_job = wp_update_post( array(
								'ID'          => $job_id,
								'post_status' => 'publish',
							) );
							if ( ! $update_job ) {
								wp_die( __( 'There was an error publishing this job.', 'job-listings' ) );
							}
							jlt_set_job_expired( $job_id );
							$published ++;
						}
						if ( $published > 0 ) {
							jlt_message_add( sprintf( _n( 'Published %s job', 'Published %s jobs', $published, 'job-listings' ), $published ) );
						} else {
							jlt_message_add( __( 'No job published', 'job-listings' ) );
						}
						do_action( 'manage_job_action_publish', $ids );
						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
						die;
						break;
					case 'unpublish':
						$unpublished = 0;
						foreach ( (array) $ids as $job_id ) {
							$job = get_post( $job_id );
							if ( $job->post_type !== 'job' ) {
								return;
							}
							if ( ! JLT_Member::can_change_job_state( $job_id, $employer_id ) ) {
								continue;
							}
							if ( $job->post_author != $employer_id && ! current_user_can( 'edit_post', $job_id ) ) {
								wp_die( __( 'You do not have sufficient permissions to access this page.', 'job-listings' ), '', array( 'response' => 403 ) );
							}
							if ( ! wp_update_post( array(
								'ID'          => $job_id,
								'post_status' => 'inactive',
							) )
							) {
								wp_die( __( 'There was an error unpublishing this job.', 'job-listings' ) );
							}
							$unpublished ++;
						}
						if ( $unpublished > 0 ) {
							jlt_message_add( sprintf( _n( 'Unpublished %s job', 'Unpublished %s jobs', $unpublished, 'job-listings' ), $unpublished ) );
						} else {
							jlt_message_add( __( 'No job unpublished', 'job-listings' ) );
						}
						do_action( 'manage_job_action_pending', $ids );
						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
						exit;
						break;
					case 'featured':

						if ( ! jlt_can_set_feature_job() ) {
							jlt_message_add( __( 'You do not have sufficient permissions set job to featured! Please check your plan package!', 'job-listings' ), 'error' );
							wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
							exit;
						}
						$job_id = reset( $ids );
						$job    = get_post( $job_id );

						if ( get_post_status( $job_id ) == 'expired' ) {

							jlt_message_add( __( 'You cannot change expired jobs to featured ones.', 'job-listings' ), 'notice' );
							wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
							exit;
						}

						if ( ! JLT_Member::can_edit_job( $job_id, $employer_id ) ) {
							return;
						}

						if ( $job->post_author != $employer_id && ! current_user_can( 'edit_post', $job_id ) ) {
							wp_die( __( 'You do not have sufficient permissions to access this page.', 'job-listings' ), '', array( 'response' => 403 ) );
						}

						$featured = jlt_get_post_meta( $job_id, '_featured' );

						if ( 'yes' !== $featured ) {
							update_post_meta( $job_id, '_featured', 'yes' );
							update_user_meta( $job->post_author, '_job_featured', absint( get_user_meta( $job->post_author, '_job_featured', true ) ) + 1 );
							jlt_message_add( __( 'Job set to featured successfully.', 'job-listings' ) );
						}

						do_action( 'manage_job_action_featured', $job_id );
						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
						exit;
						break;
					case 'delete':
						$deleted = 0;
						foreach ( (array) $ids as $job_id ) {
							$job = get_post( $job_id );
							if ( $job->post_type !== 'job' ) {
								return;
							}
							if ( $job->post_author != $employer_id && ! current_user_can( 'delete_post', $job_id ) ) {
								wp_die( __( 'You do not have sufficient permissions to access this page.', 'job-listings' ), '', array( 'response' => 403 ) );
							}

							$old_status = get_post_status( $job_id );
							$in_review  = (bool) jlt_get_post_meta( $job_id, '_in_review', '' );

							if ( ! wp_delete_post( $job_id ) ) {
								wp_die( __( 'Error in deleting.', 'job-listings' ) );
							}

							// Correct the job count.
							
							if ( ( 'pending' == $old_status && $in_review ) || 'pending_payment' == $old_status ) {
								jlt_decrease_job_posting_count( $employer_id );
								$featured = jlt_get_post_meta( $job_id, '_featured' );
								if ( $featured == 'yes' ) {
									$job_featured = jlt_get_feature_job_added( $employer_id );
									update_user_meta( $employer_id, '_job_featured', max( $job_featured - 1, 0 ) );
								}
							}

							$deleted ++;
						}
						if ( $deleted > 0 ) {
							jlt_message_add( sprintf( _n( 'Deleted %s job', 'Deleted %s jobs', $deleted, 'job-listings' ), $deleted ) );
						} else {
							jlt_message_add( __( 'No job deleted', 'job-listings' ) );
						}
						do_action( 'manage_job_action_delete', $ids );
						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
						exit;
						break;
				}
			} catch ( Exception $e ) {
				throw new Exception( $e->getMessage() );
			}
		}
	}
}

new JLT_Form_Job_Manage();