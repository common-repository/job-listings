<?php

function jlt_job_social_media() {
	if ( ! is_singular( 'job' ) ) {
		return;
	}

	// Facebook media
	if ( jlt_get_setting( 'jlt_job_social_facebook', true ) ) {
		$job_id       = get_the_ID();
		$thumbnail_id = jlt_get_post_meta( $job_id, '_cover_image', '' );

		if ( empty( $thumbnail_id ) ) {
			$company_id   = jlt_get_job_company( $job_id );
			$thumbnail_id = jlt_get_post_meta( $company_id, '_logo', '' );
		}
		$social_share_img = wp_get_attachment_url( $thumbnail_id, 'full' );
		if ( ! empty( $social_share_img ) ) :
			?>
			<meta property="og:url" content="<?php echo get_permalink( $job_id ); ?>"/>
			<meta property="og:image" content="<?php echo $social_share_img; ?>"/>
			<?php if ( is_ssl() ) : ?>
			<meta property="og:image:secure_url" content="<?php echo $social_share_img; ?>"/>
		<?php endif; ?>
		<?php endif;
	}
}

add_filter( 'wp_head', 'jlt_job_social_media' );

function jlt_related_jobs( $job_id ) {
	global $wp_query;

	$args = array(
		'post_type'      => 'job',
		'post_status'    => 'publish',
		'posts_per_page' => jlt_get_job_setting( 'job_related_number', 5 ),
		'post__not_in'   => array( $job_id ),
	);

	//  -- tax_query

	$job_categorys = get_the_terms( $job_id, 'job_category' );
	$job_types     = get_the_terms( $job_id, 'job_type' );
	$job_locations = get_the_terms( $job_id, 'job_location' );

	$args[ 'tax_query' ] = array( 'relation' => 'AND' );
	if ( $job_categorys ) {
		$term_job_category = array();
		foreach ( $job_categorys as $job_category ) {
			$term_job_category = array_merge( $term_job_category, (array) $job_category->slug );
		}
		$args[ 'tax_query' ][] = array(
			'taxonomy' => 'job_category',
			'field'    => 'slug',
			'terms'    => $term_job_category,
		);
	}

	if ( $job_types ) {
		$term_job_type = array();
		foreach ( $job_types as $job_type ) {
			$term_job_type = array_merge( $term_job_type, (array) $job_type->slug );
		}
		$args[ 'tax_query' ][] = array(
			'taxonomy' => 'job_type',
			'field'    => 'slug',
			'terms'    => $term_job_type,
		);
	}

	if ( $job_locations ) {
		$term_job_location = array();
		foreach ( $job_locations as $job_location ) {
			$term_job_location = array_merge( $term_job_location, (array) $job_location->slug );
		}
		$args[ 'tax_query' ][] = array(
			'taxonomy' => 'job_location',
			'field'    => 'slug',
			'terms'    => $term_job_location,
		);
	}

	return $args;
}

function jlt_job_edit_url() {
	$slug = jlt_get_endpoints_setting( 'edit-job', 'edit-job' );

	return esc_url_raw( add_query_arg( 'job_id', get_the_ID(), jlt_get_member_endpoint_url( $slug ) ) );
}

function jlt_job_delete_url() {
	return wp_nonce_url( add_query_arg( array(
		'action' => 'delete',
		'job_id' => get_the_ID(),
	) ), 'job-manage-action' );
}

function jlt_job_unpublish_url() {
	return wp_nonce_url( add_query_arg( array(
		'action' => 'unpublish',
		'job_id' => get_the_ID(),
	) ), 'job-manage-action' );
}

function jlt_job_publish_url() {
	return wp_nonce_url( add_query_arg( array(
		'action' => 'publish',
		'job_id' => get_the_ID(),
	) ), 'job-manage-action' );
}

function jlt_job_featured_url() {
	return wp_nonce_url( add_query_arg( array(
		'action' => 'featured',
		'job_id' => get_the_ID(),
	) ), 'job-manage-action' );
}

function jlt_job_can_edit_status( $job_id = '' ) {
	$job_id = ! empty( $job_id ) ? $job_id : get_the_ID();

	return JLT_Member::can_change_job_state( $job_id, get_current_user_id() );
}

function jlt_job_can_edit( $job_id = '' ) {
	$job_id = ! empty( $job_id ) ? $job_id : get_the_ID();

	return JLT_Member::can_edit_job( $job_id, get_current_user_id() );
}