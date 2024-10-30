<?php
/**
 * Admin Job List Table
 */

function jlt_admin_job_approve_action() {
	if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'jlt_job_approve' ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'job-listings' ), '', array( 'response' => 403 ) );
		}

		if ( ! check_admin_referer( 'jlt-job-approve' ) ) {
			wp_die( __( 'You have taken too long. Please go back and retry.', 'job-listings' ), '', array( 'response' => 403 ) );
		}

		$post_id = ! empty( $_GET[ 'job_id' ] ) ? (int) $_GET[ 'job_id' ] : '';

		if ( ! $post_id || get_post_type( $post_id ) !== 'job' ) {
			die;
		}

		$job_data = array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		);
		wp_update_post( $job_data );
		do_action( 'jlt_job_after_approve', $post_id );
		wp_safe_redirect( esc_url_raw( remove_query_arg( array(
			'trashed',
			'untrashed',
			'deleted',
			'ids',
		), wp_get_referer() ) ) );
		die();
	}
}

add_action( 'admin_init', 'jlt_admin_job_approve_action' );

function jlt_admin_job_feature_action() {
	if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'jlt_job_feature' ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'job-listings' ), '', array( 'response' => 403 ) );
		}

		if ( ! check_admin_referer( 'jlt-job-feature' ) ) {
			wp_die( __( 'You have taken too long. Please go back and retry.', 'job-listings' ), '', array( 'response' => 403 ) );
		}

		$post_id = ! empty( $_GET[ 'job_id' ] ) ? (int) $_GET[ 'job_id' ] : '';

		if ( ! $post_id || get_post_type( $post_id ) !== 'job' ) {
			die;
		}

		$featured = jlt_get_post_meta( $post_id, '_featured' );

		if ( 'yes' === $featured ) {
			update_post_meta( $post_id, '_featured', 'no' );
		} else {
			update_post_meta( $post_id, '_featured', 'yes' );
		}

		wp_safe_redirect( esc_url_raw( remove_query_arg( array(
			'trashed',
			'untrashed',
			'deleted',
			'ids',
		), wp_get_referer() ) ) );
		die();
	}
}

add_action( 'admin_init', 'jlt_admin_job_feature_action' );

