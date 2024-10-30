<?php

function jlt_user_job_query( $employer_id = '', $is_paged = true, $status = array() ) {
	if ( empty( $employer_id ) ) {
		$employer_id = get_current_user_ID();
	}

	$args = array(
		'post_type' => 'job',
		'author'    => $employer_id,
	);

	if ( $is_paged ) {
		if ( is_front_page() || is_home() ) {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}
		$args[ 'paged' ] = $paged;
	}

	if ( ! empty( $status ) ) {
		$args[ 'post_status' ] = $status;
	} else {
		$args[ 'post_status' ] = array( 'publish', 'pending', 'pending_payment', 'expired', 'inactive' );
	}

	$user_job_query = new WP_Query( $args );

	return $user_job_query;
}

function jlt_job_query_from_request( &$query, $REQUEST = array() ) {
	if ( empty( $query ) || empty( $REQUEST ) ) {
		return $query;
	}

	$tax_query = array();
	$tax_list  = array(
		'location' => 'job_location',
		'category' => 'job_category',
		'type'     => 'job_type',
		'tag'      => 'job_tag',
	);
	$tax_list  = apply_filters( 'jlt_job_query_tax_list', $tax_list );
	foreach ( $tax_list as $tax_key => $term ) {
		if ( isset( $REQUEST[ $tax_key ] ) && ! empty( $REQUEST[ $tax_key ] ) ) {
			$tax_query[] = array(
				'taxonomy' => $term,
				'field'    => 'slug',
				'terms'    => $REQUEST[ $tax_key ],
			);
		}
	}

	$tax_query = apply_filters( 'jlt_job_search_tax_query', $tax_query, $REQUEST );

	if ( ! empty( $tax_query ) ) {
		$tax_query[ 'relation' ] = 'AND';
		if ( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
			$query->tax_query->queries        = $tax_query;
			$query->query_vars[ 'tax_query' ] = $query->tax_query->queries;

			// tag is a reserved keyword so we'll have to remove it from the query
			unset( $query->query[ 'tag' ] );
			unset( $query->query_vars[ 'tag' ] );
			unset( $query->query_vars[ 'tag__in' ] );
			unset( $query->query_vars[ 'tag_slug__in' ] );
		} elseif ( is_array( $query ) ) {
			$query[ 'tax_query' ] = $tax_query;
		}
	}

	$meta_query = array();
	$get_keys   = array_keys( $REQUEST );

	$job_fields = jlt_get_job_search_custom_fields();
	foreach ( $job_fields as $field ) {
		$field_id = jlt_job_custom_fields_name( $field[ 'name' ], $field );
		if ( isset( $REQUEST[ $field_id ] ) && ! empty( $REQUEST[ $field_id ] ) ) {
			$value = jlt_sanitize_field( $REQUEST[ $field_id ], $field );
			if ( is_array( $value ) ) {
				$temp_meta_query = array( 'relation' => 'OR' );
				foreach ( $value as $v ) {
					if ( empty( $v ) ) {
						continue;
					}
					$temp_meta_query[] = array(
						'key'     => $field_id,
						'value'   => '"' . $v . '"',
						'compare' => 'LIKE',
					);
				}
				$meta_query[] = $temp_meta_query;
			} else {
				$meta_query[] = array(
					'key'   => $field_id,
					'value' => $value,
				);
			}
		} elseif ( ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'datepicker' ) && ( isset( $REQUEST[ $field_id . '_start' ] ) || isset( $REQUEST[ $field_id . '_end' ] ) ) ) {
			$value_start = isset( $REQUEST[ $field_id . '_start' ] ) && ! empty( $REQUEST[ $field_id . '_start' ] ) ? $REQUEST[ $field_id . '_start' ] : 0;
			$value_end   = isset( $REQUEST[ $field_id . '_end' ] ) && ! empty( $REQUEST[ $field_id . '_end' ] ) ? $REQUEST[ $field_id . '_end' ] : 0;
			if ( ! empty( $value_start ) || ! empty( $value_end ) ) {
				if ( $field_id == 'date' ) {
					$date_query = array();
					if ( ! empty( $value_start ) ) {
						$start                 = is_numeric( $value_start ) ? date( 'Y-m-d', $value_start ) : $value_start;
						$date_query[ 'after' ] = date( 'Y-m-d', strtotime( $start . ' -1 day' ) );
					}
					if ( isset( $value_end ) && ! empty( $value_end ) ) {
						$end                    = is_numeric( $value_end ) ? date( 'Y-m-d', $value_end ) : $value_end;
						$date_query[ 'before' ] = date( 'Y-m-d', strtotime( $end . ' +1 day' ) );
					}

					if ( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
						$query->query_vars[ 'date_query' ][] = $date_query;
					} elseif ( is_array( $query ) ) {
						$query[ 'date_query' ] = $date_query;
					}
				} else {
					$value_start = ! empty( $value_start ) ? jlt_sanitize_field( $value_start, $field ) : 0;
					$value_start = ! empty( $value_start ) ? strtotime( "midnight", $value_start ) : 0;
					$value_end   = ! empty( $value_end ) ? jlt_sanitize_field( $value_end, $field ) : 0;
					$value_end   = ! empty( $value_end ) ? strtotime( "tomorrow", strtotime( "midnight", $value_end ) ) - 1 : strtotime( '2090/12/31' );

					$meta_query[] = array(
						'key'     => $field_id,
						'value'   => array( $value_start, $value_end ),
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC',
					);
				}
			}
		}
	}

	$meta_query = apply_filters( 'jlt_job_search_meta_query', $meta_query, $REQUEST );

	if ( ! empty( $meta_query ) ) {
		$meta_query[ 'relation' ] = 'AND';
		if ( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
			$query->query_vars[ 'meta_query' ][] = $meta_query;
		} elseif ( is_array( $query ) ) {
			$query[ 'meta_query' ] = $meta_query;
		}
	}

	return apply_filters( 'jlt_job_search_query', $query, $REQUEST );
}

