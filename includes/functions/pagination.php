<?php
/**
 * Pagination Functions.
 *
 * @since 1.0.0
 */

function jlt_pagination( $args = array(), $query = null ) {
	global $wp_rewrite, $wp_query;

	do_action( 'jlt_pagination_start' );

	if ( ! empty( $query ) ) {
		$wp_query = $query;
	}

	if ( 1 >= $wp_query->max_num_pages ) {
		return;
	}

	// Paging for Member page

	$paged = ( get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1 );

	$max_num_pages = intval( $wp_query->max_num_pages );

	$defaults = array(
		'base'                   => esc_url( add_query_arg( 'paged', '%#%' ) ),
		'format'                 => '',
		'total'                  => $max_num_pages,
		'current'                => $paged,
		'prev_next'              => true,
		'prev_text'              => apply_filters( 'jlt_pagination_prev_text', '<i class="jlt-icon jltfa-arrow-left"></i>' ),
		'next_text'              => apply_filters( 'jlt_pagination_text', '<i class="jlt-icon jltfa-arrow-right"></i>' ),
		'show_all'               => false,
		'end_size'               => 1,
		'mid_size'               => 1,
		'add_fragment'           => '',
		'type'                   => 'list',
		'before'                 => '<div class="jlt-pagination">',
		'after'                  => '</div>',
		'echo'                   => true,
		'use_search_permastruct' => true,
	);

	$defaults = apply_filters( 'jlt_pagination_args_defaults', $defaults );

	if ( $wp_rewrite->using_permalinks() && ! is_search() ) {
		$defaults[ 'base' ] = user_trailingslashit( trailingslashit( get_pagenum_link() ) . 'page/%#%' );
	}

	if ( is_search() ) {
		$defaults[ 'use_search_permastruct' ] = false;
	}

	if ( is_search() ) {
		if ( class_exists( 'BP_Core_User' ) || $defaults[ 'use_search_permastruct' ] == false ) {
			$search_query       = get_query_var( 's' );
			$paged              = get_query_var( 'paged' );
			$base               = esc_url_raw( add_query_arg( 's', urlencode( $search_query ) ) );
			$base               = esc_url_raw( add_query_arg( 'paged', '%#%' ) );
			$defaults[ 'base' ] = $base;
		} else {
			$search_permastruct = $wp_rewrite->get_search_permastruct();
			if ( ! empty( $search_permastruct ) ) {
				$base               = get_search_link();
				$base               = esc_url_raw( add_query_arg( 'paged', '%#%', $base ) );
				$defaults[ 'base' ] = $base;
			}
		}
	}

	if ( $wp_rewrite->using_permalinks() ) {
		$defaults[ 'base' ] = esc_url_raw( user_trailingslashit( trailingslashit( get_pagenum_link() ) . 'page/%#%' ) );
	}

	// Ajax search JOB
	if ( is_post_type_archive( 'job' ) && isset( $wp_query->query_vars[ 'is_job_ajax_search' ] ) && $wp_query->query_vars[ 'is_job_ajax_search' ] ) {
		$job_archive_slug   = jlt_get_job_setting( 'archive_slug', 'jobs' );
		$defaults[ 'base' ] = str_replace( 'wp-admin/admin-ajax.php', $job_archive_slug . '/', $defaults[ 'base' ] );
	}

	$args = wp_parse_args( $args, $defaults );

	$args = apply_filters( 'jlt_pagination_args', $args );

	if ( 'array' == $args[ 'type' ] ) {
		$args[ 'type' ] = 'plain';
	}

	$pattern = '/\?(.*?)\//i';

	preg_match( $pattern, $args[ 'base' ], $raw_querystring );
	if ( ! empty( $raw_querystring ) ) {
		if ( $wp_rewrite->using_permalinks() && $raw_querystring ) {
			$raw_querystring[ 0 ] = str_replace( '', '', $raw_querystring[ 0 ] );
		}
		$args[ 'base' ] = str_replace( $raw_querystring[ 0 ], '', $args[ 'base' ] );
		$args[ 'base' ] .= substr( $raw_querystring[ 0 ], 0, - 1 );
	}

	//		remove &#038

	$pos = strpos( $args[ 'base' ], "&#038" );
	if ( $pos ) {
		$args[ 'base' ] = substr( $args[ 'base' ], 0, $pos );
	}

	$page_links = paginate_links( $args );

	$page_links = str_replace( array( '&#038;paged=1\'', '/page/1\'' ), '\'', $page_links );

	$page_links = $args[ 'before' ] . $page_links . $args[ 'after' ];

	$page_links = apply_filters( 'jlt_pagination', $page_links );

	do_action( 'jlt_pagination_end' );

	if ( ! empty( $query ) ) {
		wp_reset_query();
	}

	if ( $args[ 'echo' ] ) {
		echo $page_links;
	} else {
		return $page_links;
	}
}

function jlt_member_pagination( $query ) {

	if ( 1 >= $query->max_num_pages ) {
		return;
	}

	$paged = jlt_member_get_paged();

	$max_num_pages = intval( $query->max_num_pages );

	$defaults = array(
		'base'      => add_query_arg( 'current_page', '%#%' ),
		'format'    => '',
		'total'     => $max_num_pages,
		'current'   => $paged,
		'prev_next' => true,
		'prev_text' => apply_filters( 'jlt_pagination_prev_text', '<i class="jlt-icon jltfa-arrow-left"></i>' ),
		'next_text' => apply_filters( 'jlt_pagination_text', '<i class="jlt-icon jltfa-arrow-right"></i>' ),
		'show_all'  => false,
		'end_size'  => 1,
		'mid_size'  => 1,
		'type'      => 'list',
		'before'    => '<div class="jlt-pagination jlt-member-pagination">',
		'after'     => '</div>',
	);

	$page_links = paginate_links( $defaults );
	//$page_links = str_replace( array( '&#038;current_page=1\'' ,'?current_page=1' ), '', $page_links );
	$page_links = str_replace( array( '?current_page=1', '&current_page=1', 'current_page=1' ), '', $page_links );
	$page_links = $defaults[ 'before' ] . $page_links . $defaults[ 'after' ];
	echo $page_links;
	wp_reset_query();
}