function jlt_admin_job_transition_post_status( $new_status, $old_status, $post ) {
	if ( $post->post_type !== 'job' ) {
		return;
	}

	if ( ! jlt_get_post_meta( $post->ID, '_in_review', '' ) ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	if ( $new_status == 'publish' && $old_status != 'publish' ) {
		$employer_id = $post->post_author;

		wp_update_post( array(
			'ID'            => $post->ID,
			'post_date'     => current_time( 'mysql' ),
			'post_date_gmt' => current_time( 'mysql', 1 ),
		) );

		jlt_set_job_expired( $post->ID );

		update_post_meta( $post->ID, '_in_review', '' );

		// employer email
		if ( jlt_email_get_setting( 'employer_job_approved', 'active', 1 ) ) {

			if ( is_multisite() ) {
				$blogname = $GLOBALS[ 'current_site' ]->site_name;
			} else {
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}
			$employer = get_user_by( 'id', $employer_id );

			$subject = jlt_email_get_setting( 'employer_job_approved', 'subject' );

			$array_subject = array(
				'[job_title]' => $post->post_title,
				'[site_name]' => $blogname,
				'[site_url]'  => esc_url( home_url( '' ) ),
			);
			$subject       = str_replace( array_keys( $array_subject ), $array_subject, $subject );

			$to = $employer->user_email;

			$array_message = array(
				'[job_title]'      => $post->post_title,
				'[job_url]'        => get_permalink( $post ),
				'[job_content]'    => $post->post_content,
				'[job_company]'    => $employer->display_name,
				'[job_manage_url]' => JLT_Member::get_endpoint_url( 'manage-job' ),
				'[site_name]'      => $blogname,
				'[site_url]'       => esc_url( home_url( '' ) ),
			);

			$message = jlt_email_get_setting( 'employer_job_approved', 'content' );
			$message = str_replace( array_keys( $array_message ), $array_message, $message );

			jlt_mail( $to, $subject, $message, array(), 'jlt_notify_job_review_approve_employer' );
		}
	}

	if ( $new_status == 'trash' ) {
		$employer_id = $post->post_author;

		update_post_meta( $post->ID, '_in_review', '' );

		jlt_decrease_job_posting_count( $employer_id );
		$featured = jlt_get_post_meta( $post->ID, '_featured' );
		if ( $featured == 'yes' ) {
			$job_featured = jlt_get_feature_job_added( $employer_id );
			update_user_meta( $employer_id, '_job_featured', max( $job_featured - 1, 0 ) );
		}

		// employer email
		if ( jlt_email_get_setting( 'employer_job_rejected', 'active', 1 ) ) {

			if ( is_multisite() ) {
				$blogname = $GLOBALS[ 'current_site' ]->site_name;
			} else {
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}
			$employer = get_user_by( 'id', $employer_id );

			$subject = jlt_email_get_setting( 'employer_job_rejected', 'subject' );

			$array_subject = array(
				'[job_title]' => $post->post_title,
				'[site_name]' => $blogname,
				'[site_url]'  => esc_url( home_url( '' ) ),
			);
			$subject       = str_replace( array_keys( $array_subject ), $array_subject, $subject );

			$to = $employer->user_email;

			$array_message = array(
				'[job_title]'      => $post->post_title,
				'[job_url]'        => get_permalink( $post ),
				'[job_content]'    => $post->post_content,
				'[job_company]'    => $employer->display_name,
				'[job_manage_url]' => JLT_Member::get_endpoint_url( 'manage-job' ),
				'[site_name]'      => $blogname,
				'[site_url]'       => esc_url( home_url( '' ) ),
			);

			$message = jlt_email_get_setting( 'employer_job_rejected', 'content' );
			$message = str_replace( array_keys( $array_message ), $array_message, $message );

			jlt_mail( $to, $subject, $message, array(), 'jlt_notify_job_review_reject_employer' );
		}
	}
}

add_action( 'transition_post_status', 'jlt_admin_job_transition_post_status', 10, 3 );

function jlt_admin_job_list_columns_header( $columns ) {
	if ( ! is_array( $columns ) ) {
		$columns = array();
	}

	$temp_title_col = $columns[ 'title' ];
	unset( $columns[ 'title' ], $columns[ 'date' ], $columns[ 'author' ] );

	$columns[ "job_type" ]     = __( "Type", 'job-listings' );
	$columns[ "title" ]        = $temp_title_col;
	$columns[ "job_category" ] = __( "Categories", 'job-listings' );
	$columns[ "job_posted" ]   = __( "Posted", 'job-listings' );
	$columns[ "job_closing" ]  = __( "Closing", 'job-listings' );
	$columns[ "job_expires" ]  = __( "Expired", 'job-listings' );
	$columns[ 'featured_job' ] = '<span class="tips" data-tip="' . __( "Is Job Featured?", 'job-listings' ) . '">' . __( "Featured?", 'job-listings' ) . '</span>';
	$columns[ 'application' ]  = '<span class="tips" data-tip="' . __( "Number of Application", 'job-listings' ) . '">' . __( "Application", 'job-listings' ) . '</span>';
	$columns[ 'job_status' ]   = __( "Status", 'job-listings' );
	if ( isset( $columns[ 'comments' ] ) ) {
		$temp = $columns[ 'comments' ];
		unset( $columns[ 'comments' ] );
		$columns[ 'comments' ] = $temp;
	}
	$columns[ 'job_actions' ] = __( "Actions", 'job-listings' );

	return $columns;
}

add_filter( 'manage_edit-job_columns', 'jlt_admin_job_list_columns_header' );

function jlt_admin_job_list_columns_data( $column ) {
	global $post, $wpdb;
	switch ( $column ) {
		case "job_type" :
			$type = jlt_get_job_type( $post );
			if ( $type ) {
				if ( ! empty( $type->color ) ) {
					edit_term_link( $type->name, '<span class="job-type ' . $type->slug . '" style="background-color:' . $type->color . ';">', '</span>', $type );
				} else {
					edit_term_link( $type->name, '<span class="job-type ' . $type->slug . '" style="color:#0073aa;font-size:13px;">', '</span>', $type );
				}
			}
			break;
		case "job_position" :
			echo '<div class="job_position">';
			echo '<a href="' . admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) . '" class="tips job_title" data-tip="' . sprintf( __( 'ID: %d', 'job-listings' ), $post->ID ) . '"><b>' . get_the_title( $post ) . '<b/></a>';

			echo '<div class="location">';

			$company_id = jlt_get_job_company( $post );
			if ( $company_id ) {
				$company_name = get_the_title( $company_id );
				echo '<span>' . __( 'for', 'job-listings' ) . '&nbsp;<a href="' . get_edit_post_link( $company_id ) . '">' . $company_name . '</a></span>';
			}

			echo '</div>';
			echo '</div>';
			break;
		case "job_category" :
			if ( ! $terms = get_the_terms( $post->ID, $column ) ) {
				echo '<span class="na">&ndash;</span>';
			} else {
				$terms_edit = array();
				foreach ( $terms as $term ) {
					$terms_edit[] = edit_term_link( $term->name, '', '', $term, false );
				}
				echo implode( ', ', $terms_edit );
			}
			break;
		case "job_posted" :
			echo '<strong>' . date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) . '</strong><span>';
			echo ( empty( $post->post_author ) ? __( 'by a guest', 'job-listings' ) : sprintf( __( 'by %s', 'job-listings' ), '<a href="' . get_edit_user_link( $post->post_author ) . '">' . get_the_author() . '</a>' ) ) . '</span>';
			break;
		case "job_closing" :
			if ( $post->_closing ) {
				$closing = ! is_numeric( $post->_closing ) ? strtotime( $post->_closing ) : $post->_closing;
				echo '<strong>' . date_i18n( get_option( 'date_format' ), $closing ) . '</strong>';
			} else {
				echo '&ndash;';
			}
			break;
		case "job_expires" :
			if ( $post->_expires ) {
				echo '<strong>' . date_i18n( get_option( 'date_format' ), $post->_expires ) . '</strong>';
			} else {
				echo '&ndash;';
			}
			break;
		case "featured_job" :
			$featured = jlt_get_post_meta( $post->ID, '_featured' );
			if ( empty( $featured ) ) {
				// Update old data
				update_post_meta( $post->ID, '_featured', 'no' );
			}
			$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=jlt_job_feature&job_id=' . $post->ID ), 'jlt-job-feature' );
			echo '<a href="' . esc_url( $url ) . '" title="' . __( 'Toggle featured', 'job-listings' ) . '">';
			if ( 'yes' === $featured ) {
				echo '<span class="jlt-job-feature" title="' . esc_attr__( 'Yes', 'job-listings' ) . '"><i class="dashicons dashicons-star-filled "></i></span>';
			} else {
				echo '<span class="jlt-job-feature not-featured"  title="' . esc_attr__( 'No', 'job-listings' ) . '"><i class="dashicons dashicons-star-empty"></i></span>';
			}
			echo '</a>';

			break;
		case "application" :
			$application_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'application' AND post_parent = {$post->ID}" );
			if ( $application_count > 0 ) {
				$url_args         = array(
					's'           => '',
					'post_status' => 'all',
					'post_type'   => 'application',
					'job'         => $post->ID,
					'action'      => - 1,
					'action2'     => - 1,
				);
				$application_link = esc_url( add_query_arg( $url_args, admin_url( 'edit.php' ) ) );
				echo '<strong><a href="' . $application_link . '">' . $application_count . '</a></strong>';
			} else {
				echo '&ndash;';
			}
			break;
		case "job_status" :
			$status      = jlt_correct_job_status( $post->ID, $post->post_status );
			$status_text = '';
			$statuses    = jlt_get_job_status();
			if ( isset( $statuses[ $status ] ) ) {
				$status_text = $statuses[ $status ];
			} else {
				$status_text = __( 'Inactive', 'job-listings' );
			}
			echo esc_html( $status_text );
			break;
		case "job_actions" :
			echo '<div class="actions">';
			$admin_actions = array();
			if ( $post->post_status == 'pending' && current_user_can( 'publish_post', $post->ID ) ) {
				$url                        = wp_nonce_url( admin_url( 'admin-ajax.php?action=jlt_job_approve&job_id=' . $post->ID ), 'jlt-job-approve' );
				$admin_actions[ 'approve' ] = array(
					'action' => 'approve',
					'name'   => __( 'Approve', 'job-listings' ),
					'url'    => $url,
					'icon'   => 'yes',
				);
			}
			if ( $post->post_status !== 'trash' ) {
				if ( current_user_can( 'read_post', $post->ID ) ) {
					$admin_actions[ 'view' ] = array(
						'action' => 'view',
						'name'   => __( 'View', 'job-listings' ),
						'url'    => $post->post_status == 'draft' ? esc_url( get_preview_post_link( $post ) ) : get_permalink( $post->ID ),
						'icon'   => 'visibility',
					);
				}
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$admin_actions[ 'edit' ] = array(
						'action' => 'edit',
						'name'   => __( 'Edit', 'job-listings' ),
						'url'    => get_edit_post_link( $post->ID ),
						'icon'   => 'edit',
					);
				}
				if ( current_user_can( 'delete_post', $post->ID ) ) {
					$admin_actions[ 'delete' ] = array(
						'action' => 'delete',
						'name'   => __( 'Delete', 'job-listings' ),
						'url'    => get_delete_post_link( $post->ID ),
						'icon'   => 'trash',
					);
				}
			}

			$admin_actions = apply_filters( 'job_manager_admin_actions', $admin_actions, $post );

			foreach ( $admin_actions as $action ) {
				printf( '<a class="button tips action-%1$s" href="%2$s" data-tip="%3$s">%4$s</a>', $action[ 'action' ], esc_url( $action[ 'url' ] ), esc_attr( $action[ 'name' ] ), '<i class="dashicons dashicons-' . $action[ 'icon' ] . '"></i>' );
			}

			echo '</div>';

			break;
	}
}