function jlt_job_query_vars_default() {
	global $wp;
	// Set custom vars

	$vars = array(
		'keyword' => get_query_var( 'keyword' ),
	);
	// Add query vars

	foreach ( array_keys( $vars ) as $var ) {
		$wp->add_query_var( $var );
	}

	return $vars;
}

add_filter( 'init', 'jlt_job_query_vars_default' );

function jlt_job_listings( $query = array(), $args = array() ) {

	$job_query_args = array(
		'post_type'   => 'job',
		'p'           => '',
		'post__in'    => '',
		'offset'      => '',
		'post_status' => '',
		'author'      => '',
		'tax_query'   => array(),
		'meta_query'  => array(),
	);

	$job_query_args = array_merge( $job_query_args, jlt_job_query_vars_default() );

	if ( is_object( $query ) && isset( $query->query_vars ) ) {
		$args = $query->query_vars;
	}

	$args = array_merge( $job_query_args, $args );

	//	Keyword
	if ( $args[ 'keyword' ] ) {
		$args[ 's' ] = $args[ 'keyword' ];
	}

	//	Paged
	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
		$paged = get_query_var( 'page' );
	} else {
		$paged = 1;
	}

	if ( isset( $args[ 'paged' ] ) ) {
		$paged = absint( $args[ 'paged' ] );
	}

	$args[ 'paged' ] = $paged;

	//	Taxonomies Query
	$tax_query = array();
	$tax_list  = jlt_get_job_taxonomies();

	foreach ( $tax_list as $tax_key => $term ) {
		$tax_key = str_replace( 'job_', '', $term );
		if ( isset( $_REQUEST[ $tax_key ] ) && $_REQUEST[ $tax_key ] != 'all' && $_REQUEST[ $tax_key ] != '' ) {

			$tax_query[] = array(
				'taxonomy' => $term,
				'field'    => 'slug',
				'terms'    => $_REQUEST[ $tax_key ],
			);
		}
	}

	$tax_query = apply_filters( 'jlt_job_search_tax_query', $tax_query );

	if ( ! empty( $tax_query ) ) {
		$tax_query[ 'relation' ] = 'AND';
		$args[ 'tax_query' ]     = $tax_query;
	}

	//	Custom Fields Query
	$meta_query = array();
	$job_fields = jlt_get_job_search_custom_fields();

	foreach ( $job_fields as $field ) {
		$field_id = jlt_job_custom_fields_name( $field[ 'name' ], $field );
		if ( isset( $_REQUEST[ $field_id ] ) && ! empty( $_REQUEST[ $field_id ] ) ) {
			$value = jlt_sanitize_field( $_REQUEST[ $field_id ], $field );
			if ( is_array( $value ) ) {
				$temp_meta_query = array( 'relation' => 'OR' );
				foreach ( $value as $v ) {
					if ( empty( $v ) ) {
						continue;
					}
					$temp_meta_query[] = array(
						'key'     => $field_id,
						'value'   => '"' . $v . '"',
						'compare' => 'LIKE',
					);
				}
				$meta_query[] = $temp_meta_query;
			} else {
				$meta_query[] = array(
					'key'   => $field_id,
					'value' => $value,
				);
			}
		} elseif ( ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'datepicker' ) && ( isset( $_REQUEST[ $field_id . '_start' ] ) || isset( $_REQUEST[ $field_id . '_end' ] ) ) ) {
			$value_start = isset( $_REQUEST[ $field_id . '_start' ] ) && ! empty( $_REQUEST[ $field_id . '_start' ] ) ? $_REQUEST[ $field_id . '_start' ] : 0;
			$value_end   = isset( $_REQUEST[ $field_id . '_end' ] ) && ! empty( $_REQUEST[ $field_id . '_end' ] ) ? $_REQUEST[ $field_id . '_end' ] : 0;
			if ( ! empty( $value_start ) || ! empty( $value_end ) ) {
				if ( $field_id == 'date' ) {
					$date_query = array();
					if ( ! empty( $value_start ) ) {
						$start                 = is_numeric( $value_start ) ? date( 'Y-m-d', $value_start ) : $value_start;
						$date_query[ 'after' ] = date( 'Y-m-d', strtotime( $start . ' -1 day' ) );
					}
					if ( isset( $value_end ) && ! empty( $value_end ) ) {
						$end                    = is_numeric( $value_end ) ? date( 'Y-m-d', $value_end ) : $value_end;
						$date_query[ 'before' ] = date( 'Y-m-d', strtotime( $end . ' +1 day' ) );
					}

					if ( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
						$query->query_vars[ 'date_query' ][] = $date_query;
					} elseif ( is_array( $query ) ) {
						$query[ 'date_query' ] = $date_query;
					}
				} else {
					$value_start = ! empty( $value_start ) ? jlt_sanitize_field( $value_start, $field ) : 0;
					$value_start = ! empty( $value_start ) ? strtotime( "midnight", $value_start ) : 0;
					$value_end   = ! empty( $value_end ) ? jlt_sanitize_field( $value_end, $field ) : 0;
					$value_end   = ! empty( $value_end ) ? strtotime( "tomorrow", strtotime( "midnight", $value_end ) ) - 1 : strtotime( '2090/12/31' );

					$meta_query[] = array(
						'key'     => $field_id,
						'value'   => array( $value_start, $value_end ),
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC',
					);
				}
			}
		}
	}

	$meta_query = apply_filters( 'jlt_job_search_meta_query', $meta_query );

	if ( ! empty( $meta_query ) ) {
		$meta_query[ 'relation' ] = 'AND';
		$args[ 'meta_query' ]     = $meta_query;
	}

	//	Args to Query

	$args = apply_filters( 'jlt_job_listings_args', $args );

	$result = new WP_Query( $args );
	wp_reset_query();

	return apply_filters( 'jlt_job_listings', $result, $job_query_args, $query );
}

function jlt_get_query_found_posts( $args = array() ) {

	$rs = jlt_job_listings( array(), $args );

	return $rs->found_posts;
}

function jlt_company_listings( $query = array(), $args = array() ) {

	$company_query_args = array(
		'post_type'   => 'company',
		'p'           => '',
		'post__in'    => '',
		'offset'      => '',
		'post_status' => '',
		'author'      => '',
		'tax_query'   => array(),
		'meta_query'  => array(),
	);

	if ( is_object( $query ) && isset( $query->query_vars ) ) {
		$args = $query->query_vars;
	}

	$args = array_merge( $company_query_args, $args );

	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
		$paged = get_query_var( 'page' );
	} else {
		$paged = 1;
	}

	if ( isset( $args[ 'paged' ] ) ) {
		$paged = absint( $args[ 'paged' ] );
	}

	$args[ 'paged' ] = $paged;

	//	Args to Query

	$args = apply_filters( 'jlt_company_listings_args', $args );

	$result = new WP_Query( $args );
	wp_reset_query();

	return apply_filters( 'jlt_company_listings', $result, $company_query_args, $query );
}