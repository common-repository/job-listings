<?php
/**
 * Job Location.
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . '/common/google-map/location.php';

function jlt_geolocation_enabled() {
	return apply_filters( 'jlt_job_geolocation_enabled', true );
}

function jlt_get_geolocation( $raw_address = '' ) {
	$invalid_chars = array( " " => "+", "," => "", "?" => "", "&" => "", "=" => "", "#" => "" );
	$raw_address   = trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_address ) ) );

	if ( empty( $raw_address ) ) {
		return false;
	}

	$transient_name               = 'geocode_' . md5( $raw_address );
	$geocoded_address             = get_transient( $transient_name );
	$jlt_geocode_over_query_limit = get_transient( 'jlt_geocode_over_query_limit' );

	// Query limit reached - don't geocode for a while
	if ( $jlt_geocode_over_query_limit && false === $geocoded_address ) {
		return false;
	}

	try {
		if ( false === $geocoded_address || empty( $geocoded_address->results[ 0 ] ) ) {
			$url    = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
			$result = wp_remote_get( apply_filters( 'jlt_job_geolocation_endpoint', $url . $raw_address . "&region=" . apply_filters( 'jlt_job_geolocation_region_cctld', '', $raw_address ), $raw_address ), array(
				'timeout'     => 60,
				'redirection' => 1,
				'httpversion' => '1.1',
				'user-agent'  => 'WordPress JobListings; ' . home_url( '/' ),
				'sslverify'   => false,
			) );
			if ( ! is_wp_error( $result ) && $result[ 'body' ] ) {
				$result           = wp_remote_retrieve_body( $result );
				$geocoded_address = json_decode( $result );

				if ( $geocoded_address->status ) {
					switch ( $geocoded_address->status ) {
						case 'ZERO_RESULTS' :
							throw new Exception( __( "No results found", 'job-listings' ) );
							break;
						case 'OVER_QUERY_LIMIT' :
							set_transient( 'jlt_geocode_over_query_limit', 1, MINUTE_IN_SECONDS );
							throw new Exception( __( "Query limit reached", 'job-listings' ) );
							break;
						case 'OK' :
							if ( ! empty( $geocoded_address->results[ 0 ] ) ) {
								set_transient( $transient_name, $geocoded_address, MONTH_IN_SECONDS );
							} else {
								throw new Exception( __( "Geocoding error", 'job-listings' ) );
							}
							break;
						default :
							throw new Exception( __( "Geocoding error", 'job-listings' ) );
							break;
					}
				} else {
					throw new Exception( __( "Geocoding error", 'job-listings' ) );
				}
			} else {
				throw new Exception( __( "Geocoding error", 'job-listings' ) );
			}
		}
	} catch ( Exception $e ) {
		return false;
	}

	$address                        = array();
	$address[ 'lat' ]               = sanitize_text_field( $geocoded_address->results[ 0 ]->geometry->location->lat );
	$address[ 'long' ]              = sanitize_text_field( $geocoded_address->results[ 0 ]->geometry->location->lng );
	$address[ 'formatted_address' ] = sanitize_text_field( $geocoded_address->results[ 0 ]->formatted_address );

	if ( ! empty( $geocoded_address->results[ 0 ]->address_components ) ) {
		$address_data               = $geocoded_address->results[ 0 ]->address_components;
		$street_number              = false;
		$address[ 'street' ]        = false;
		$address[ 'city' ]          = false;
		$address[ 'state_short' ]   = false;
		$address[ 'state_long' ]    = false;
		$address[ 'zipcode' ]       = false;
		$address[ 'country_short' ] = false;
		$address[ 'country_long' ]  = false;

		foreach ( $address_data as $data ) {
			switch ( $data->types[ 0 ] ) {
				case 'street_number' :
					$address[ 'street' ] = sanitize_text_field( $data->long_name );
					break;
				case 'route' :
					$route = sanitize_text_field( $data->long_name );

					if ( ! empty( $address[ 'street' ] ) ) {
						$address[ 'street' ] = $address[ 'street' ] . ' ' . $route;
					} else {
						$address[ 'street' ] = $route;
					}
					break;
				case 'sublocality_level_1' :
				case 'locality' :
					$address[ 'city' ] = sanitize_text_field( $data->long_name );
					break;
				case 'administrative_area_level_1' :
					$address[ 'state_short' ] = sanitize_text_field( $data->short_name );
					$address[ 'state_long' ]  = sanitize_text_field( $data->long_name );
					break;
				case 'postal_code' :
					$address[ 'postcode' ] = sanitize_text_field( $data->long_name );
					break;
				case 'country' :
					$address[ 'country_short' ] = sanitize_text_field( $data->short_name );
					$address[ 'country_long' ]  = sanitize_text_field( $data->long_name );
					break;
			}
		}
	}

	return $address;
}

function jlt_job_location_save_geo_data( $term_id, $tt_id, $taxonomy ) {
	if ( 'job_location' === $taxonomy && jlt_geolocation_enabled() ) {
		if ( function_exists( 'get_term_meta' ) ) {
			// $geolocation = get_term_meta( $term_id, '_geolocation', true );

			// if( empty( $geolocation ) ) {
			$term = get_term( $term_id, 'job_location' );
			if ( $term && ! is_wp_error( $term ) ) {
				$geolocation = jlt_get_geolocation( $term->slug );

				update_term_meta( $term_id, '_geolocation', $geolocation );
			}
			// }
		} else {
			// Support for WordPress version 4.3 and older.
			$jlt_job_geolocation = get_option( 'jlt_job_geolocation' );
			if ( ! $jlt_job_geolocation ) {
				$jlt_job_geolocation = array();
			}

			$term = get_term( $term_id, 'job_location' );
			if ( $term && ! is_wp_error( $term ) ) {
				if ( ! isset( $jlt_job_geolocation[ $term->slug ] ) ) {
					$location_geo_data = jlt_get_geolocation( $term->name );
					if ( $location_geo_data && ! is_wp_error( $location_geo_data ) ) {
						$jlt_job_geolocation[ $term->slug ] = $location_geo_data;
					}
				}
			}

			//update geo option
			update_option( 'jlt_job_geolocation', $jlt_job_geolocation );
		}

		delete_transient( 'jlt_transient_job_markers' );
	}
}

add_action( 'created_term', 'jlt_job_location_save_geo_data', 10, 3 );
add_action( 'edit_term', 'jlt_job_location_save_geo_data', 10, 3 );

function jlt_location_enqueue_scripts() {
	if ( is_page() && ( jlt_is_job_posting_page() || get_the_ID() == JLT_Member::get_member_page_id() ) ) {
		wp_enqueue_script( 'google-map' );
	}
}

add_action( 'wp_enqueue_scripts', 'jlt_location_enqueue_scripts', 100 );

function jlt_job_render_field_job_location( $field = array(), $field_id = '', $value = array(), $form_type = '', $object = array() ) {
	if ( $form_type != 'search' ) :
		if ( ! empty( $object ) && isset( $object[ 'ID' ] ) ) {
			$job_id = absint( $object[ 'ID' ] );
		}
		$location_address = get_post_meta( $job_id, '_location_address', true );
		?>
		<div id="job_location_field">
			<?php
			$allow_user_input = strpos( $field[ 'type' ], 'input' ) !== false;
			$field[ 'type' ]  = strpos( $field[ 'type' ], 'single' ) !== false ? 'select' : 'multiple_select';
			jlt_render_select_field( $field, $field_id, $value, $form_type );

			if ( $form_type != 'search' && $allow_user_input ) {
				jlt_job_add_new_location();
			}
			?>
		</div>
	<?php else: ?>
		<?php
		$field[ 'type' ] = strpos( $field[ 'type' ], 'single' ) !== false ? 'select' : 'multiple_select';
		jlt_render_select_field( $field, $field_id, $value, $form_type );
		?>
	<?php endif;
}

add_filter( 'jlt_render_field_job_location', 'jlt_job_render_field_job_location', 10, 5 );
add_filter( 'jlt_render_field_multi_location_input', 'jlt_job_render_field_job_location', 10, 5 );
add_filter( 'jlt_render_field_multi_location', 'jlt_job_render_field_job_location', 10, 5 );
add_filter( 'jlt_render_field_single_location_input', 'jlt_job_render_field_job_location', 10, 5 );
add_filter( 'jlt_render_field_single_location', 'jlt_job_render_field_job_location', 10, 5 );

function jlt_job_add_new_location( $data_type = 'slug' ) {
	?>
	<div class="add-new-location">
		<i title="<?php _e( 'Add New Location', 'job-listings' ); ?>" class="jlt-icon jltfa-plus-circle"></i>
	</div>
	<div class="add-new-location-content" style="display:none">
		<input id="add-google-location" type="text" value="" class="jlt-form-control input-text"
		       placeholder="<?php echo esc_attr__( 'Enter new location', 'job-listings' ) ?>" style="height: 36px;">
		<button class="jlt-btn add-new-location-submit" data-return-type="<?php echo $data_type; ?>"
		        type="button"><?php _e( 'Add', 'job-listings' ) ?></button>
	</div>
	<?php $enable_auto_complete = jlt_get_location_setting( 'enable_auto_complete', 1 ); ?>
	<?php if ( $enable_auto_complete ) :
		$country_restriction = jlt_get_location_setting( 'country_restriction', '' );
		?>
		<script>
			jQuery(document).ready(function () {
				var input = document.getElementById('add-google-location');
				var options = {
					<?php if( ! empty( $country_restriction ) ) : ?>
					componentRestrictions: {country: '<?php echo $country_restriction; ?>'},
					<?php endif; ?>
					types: ['<?php echo jlt_get_location_setting( 'location_type', 'cities' ); ?>']
				};

				autocomplete = new google.maps.places.Autocomplete(input, options);
				google.maps.event.addDomListener(input, 'keydown', function (e) {
					if (e.keyCode == 13) {
						e.preventDefault();
					}
				});
			});
		</script>
	<?php endif;
}

function jlt_job_get_term_geolocation( $term = null ) {
	$term_id = is_object( $term ) ? $term->term_id : ( is_numeric( $term ) ? $term : 0 );
	if ( empty( $term_id ) ) {
		return false;
	}

	$term = is_object( $term ) ? $term : get_term( $term_id, 'job_location' );
	if ( empty( $term ) || is_wp_error( $term ) ) {
		return false;
	}
	$geolocation = false;
	if ( function_exists( 'get_term_meta' ) ) {
		$geolocation = get_term_meta( $term_id, '_geolocation', true );

		if ( empty( $geolocation ) ) {
			$geolocation = jlt_get_geolocation( $term->slug );

			update_term_meta( $term_id, '_geolocation', $geolocation );
		}
	} else {
		// Support for WordPress version 4.3 and older.
		$jlt_job_geolocation = get_option( 'jlt_job_geolocation' );
		if ( ! empty( $jlt_job_geolocation ) && isset( $jlt_job_geolocation[ $term->slug ] ) ) {
			$geolocation = $jlt_job_geolocation[ $term->slug ];
		} else {
			$geolocation = jlt_get_geolocation( $term->slug );

			$jlt_job_geolocation                = empty( $jlt_job_geolocation ) ? array() : $jlt_job_geolocation;
			$jlt_job_geolocation[ $term->slug ] = $geolocation;

			update_option( 'jlt_job_geolocation', $jlt_job_geolocation );
		}
	}

	return $geolocation;
}

function jlt_job_build_map_data() {
	if ( false !== ( $result = get_transient( 'jlt_transient_job_markers' ) ) ) {
		return $result;
	}

	$args    = array(
		'post_type'   => 'job',
		'nopaging'    => true,
		'post_status' => 'publish',
	);
	$markers = array();
	$r       = new WP_Query( $args );
	if ( $r->have_posts() ):
		while ( $r->have_posts() ):
			$r->the_post();
			global $post;

			$location_address = get_post_meta( $post->ID, '_location_address', true );
			if ( $location_address ) {

				$job_location_geo_data = jlt_location_geo( $location_address );

				$company_logo = '';
				$company_url  = '';
				$company_name = '';
				$company_id   = jlt_get_job_company( $post );
				$type_name    = '';
				$type_url     = '';
				$type_color   = '';

				$type = jlt_get_job_type( $post );
				if ( $type ) {
					$type_name  = $type->name;
					$type_url   = get_term_link( $type, 'job_type' );
					$type_color = $type->color;
				}

				if ( ! empty( $company_id ) ) {
					$company_logo = JLT_Company::get_company_logo( $company_id );
					$company_url  = get_permalink( $company_id );
					$company_name = get_the_title( $company_id );
				}

				$marker    = array(
					'latitude'    => $job_location_geo_data[ 'lat' ],
					'longitude'   => $job_location_geo_data[ 'long' ],
					'address'     => $job_location_geo_data[ 'formatted_address' ],
					'title'       => utf8_encode( htmlentities( get_the_title( $post->ID ) ) ),
					'image'       => $company_logo,
					'type'        => $type_name,
					'type_url'    => $type_url,
					'type_color'  => $type_color,
					'url'         => get_permalink( $post->ID ),
					'company_url' => $company_url,
					'company'     => utf8_encode( htmlentities( $company_name ) ),
					'term'        => 'Example term',
					'term_url'    => '#term_url',
				);
				$markers[] = $marker;
			}
		endwhile;
		wp_reset_query();
	endif;

	$result = json_encode( $markers );
	set_transient( 'jlt_transient_job_markers', $result, DAY_IN_SECONDS );

	return $result;
}

function jlt_remove_transient_job_markers( $post_id ) {
	if ( 'job' == get_post_type( $post_id ) ) {
		delete_transient( 'jlt_transient_job_markers' );
	}
}

add_action( 'save_post', 'jlt_remove_transient_job_markers', 10, 1 );

function jlt_search_job_location( $search_name = '' ) {
	$data = array();
	$args = array(
		'hide_empty' => false,
	);
	if ( ! empty( $search_name ) ) {
		$args[ 'name__like' ] = $search_name;
	}
	$locations = (array) get_terms( 'job_location', $args );
	foreach ( $locations as $location ) {
		$key          = esc_attr( $location->slug );
		$data[ $key ] = $location->name;
	};

	return $data;
}

function jlt_get_job_location( $job_id ) {
	$locations = get_the_terms( $job_id, 'job_location' );
	if ( ! empty( $locations ) && ! is_wp_error( $locations ) ) {

		return $locations;
	}

	return array();
}

function jlt_location_save_address( $job_id ) {

	if ( isset( $_POST[ '_location_address' ] ) ) {

		$full_address[] = sanitize_text_field( $_POST[ '_location_address' ] );
		update_post_meta( $job_id, '_location_address', sanitize_text_field( $_POST[ '_location_address' ] ) );
		update_post_meta( $job_id, '_location_address_full', sanitize_text_field( $_POST[ '_location_address' ] ) );
	}
}

add_action( 'jlt_after_save_job', 'jlt_location_save_address' );
add_action( 'save_post_jlt_job', 'jlt_location_save_address' );

function jlt_location_save_lon_lat( $job_id ) {

	$full_address = get_post_meta( $job_id, '_location_address_full', true );

	if ( ! empty( $full_address ) ) {

		$location = jlt_location_geo( $full_address );
		if ( ! empty( $location ) ) {
			update_post_meta( $job_id, '_location_lat', $location[ 'lat' ] );
			update_post_meta( $job_id, '_location_long', $location[ 'long' ] );
		}
	}
}

add_action( 'save_post_jlt_job', 'jlt_location_save_lon_lat' );

function jlt_location_geo( $full_address ) {
	if ( empty( $full_address ) ) {
		return;
	}

	$geo = file_get_contents( 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode( $full_address ) . '&sensor=false' );
	$geo = json_decode( $geo, true );

	if ( $geo[ 'status' ] == 'OK' ) {

		$location[ 'lat' ]               = $geo[ 'results' ][ 0 ][ 'geometry' ][ 'location' ][ 'lat' ];
		$location[ 'long' ]              = $geo[ 'results' ][ 0 ][ 'geometry' ][ 'location' ][ 'lng' ];
		$location[ 'formatted_address' ] = $geo[ 'results' ][ 0 ][ 'formatted_address' ];

		return $location;
	}

	return '';
}