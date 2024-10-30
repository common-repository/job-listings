<?php

function jlt_is_enabled_job_package_cf() {
	$enabled = (bool) jlt_get_job_custom_fields_option( 'job_package', '0' );

	return jlt_is_woo_job_posting() && $enabled;
}

function jlt_get_job_package_cf( $package_id = null ) {
	if ( ! jlt_is_enabled_job_package_cf() ) {
		return array();
	}

	if ( ! empty( $package_id ) ) {
		$package_cf = get_post_meta( $package_id, '_job_package_cf', true );
		$package_cf = ! is_array( $package_cf ) ? array( $package_cf ) : $package_cf;
		if ( empty( $package_cf ) || ( 1 == count( $package_cf ) && empty( $package_cf[ 0 ] ) ) ) {
			return array();
		} elseif ( in_array( 'all', $package_cf ) ) {
			return jlt_get_job_custom_fields_option( 'job_package_fields', array() );
		} elseif ( is_array( $package_cf ) ) {
			return $package_cf;
		}

		return array();
	}

	return jlt_get_job_custom_fields_option( 'job_package_fields', array() );
}

function jlt_job_package_cf_data() {

	global $post;

	$job_package_cfs = jlt_get_job_custom_fields_option( 'job_package_fields', array() );

	if ( ! empty( $job_package_cfs ) ) {
		$job_custom_fields = jlt_get_job_custom_fields();
		$selected_fields   = array(
			''    => __( 'None', 'job-listings' ),
			'all' => __( 'All fields', 'job-listings' ),
		);
		foreach ( $job_custom_fields as $field ) {
			if ( in_array( $field[ 'name' ], $job_package_cfs ) ) {
				$field_label                         = ( isset( $field[ 'label_translated' ] ) && ! empty( $field[ 'label_translated' ] ) ) ? $field[ 'label_translated' ] : $field[ 'label' ];
				$selected_fields[ $field[ 'name' ] ] = $field_label;
			}
		}

		jlt_wc_wp_select_multiple( array(
			'id'          => '_job_package_cf',
			'label'       => __( 'Job Custom Fields', 'job-listings' ),
			'description' => __( 'Choose fields that come with this package', 'job-listings' ),
			'options'     => $selected_fields,
			'desc_tip'    => true,
		) );
	}
}

add_action( 'jlt_job_package_data', 'jlt_job_package_cf_data' );

function jlt_job_package_cf_save_data( $post_id ) {
	// Save meta
	if ( isset( $_POST[ '_job_package_cf' ] ) ) {
		update_post_meta( $post_id, '_job_package_cf', esc_html( $_POST[ '_job_package_cf' ] ) );
	}
}

add_action( 'jlt_job_package_save_data', 'jlt_job_package_cf_save_data' );

function jlt_job_package_cf_job_form( $custom_fields ) {
	if ( ! jlt_is_enabled_job_package_cf() ) {
		return $custom_fields;
	}

	// Only work on the member page or job posting page
	if ( ! jlt_is_job_posting_page() && ( get_the_ID() != JLT_Member::get_member_page_id() ) ) {
		return $custom_fields;
	}

	$all_job_package_cfs = jlt_get_job_custom_fields_option( 'job_package_fields', array() );
	$package             = jlt_get_job_posting_info();
	$package_id          = isset( $package[ 'product_id' ] ) ? absint( $package[ 'product_id' ] ) : 0;

	if ( empty( $package_id ) && jlt_is_job_posting_page() && isset( $_REQUEST[ 'package_id' ] ) ) {
		$package_id = intval( $_REQUEST[ 'package_id' ] );
	}

	$package_cfs = ! empty( $package_id ) ? jlt_get_job_package_cf( $package_id ) : array();

	$remove_fields = array_diff( $all_job_package_cfs, $package_cfs );
	if ( ! empty( $remove_fields ) ) {
		foreach ( $custom_fields as $index => $field ) {
			if ( in_array( $field[ 'name' ], $remove_fields ) ) {
				unset( $custom_fields[ $index ] );
			}
		}
	}

	return $custom_fields;
}

add_filter( 'jlt_job_custom_fields', 'jlt_job_package_cf_job_form' );

