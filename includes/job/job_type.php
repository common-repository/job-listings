<?php

function jlt_job_type_add_color() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	?>
	<div class="form-field">
		<label><?php _e( 'Color', 'job-listings' ); ?></label>
		<input id="jlt_job_type_color" type="text" size="40" value="" name="jlt_job_type_color">
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$("#jlt_job_type_color").wpColorPicker();
			});
		</script>
	</div>
	<?php
}

add_action( 'job_type_add_form_fields', 'jlt_job_type_add_color' );

function jlt_get_job_type_color( $term = null ) {
	if ( empty( $term ) ) {
		return false;
	}

	$term_id = is_object( $term ) ? $term->term_id : is_numeric( $term ) ? $term : 0;
	if ( empty( $term_id ) ) {
		return '';
	}

	$color = '';
	if ( function_exists( 'get_term_meta' ) ) {
		$color = get_term_meta( $term_id, '_color', true );

		// try getting the color from the option table ( legacy )
		if ( empty( $color ) ) {
			$type_colors = get_option( 'jlt_job_type_colors' );
			$color       = isset( $type_colors[ $term_id ] ) ? $type_colors[ $term_id ] : '';

			if ( ! empty( $color ) ) {
				update_term_meta( $term_id, '_color', $color );

				unset( $type_colors[ $term_id ] );
				update_option( 'jlt_job_type_colors', $type_colors );
			}
		}
	} else {
		// Support for WordPress version 4.3 and older.
		$term = is_object( $term ) ? $term : get_term( $term_id, 'job_type' );

		$type_colors = get_option( 'jlt_job_type_colors' );
		$color       = isset( $type_colors[ $term_id ] ) ? $type_colors[ $term_id ] : '';
	}

	return $color;
}

function jlt_job_type_edit_color( $term, $taxonomy ) {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	$color = jlt_get_job_type_color( $term );
	?>
	<tr class="form-field">
		<th scope="row" valign="top"><label><?php _e( 'Color', 'job-listings' ); ?></label></th>
		<td>
			<input id="jlt_job_type_color" type="text" size="40" value="<?php echo esc_attr( $color ); ?>"
			       name="jlt_job_type_color">
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$("#jlt_job_type_color").wpColorPicker();
				});
			</script>
		</td>
	</tr>
	<?php
}

add_action( 'job_type_edit_form_fields', 'jlt_job_type_edit_color', 10, 3 );

function jlt_job_type_save_color( $term_id, $tt_id, $taxonomy ) {
	if ( isset( $_POST[ 'jlt_job_type_color' ] ) ) {
		if ( function_exists( 'update_term_meta' ) ) {
			update_term_meta( $term_id, '_color', esc_html( $_POST[ 'jlt_job_type_color' ] ) );
		} else {
			// Support for WordPress version 4.3 and older.
			$type_colors = get_option( 'jlt_job_type_colors' );
			if ( ! $type_colors ) {
				$type_colors = array();
			}

			$type_colors[ $term_id ] = sanitize_text_field( $_POST[ 'jlt_job_type_color' ] );
			update_option( 'jlt_job_type_colors', $type_colors );
		}
	}
}

add_action( 'created_term', 'jlt_job_type_save_color', 10, 3 );
add_action( 'edit_term', 'jlt_job_type_save_color', 10, 3 );
