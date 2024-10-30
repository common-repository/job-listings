<?php

function jlt_is_enabled_candidate_package_apply_job() {
	return 'package' == jlt_get_action_control( 'apply_job' );
}

function jlt_candidate_package_apply_job_data() {
	global $post;
	if ( jlt_is_enabled_candidate_package_apply_job() ) {
		woocommerce_wp_checkbox( array(
			'id'          => '_can_apply_job',
			'label'       => __( 'Can apply for Job', 'job-listings' ),
			'description' => __( 'Allow buyers to apply for jobs.', 'job-listings' ),
			'cbvalue'     => 1,
			'desc_tip'    => false,
		) );

		$disable_field = get_post_meta( $post->ID, '_can_apply_job', true ) === '1' ? '' : 'disabled';
		woocommerce_wp_text_input( array(
			'id'                => '_job_apply_limit',
			'label'             => __( 'Job apply limit', 'job-listings' ),
			'description'       => __( 'The maximum number of jobs this package allows candidates to apply, input -1 for unlimited.', 'job-listings' ),
			'placeholder'       => '',
			'type'              => 'number',
			'value'             => get_post_meta( $post->ID, '_job_apply_limit', true ),
			'desc_tip'          => true,
			'custom_attributes' => array( 'min' => '', 'step' => '1', $disable_field => $disable_field ),
		) );
		?>
		<script type="text/javascript">
			jQuery('.pricing').addClass('show_if_candidate_package');
			jQuery(document).ready(function ($) {
				$("#_can_apply_job").change(function () {
					if (this.checked) {
						$('#_job_apply_limit').prop('disabled', false);
					} else {
						$('#_job_apply_limit').prop('disabled', true);
					}
				});
			});
		</script>
		<?php
	}
}

add_action( 'jlt_candidate_package_data', 'jlt_candidate_package_apply_job_data' );

function jlt_candidate_package_save_apply_job_data( $post_id ) {
	if ( jlt_is_enabled_candidate_package_apply_job() ) {
		// Save meta
		$fields = array(
			'_can_apply_job'   => '',
			'_job_apply_limit' => 'int',
		);
		foreach ( $fields as $key => $value ) {
			$value = ! empty( $_POST[ $key ] ) ? $_POST[ $key ] : '';
			switch ( $value ) {
				case 'int' :
					$value = intval( $value );
					break;
				case 'float' :
					$value = floatval( $value );
					break;
				default :
					$value = sanitize_text_field( $value );
			}
			update_post_meta( $post_id, $key, $value );
		}
	}
}

add_action( 'jlt_candidate_package_save_data', 'jlt_candidate_package_save_apply_job_data' );

function jlt_candidate_package_apply_job_user_data( $data, $product ) {
	if ( jlt_is_enabled_candidate_package_apply_job() && is_object( $product ) ) {
		$data[ 'can_apply_job' ]   = $product->can_apply_job;
		$data[ 'job_apply_limit' ] = $product->job_apply_limit;
	}

	return $data;
}

add_filter( 'jlt_candidate_package_user_data', 'jlt_candidate_package_apply_job_user_data', 10, 2 );

function jlt_candidate_package_apply_job_order_completed( $product, $user_id ) {
	if ( jlt_is_enabled_candidate_package_apply_job() && $product->can_apply_job == '1' ) {
		update_user_meta( $user_id, '_job_apply_count', '0' );
		$package = get_user_meta( $user_id, '_candidate_package', true );
	}
}

add_action( 'jlt_candidate_package_order_completed', 'jlt_candidate_package_apply_job_order_completed', 10, 2 );

function jlt_candidate_package_features_apply_job( $product ) {
	if ( jlt_is_enabled_candidate_package_apply_job() && $product->can_apply_job == '1' ) :
		$job_apply_limit = $product->job_apply_limit;
		?>
		<?php if ( $product->job_apply_limit == - 1 ) : ?>
		<li class="jlt-li-icon"><i
				class="fa fa-check-circle"></i> <?php _e( 'Apply for unlimited Jobs', 'job-listings' ); ?></li>
	<?php elseif ( $job_apply_limit > 0 ) : ?>
		<li class="jlt-li-icon"><i
				class="fa fa-check-circle"></i> <?php echo sprintf( _n( 'Apply for %d job', 'Apply for %d jobs', $job_apply_limit, 'job-listings' ), $job_apply_limit ); ?>
		</li>
	<?php endif; ?>
	<?php endif;
}

add_action( 'jlt_candidate_package_features_list', 'jlt_candidate_package_features_apply_job' );

function jlt_manage_plan_features_apply_job( $package ) {
	if ( JLT_Member::is_candidate() && jlt_is_enabled_candidate_package_apply_job() ) :
		$job_apply_limit = isset( $package[ 'job_apply_limit' ] ) && ! empty( $package[ 'job_apply_limit' ] ) ? intval( $package[ 'job_apply_limit' ] ) : 0;
		$job_apply_remain = jlt_get_job_apply_remain();

		if ( isset( $package[ 'can_apply_job' ] ) && $package[ 'can_apply_job' ] == '1' ) : ?>
			<?php if ( $job_apply_limit ) : ?>
				<div class="col-xs-6"><strong><?php _e( 'Job Apply Limit', 'job-listings' ) ?></strong></div>
				<?php if ( $job_apply_limit == - 1 ) : ?>
					<div class="col-xs-6"><?php _e( 'Unlimited', 'job-listings' ); ?></div>
				<?php elseif ( $job_apply_limit > 0 ) : ?>
					<div
						class="col-xs-6"><?php echo sprintf( _n( '%d job', '%d jobs', $job_apply_limit, 'job-listings' ), $job_apply_limit ); ?>
						<?php if ( $job_apply_remain < $job_apply_limit ) {
							echo '&nbsp;' . sprintf( __( '( %d remain )', 'job-listings' ), $job_apply_remain );
						} ?></div>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif;
	endif;
}

add_action( 'jlt_manage_plan_features_list', 'jlt_manage_plan_features_apply_job' );

function jlt_get_job_apply_remain( $user_id = '' ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$package         = jlt_get_resume_posting_info( $user_id );
	$job_apply_limit = empty( $package ) || ! is_array( $package ) || ! isset( $package[ 'job_apply_limit' ] ) ? 0 : $package[ 'job_apply_limit' ];
	if ( $job_apply_limit == - 1 ) {
		return - 1;
	}

	$job_applied = jlt_get_job_applied_count( $user_id );

	return max( absint( $job_apply_limit ) - absint( $job_applied ), 0 );
}

function jlt_get_job_applied_count( $user_id = '' ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$job_applied = get_user_meta( $user_id, '_job_apply_count', true );

	return empty( $job_applied ) ? 0 : absint( $job_applied );
}

function jlt_candidate_package_apply_job_count( $application_id ) {
	$user_id = get_current_user_id();
	if ( ! empty( $user_id ) ) {
		$job_apply_count = jlt_get_job_applied_count( $user_id );
		update_user_meta( $user_id, '_job_apply_count', $job_apply_count + 1 );
	}
}

add_action( 'new_job_application', 'jlt_candidate_package_apply_job_count', 10, 2 );