function jlt_job_package_cf_features( $product ) {
	if ( ! jlt_is_enabled_job_package_cf() ) {
		return;
	}
	$all_job_package_cfs = jlt_get_job_custom_fields_option( 'job_package_fields', array() );
	$package_cfs         = jlt_get_job_package_cf( $product->id );

	foreach ( $all_job_package_cfs as $field_name ) :
		$field = jlt_get_job_field( $field_name );
		if ( empty( $field ) ) {
			continue;
		}

		$field_label = ( isset( $field[ 'label_translated' ] ) && ! empty( $field[ 'label_translated' ] ) ) ? $field[ 'label_translated' ] : $field[ 'label' ];
		if ( in_array( $field_name, $package_cfs ) ) : ?>
			<li class="jlt-li-icon"><i
					class="fa fa-check-circle"></i> <?php echo sprintf( __( 'Post job with %s', 'job-listings' ), $field_label ); ?>
			</li>
		<?php else : ?>
			<li class="jlt-li-icon"><i
					class="fa fa-times-circle-o not-good"></i> <?php echo sprintf( __( 'No %s for Jobs', 'job-listings' ), $field_label ); ?>
			</li>
		<?php endif; ?>
	<?php endforeach;
}

add_action( 'jlt_job_package_features_list', 'jlt_job_package_cf_features' );

function jlt_employer_manage_plan_cf_features( $package ) {
	if ( ! JLT_Member::is_employer() || ! jlt_is_enabled_job_package_cf() || ! isset( $package[ 'product_id' ] ) ) {
		return;
	}
	$all_job_package_cfs = jlt_get_job_custom_fields_option( 'job_package_fields', array() );
	$package_cfs         = jlt_get_job_package_cf( $package[ 'product_id' ] );

	foreach ( $all_job_package_cfs as $field_name ) :
		$field = jlt_get_job_field( $field_name );
		if ( empty( $field ) ) {
			continue;
		}

		$field_label = ( isset( $field[ 'label_translated' ] ) && ! empty( $field[ 'label_translated' ] ) ) ? $field[ 'label_translated' ] : $field[ 'label' ];
		$icon        = in_array( $field_name, $package_cfs ) ? 'fa-check-circle' : 'fa-times-circle-o not-good';
		?>
		<div class="col-xs-6">
			<strong><?php echo sprintf( __( 'Post with %s', 'job-listings' ), $field_label ); ?></strong></div>
		<div
			class="col-xs-6"><?php echo in_array( $field_name, $package_cfs ) ? __( 'Yes', 'job-listings' ) : __( 'No', 'job-listings' ); ?></div>
	<?php endforeach;
}

add_action( 'jlt_manage_plan_features_list', 'jlt_employer_manage_plan_cf_features' );

function jlt_job_package_cf_options() {
	$job_package        = jlt_get_job_custom_fields_option( 'job_package', '0' );
	$job_package_fields = jlt_get_job_custom_fields_option( 'job_package_fields', array() );
	?>
	<table class="form-table" cellspacing="0">
		<tbody>
		<tr>
			<th>
				<?php _e( 'Integrate Custom Field with Job Package', 'job-listings' ) ?>
			</th>
			<td>
				<input type="hidden" name="jlt_job_custom_field[__options__][job_package]" value="0"/>
				<input type="checkbox" name="jlt_job_custom_field[__options__][job_package]"
				       value="1" <?php checked( $job_package ); ?> id="job-package-cf-enabled"/><br/>
				<em><?php echo __( 'Enable this function and you can decide which fields employer can use with each Job Package', 'job-listings' ); ?></em>
			</td>
		</tr>
		<tr class="job-package-cf">
			<th>
				<?php _e( 'Fields to add to Job Package', 'job-listings' ); ?>
			</th>
			<td>
				<?php $custom_fields = jlt_get_job_custom_fields(); ?>
				<select class="jlt-admin-chosen" name="jlt_job_custom_field[__options__][job_package_fields][]"
				        multiple="multiple" style="width: 500px;max-width: 100%;">
					<?php foreach ( $custom_fields as $key => $field ) : ?>
						<option <?php selected( in_array( $field[ 'name' ], $job_package_fields ), true ); ?>
							value="<?php echo $field[ 'name' ]; ?>"><?php echo $field[ 'label' ]; ?></option>
					<?php endforeach; ?>
				</select>
				<br/><em><?php echo __( 'Fields selected here will required buying specific Job Package. Please continue edit on Job Package Products.', 'job-listings' ); ?></em>
			</td>
		</tr>
		</tbody>
	</table>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			$('#job-package-cf-enabled').change(function (event) {
				if ($(this).is(':checked')) {
					$('.job-package-cf').show();
				} else {
					$('.job-package-cf').hide();
				}
			});

			$('#job-package-cf-enabled').change();
		});
	</script>
	<?php
}
