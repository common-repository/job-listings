<?php

if ( ! function_exists( 'jlt_member_admin_init' ) ) :
	function jlt_member_admin_init() {
		register_setting( 'jlt_company_custom_field', 'jlt_company_custom_field' );
		register_setting( 'jlt_candidate_custom_field', 'jlt_candidate_custom_field' );
	}

	add_filter( 'admin_init', 'jlt_member_admin_init' );
endif;

if ( ! function_exists( 'jlt_get_members' ) ) :
	function jlt_get_members( $role = '' ) {
		$transient_name = 'jlt_members_' . $role;

		if ( false !== ( $users = get_transient( $transient_name ) ) ) {
			return $users;
		}

		$users = get_users( array( 'role' => $role, 'orderby' => 'display_name' ) );

		set_transient( $transient_name, $users, DAY_IN_SECONDS );

		return $users;
	}
endif;

if ( ! function_exists( 'jlt_get_member_ids' ) ) :
	function jlt_get_member_ids( $role = '' ) {
		$transient_name = 'jlt_member_ids_' . $role;

		if ( false !== ( $users = get_transient( $transient_name ) ) ) {
			return $users;
		}

		$users = get_users( array( 'role' => $role, 'fields' => 'ID' ) );

		set_transient( $transient_name, $users, DAY_IN_SECONDS );

		return $users;
	}
endif;

if ( ! function_exists( 'jlt_remove_transient_members' ) ) :

	/**
	 * Remove users transient whenever a user is created or updated
	 *
	 * @param  int $user_id ID of the user
	 */
	function jlt_remove_transient_members( $user_id ) {
		$role = JLT_Member::get_user_role( $user_id );
		delete_transient( 'jlt_members_' . $role );
		delete_transient( 'jlt_member_ids_' . $role );
	}

	add_action( 'profile_update', 'jlt_remove_transient_members', 10, 1 );
	add_action( 'user_register', 'jlt_remove_transient_members', 10, 1 );
endif;

if ( ! function_exists( 'jlt_list_endpoints_employer' ) ):
	function jlt_list_endpoints_employer() {
		$endpoints = array(
			array(
				'key'          => 'manage-job',
				'value'        => jlt_get_endpoints_setting( 'manage-job', 'manage-job' ),
				'text'         => __( 'Manage Jobs', 'job-listings' ),
				'order'        => 5,
				'show_in_menu' => true,
			),
			array(
				'key'          => 'edit-job',
				'value'        => jlt_get_endpoints_setting( 'edit-job', 'edit-job' ),
				'text'         => __( 'Edit job', 'job-listings' ),
				'order'        => 5,
				'show_in_menu' => false,
			),
			array(
				'key'          => 'manage-application',
				'value'        => jlt_get_endpoints_setting( 'manage-application', 'manage-application' ),
				'text'         => __( 'Manage Applications', 'job-listings' ),
				'order'        => 10,
				'show_in_menu' => true,
			),
			array(
				'key'          => 'company-profile',
				'value'        => jlt_get_endpoints_setting( 'company-profile', 'company-profile' ),
				'text'         => __( 'Company Profile', 'job-listings' ),
				'order'        => 25,
				'show_in_menu' => true,
			),
		);

		return apply_filters( 'jlt_list_endpoints_employer', $endpoints );
	}
endif;

if ( ! function_exists( 'jlt_endpoints_employer' ) ) :

	function jlt_endpoints_employer() {
		$list      = jlt_list_endpoints_employer();
		$endpoints = array();
		foreach ( $list as $l ) {
			$endpoints[ $l[ 'key' ] ] = $l[ 'value' ];
		}

		return $endpoints;
	}
endif;

if ( ! function_exists( 'jlt_get_employer_menu' ) ) :
	function jlt_get_employer_menu() {
		$endpoints = array();
		$all       = jlt_list_endpoints_employer();

		foreach ( $all as $endpoint ) {
			if ( $endpoint[ 'show_in_menu' ] ) {
				$endpoints[] = array(
					'url'   => $endpoint[ 'value' ],
					'text'  => $endpoint[ 'text' ],
					'order' => $endpoint[ 'order' ],
				);
			}
		}

		$endpoints = apply_filters( 'jlt_get_employer_menu_items', $endpoints );

		// Remove missing endpoints.
		foreach ( $endpoints as $k => $endpoint ) {
			if ( empty( $endpoint[ 'url' ] ) ) {
				unset( $endpoints[ $k ] );
			}
		}

		// Order
		$order = array();
		foreach ( $endpoints as $key => $row ) {
			$order[ $key ] = $row[ 'order' ];
		}
		array_multisort( $order, SORT_ASC, $endpoints );

		return $endpoints;
	}
endif;

function jlt_list_endpoints_candidate() {
	$endpoints = array(
		array(
			'key'          => 'manage-job-applied',
			'value'        => jlt_get_endpoints_setting( 'manage-job-applied', 'manage-job-applied' ),
			'text'         => __( 'Manage Applications', 'job-listings' ),
			'order'        => 10,
			'show_in_menu' => true,
		),
		array(
			'key'          => 'candidate-profile',
			'value'        => jlt_get_endpoints_setting( 'candidate-profile', 'candidate-profile' ),
			'text'         => __( 'Candidate Profile', 'job-listings' ),
			'order'        => 30,
			'show_in_menu' => true,
		),
	);

	return apply_filters( 'jlt_list_endpoints_candidate', $endpoints );
}