add_filter( 'manage_job_posts_custom_column', 'jlt_admin_job_list_columns_data' );

function jlt_admin_job_list_filter() {
	$type = 'post';
	if ( isset( $_GET[ 'post_type' ] ) ) {
		$type = sanitize_text_field( $_GET[ 'post_type' ] );
	}

	//only add filter to post type you want
	if ( 'job' == $type ) {
		global $post;

		// Company
		$companies = get_posts( array(
			'post_type'        => 'company',
			'posts_per_page'   => - 1,
			'post_status'      => 'publish',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
		) );
		?>
		<select name="company">
			<option value=""><?php _e( 'All Companies', 'job-listings' ); ?></option>
			<?php
			$current_v = isset( $_GET[ 'company' ] ) ? $_GET[ 'company' ] : '';
			foreach ( $companies as $company ) {
				if ( empty( $company->post_title ) ) {
					continue;
				}
				printf( '<option value="%s"%s>%s</option>', $company->ID, $company->ID == $current_v ? ' selected="selected"' : '', $company->post_title );
			}
			?>
		</select>
		<?php
		// Job Category
		$job_categories = get_terms( 'job_category' );
		?>
		<select name="job_category">
			<option value=""><?php _e( 'All Categories', 'job-listings' ); ?></option>
			<?php
			$current_v = isset( $_GET[ 'job_category' ] ) ? $_GET[ 'job_category' ] : '';
			foreach ( $job_categories as $job_category ) {
				printf( '<option value="%s"%s>%s</option>', $job_category->slug, $job_category->slug == $current_v ? ' selected="selected"' : '', $job_category->name );
			}
			?>
		</select>
		<?php
		// Job Location
		$job_locations = get_terms( 'job_location' );
		?>
		<select name="job_location">
			<option value=""><?php _e( 'All Locations', 'job-listings' ); ?></option>
			<?php
			$current_v = isset( $_GET[ 'job_location' ] ) ? $_GET[ 'job_location' ] : '';
			foreach ( $job_locations as $job_location ) {
				printf( '<option value="%s"%s>%s</option>', $job_location->slug, $job_location->slug == $current_v ? ' selected="selected"' : '', $job_location->name );
			}
			?>
		</select>
		<?php
		// Job Type
		$job_types = get_terms( 'job_type' );
		?>
		<select name="job_type">
			<option value=""><?php _e( 'All Types', 'job-listings' ); ?></option>
			<?php
			$current_v = isset( $_GET[ 'job_type' ] ) ? $_GET[ 'job_type' ] : '';
			foreach ( $job_types as $job_type ) {
				printf( '<option value="%s"%s>%s</option>', $job_type->slug, $job_type->slug == $current_v ? ' selected="selected"' : '', $job_type->name );
			}
			?>
		</select>
		<?php
		// Job Tag
		$job_tags = get_terms( 'job_tag' );
		?>
		<select name="job_tag">
			<option value=""><?php _e( 'All Tags', 'job-listings' ); ?></option>
			<?php
			$current_v = isset( $_GET[ 'job_tag' ] ) ? $_GET[ 'job_tag' ] : '';
			foreach ( $job_tags as $job_tag ) {
				printf( '<option value="%s"%s>%s</option>', $job_tag->slug, $job_tag->slug == $current_v ? ' selected="selected"' : '', $job_tag->name );
			}
			?>
		</select>
		<?php
	}
}

