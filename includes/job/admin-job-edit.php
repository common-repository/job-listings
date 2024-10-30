<?php
/**
 * Admin Job Edit
 */

function jlt_admin_job_updated_messages( $messages ) {
	global $post, $post_ID, $wp_post_types;

	$messages[ 'job' ] = array(
		0  => '',
		// Unused. Messages start at index 1.
		1  => sprintf( __( '%s updated. <a href="%s">View</a>', 'job-listings' ), $wp_post_types[ 'job' ]->labels->singular_name, esc_url( get_permalink( $post_ID ) ) ),
		2  => __( 'Custom field updated.', 'job-listings' ),
		3  => __( 'Custom field deleted.', 'job-listings' ),
		4  => sprintf( __( '%s updated.', 'job-listings' ), $wp_post_types[ 'job' ]->labels->singular_name ),
		5  => isset( $_GET[ 'revision' ] ) ? sprintf( __( '%s restored to revision from %s', 'job-listings' ), $wp_post_types[ 'job' ]->labels->singular_name, wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
		6  => sprintf( __( '%s published. <a href="%s">View</a>', 'job-listings' ), $wp_post_types[ 'job' ]->labels->singular_name, esc_url( get_permalink( $post_ID ) ) ),
		7  => sprintf( __( '%s saved.', 'job-listings' ), $wp_post_types[ 'job' ]->labels->singular_name ),
		8  => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview</a>', 'job-listings' ), $wp_post_types[ 'job' ]->labels->singular_name, esc_url( add_query_arg( 'job_id', $post_ID, JLT_Member::get_endpoint_url( 'preview-job' ) ) ) ),
		// 8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview</a>', 'job-listings' ), $wp_post_types['job']->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9  => sprintf( __( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview</a>', 'job-listings' ), $wp_post_types[ 'job' ]->labels->singular_name, date_i18n( __( 'M j, Y @ G:i', 'job-listings' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview</a>', 'job-listings' ), $wp_post_types[ 'job' ]->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
}

add_filter( 'post_updated_messages', 'jlt_admin_job_updated_messages' );

function jlt_admin_job_edit_title_placeholder( $text, $post ) {
	if ( $post->post_type == 'job' ) {
		return __( 'Job Title', 'job-listings' );
	}

	return $text;
}

add_filter( 'enter_title_here', 'jlt_admin_job_edit_title_placeholder', 10, 2 );

function jlt_extend_job_status() {
	global $post, $post_type;
	if ( $post_type === 'job' ) {
		$html = $selected_label = '';
		foreach ( (array) jlt_get_job_status() as $status => $label ) {
			$seleced = selected( $post->post_status, esc_attr( $status ), false );
			if ( $seleced ) {
				$selected_label = $label;
			}
			$html .= "<option " . $seleced . " value='" . esc_attr( $status ) . "'>" . $label . "</option>";
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				<?php if ( ! empty( $selected_label ) ) : ?>
				jQuery('#post-status-display').html('<?php echo esc_js( $selected_label ); ?>');
				<?php endif; ?>
				var select = jQuery('#post-status-select').find('select');
				jQuery(select).html("<?php echo( $html ); ?>");
			});
		</script>
		<?php
	}
}

foreach ( array( 'post', 'post-new' ) as $hook ) {
	add_action( "admin_footer-{$hook}.php", 'jlt_extend_job_status' );
}
function jlt_job_meta_boxes() {
	$helper = new JLT_Meta_Boxes_Helper( '', array( 'page' => 'job' ) );

	$meta_box = array(
		'id'       => "job_settings",
		'title'    => __( 'Job Settings', 'job-listings' ),
		'page'     => 'job',
		'context'  => 'normal',
		'priority' => 'high',
		'fields'   => array(
			array(
				'id'       => 'author',
				'label'    => __( 'Posted by ( Employer - Company )', 'job-listings' ),
				'type'     => 'author',
				'callback' => 'jlt_meta_box_field_job_author',
			),
			array(
				'id'       => '_company_id',
				'label'    => __( 'Company', 'job-listings' ),
				'type'     => 'company',
				'callback' => 'jlt_meta_box_field_company',
				'desc'     => __( 'Use this option when you want to assign job to company without creating an employer.', 'job-listings' ),
			),

			array(
				'id'    => '_expires',
				'label' => __( 'Job\'s Expiration Date', 'job-listings' ),
				'type'  => 'datepicker',
			),
		),
	);

	$helper->add_meta_box( $meta_box );

	$fields = jlt_get_job_custom_fields();

	$custom_apply_link = jlt_get_application_setting( 'custom_apply_link', '' );

	if ( empty( $custom_apply_link ) ) {
		unset( $fields[ '_custom_application_url' ] );
	}

	if ( $fields ) {
		foreach ( $fields as $field ) {
			if ( isset( $field[ 'is_tax' ] ) ) {
				continue;
			}

			$id = jlt_job_custom_fields_name( $field[ 'name' ], $field );

			$new_field = jlt_custom_field_to_meta_box( $field, $id );

			$meta_box[ 'fields' ][] = $new_field;
		}
	}

	$helper->add_meta_box( $meta_box );
}

add_action( 'add_meta_boxes', 'jlt_job_meta_boxes', 30 );

function jlt_meta_box_field_company( $post, $id, $type, $meta, $std, $field ) {
	$args = array(
		'post_type'        => 'company',
		'post_status'      => 'publish',
		'posts_per_page'   => - 1,
		'orderby'          => 'title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	);

	$companies          = get_posts( $args );
	$company_option_arr = array( array( 'value' => '', 'label' => '' ) );
	foreach ( $companies as $company ) {
		if ( ! empty( $company->post_title ) ) {
			$company_option_arr[] = array(
				'value' => $company->ID,
				'label' => $company->post_title,
			);
		}
	}

	$company_id = jlt_get_post_meta( $post->ID, '_company_id', '' );

	echo '<select id=' . $id . ' name="jlt_meta_boxes[' . $id . ']" class="jlt-admin-chosen' . ( is_rtl() ? ' chosen-rtl' : '' ) . '" data-placeholder="' . __( '- Select a Company - ', 'job-listings' ) . '">';
	echo '	<option value=""></option>';
	foreach ( $companies as $company ) {
		echo '<option value="' . $company->ID . '"';
		selected( $company_id, $company->ID, true );
		echo '>' . $company->post_title;
		echo '</option>';
	}
	echo '</select>';
}

function jlt_meta_box_field_job_author( $post, $id, $type, $meta, $std, $field ) {

	// $meta = !empty($meta) ? $meta : $std;
	$user_list  = jlt_get_members( JLT_Member::EMPLOYER_ROLE );
	$admin_list = jlt_get_members( 'administrator' );
	$user_list  = array_merge( $admin_list, $user_list );

	echo '<select name="post_author_override" id="post_author_override" class="jlt-admin-chosen' . ( is_rtl() ? ' chosen-rtl' : '' ) . '" data-placeholder="' . __( '- Select an Employer - ', 'job-listings' ) . '">';
	echo '	<option value=""></option>';
	foreach ( $user_list as $user ) {
		$company_id = jlt_get_employer_company( $user->ID );
		echo '<option value="' . $user->ID . '"';
		selected( $post->post_author, $user->ID, true );
		echo '>' . $user->display_name;
		if ( ! empty( $company_id ) ) {
			$company_name = get_the_title( $company_id );
			echo( ! empty( $company_name ) ? ' - ' . $company_name : '' );
		}
		echo '</option>';
	}
	echo '</select>';
}

function jlt_job_save_meta_box( $post_id ) {
	$meta_box = $_POST[ 'jlt_meta_boxes' ];
	if ( ( ! isset( $meta_box[ '_company_id' ] ) || empty( $meta_box[ '_company_id' ] ) ) && ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
		// don't auto save company when there's WPML to prevent error with post duplicate function

		$employer_id = get_post_field( 'post_author', $post_id );
		$company_id  = jlt_get_employer_company( $employer_id );

		if ( $company_id ) {
			update_post_meta( $post_id, '_company_id', $company_id );
		}
	}
}

add_action( 'jlt_save_meta_box', 'jlt_job_save_meta_box' );
function jlt_wpml_duplicate_job_company_field( $master_post_id, $lang, $post_array, $id ) {
	if ( empty( $id ) || empty( $master_post_id ) ) {
		return false;
	}
	if ( $post_array[ 'post_type' ] == 'job' ) {
		$company_id = get_post_meta( $master_post_id, '_company_id', true );

		if ( ! empty( $company_id ) ) {
			$company_id = apply_filters( 'wpml_object_id', $company_id, 'company', true, $lang );

			update_post_meta( $id, '_company_id', $company_id );
		}
	}
}

add_action( 'icl_make_duplicate', 'jlt_wpml_duplicate_job_company_field', 10, 4 );