if ( ! function_exists( 'jlt_get_candidate_menu' ) ) :
	function jlt_get_candidate_menu() {
		$endpoints = array();
		$all       = jlt_list_endpoints_candidate();

		foreach ( $all as $endpoint ) {
			if ( $endpoint[ 'show_in_menu' ] ) {
				$endpoints[] = array(
					'url'   => $endpoint[ 'value' ],
					'text'  => $endpoint[ 'text' ],
					'order' => $endpoint[ 'order' ],
				);
			}
		}

		$endpoints = apply_filters( 'jlt_get_candidate_menu_items', $endpoints );

		// Remove missing endpoints.
		foreach ( $endpoints as $k => $endpoint ) {
			if ( empty( $endpoint[ 'url' ] ) ) {
				unset( $endpoints[ $k ] );
			}
		}

		// Order
		$order = array();
		foreach ( $endpoints as $key => $row ) {
			$order[ $key ] = $row[ 'order' ];
		}
		array_multisort( $order, SORT_ASC, $endpoints );

		return $endpoints;
	}
endif;

if ( ! function_exists( 'jlt_endpoints_candidate' ) ) :

	function jlt_endpoints_candidate() {
		$list      = jlt_list_endpoints_candidate();
		$endpoints = array();
		foreach ( $list as $l ) {
			$endpoints[ $l[ 'key' ] ] = $l[ 'value' ];
		}

		return $endpoints;
	}
endif;

if ( ! function_exists( 'jlt_unique_query_vars' ) ) :

	function jlt_unique_query_vars( $endpoints ) {
		$list_candidate = jlt_endpoints_candidate();
		$list_employer  = jlt_endpoints_employer();

		$lists     = array_merge( $list_candidate, $list_employer );
		$endpoints = array_merge( $endpoints, $lists );
		$result    = array_unique( $endpoints );

		return $result;
	}

	add_filter( 'jlt_member_list_query_vars', 'jlt_unique_query_vars' );
endif;

if ( ! function_exists( 'jlt_get_member_enpoint_url' ) ) :

	function jlt_get_member_endpoint_url( $endpoint ) {
		$member_page_url = JLT_Member::get_member_page_url();
		$url             = jlt_get_endpoint_url( $endpoint, '', $member_page_url );

		return $url;
	}

endif;

if ( ! function_exists( 'jlt_employer_navigation' ) ) {
	function jlt_employer_navigation() {
		jlt_get_template( 'member/navigation-employer.php' );
	}
}

if ( ! function_exists( 'jlt_candidate_navigation' ) ) {
	function jlt_candidate_navigation() {
		jlt_get_template( 'member/navigation-candidate.php' );
	}
}

if ( ! function_exists( 'jlt_member_navigation' ) ) :

	function jlt_member_navigation() {
		if ( ! jlt_is_logged_in() ) {
			return;
		}
		if ( JLT_Member::is_employer() ) {
			jlt_employer_navigation();
		} else {
			jlt_candidate_navigation();
		}
	}

endif;

if ( ! function_exists( 'jlt_member_can_register' ) ) :

	function jlt_member_can_register() {
		return JLT_Member::can_register();
	}

endif;

if ( ! function_exists( 'jlt_member_register_url' ) ) :

	function jlt_member_register_url() {
		return JLT_Member::get_register_url();
	}

endif;

if ( ! function_exists( 'jlt_member_login_url' ) ) :

	function jlt_member_login_url() {
		return JLT_Member::get_login_url();
	}

endif;

if ( ! function_exists( 'jlt_member_logout_url' ) ) :

	function jlt_member_logout_url() {
		return JLT_Member::get_logout_url();
   }

endif;

if ( ! function_exists( 'jlt_member_page_url' ) ) :

	function jlt_member_page_url() {
		return JLT_Member::get_member_page_url();
	}

endif;

if ( ! function_exists( 'jlt_get_endpoint_value' ) ) :

	function jlt_get_endpoint_value( $key ) {
		$list_endpoint = jlt_list_endpoints_candidate();
		$value         = array_search( $key, array_column( $list_endpoint, 'key' ), true );
		if ( ! $value ) {
			return $list_endpoint[ $value ][ 'value' ];
		}

		return '';
	}
endif;

if ( ! function_exists( 'jlt_all_endpoints' ) ) :

	function jlt_all_endpoints() {
		$list_endpoint_candidate = jlt_endpoints_candidate();
		$list_endpoint_employer  = jlt_endpoints_employer();
		$list_endpoint           = array_merge( $list_endpoint_candidate, $list_endpoint_employer );
		return apply_filters( 'jlt_member_list_endpoint', $list_endpoint );
	}

endif;

if ( ! function_exists( 'jlt_is_member_page' ) ) :

	function jlt_is_member_page( $page ) {
		if ( is_object( $page ) ) {
			$page_id = $page->ID;
		} else {
			$page_id = $page;
		}
		if ( JLT_Member::get_member_page_id() == $page_id ) {
			return true;
		}

		return false;
	}

endif;

if ( ! function_exists( 'jlt_member_get_paged' ) ) :

	function jlt_member_get_paged() {
		$paged = isset( $_GET[ 'current_page' ] ) ? $_GET[ 'current_page' ] : 1;

		return $paged;
	}

endif;