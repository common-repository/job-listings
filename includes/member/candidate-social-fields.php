<?php

function jlt_candidate_render_social_field( $social = '', $candidate_id = 0 ) {
	$all_socials = jlt_get_social_fields();
	if ( empty( $social ) || ! isset( $all_socials[ $social ] ) ) {
		return;
	}

	$field            = $all_socials[ $social ];
	$field[ 'name' ]  = $social;
	$field[ 'type' ]  = 'text';
	$field[ 'value' ] = $social == 'email' ? 'email@' . $_SERVER[ 'HTTP_HOST' ] : 'http://';
	$field_id         = $field[ 'name' ];

	$value = ! empty( $candidate_id ) ? get_user_meta( $candidate_id, $field_id, true ) : '';
	$value = ! is_array( $value ) ? trim( $value ) : $value;

	$params = apply_filters( 'jlt_candidate_render_social_field_params', compact( 'field', 'field_id', 'value' ), $candidate_id );
	extract( $params );
	$object = array( 'ID' => $candidate_id, 'type' => 'user' );

	$field_id = esc_attr( $field_id );
	?>
	<fieldset class="fieldset <?php jlt_custom_field_class( $field, $object ); ?>">

		<label for="<?php echo esc_attr( $field_id ) ?>">
			<?php echo( isset( $field[ 'label_translated' ] ) ? $field[ 'label_translated' ] : $field[ 'label' ] ) ?>
			<?php echo isset( $field[ 'required' ] ) && $field[ 'required' ] ? '<span class="label-required">' . __( '*', 'job-listings' ) . '</span>' : ''; ?>
		</label>

		<div class="field">
			<?php jlt_render_field( $field, $field_id, $value, '', $object ); ?>
		</div>

	</fieldset>
	<?php
}