add_action( 'restrict_manage_posts', 'jlt_admin_job_list_filter' );

function jlt_admin_job_list_filter_action( $query ) {
	global $pagenow;
	$type = 'post';
	if ( isset( $_GET[ 'post_type' ] ) ) {
		$type = sanitize_text_field($_GET[ 'post_type' ]);
	}
	if ( 'job' == $type && is_admin() && $pagenow == 'edit.php' ) {
		if ( ! isset( $query->query_vars[ 'post_type' ] ) || $query->query_vars[ 'post_type' ] == 'job' ) {
			if ( isset( $_GET[ 'company' ] ) && $_GET[ 'company' ] != '' ) {
				$company_id = absint( $_GET[ 'company' ] );

				if ( ! empty( $company_id ) ) {
					$job_ids                         = JLT_Company::get_company_jobs( $company_id, array(), - 1, $_GET[ 'post_status' ] );
					$query->query_vars[ 'post__in' ] = array_merge( $job_ids, array( 0 ) );
				}
			}
			if ( isset( $_GET[ 'employer' ] ) && $_GET[ 'employer' ] != '' ) {
				$employer_id = absint( $_GET[ 'employer' ] );

				if ( ! empty( $employer_id ) ) {
					$query->query_vars[ 'author' ] = $employer_id;
				}
			}
		}
	}
}

add_filter( 'parse_query', 'jlt_admin_job_list_filter_action' );

function jlt_admin_job_list_views_status( $views ) {
	if ( isset( $views[ 'publish' ] ) ) {
		$views[ 'publish' ] = str_replace( 'Published ', _x( 'Active', 'Job status', 'job-listings' ) . ' ', $views[ 'publish' ] );
	}

	return $views;
}

add_filter( 'views_edit-job', 'jlt_admin_job_list_views_status' );
