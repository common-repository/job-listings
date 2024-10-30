<?php

function jlt_company_render_social_field( $social = '', $company_id = 0 ) {
	$all_socials = jlt_get_social_fields();
	if ( empty( $social ) || ! isset( $all_socials[ $social ] ) ) {
		return;
	}

	$field            = $all_socials[ $social ];
	$field[ 'name' ]  = '_' . $social;
	$field[ 'type' ]  = 'text';
	$field[ 'value' ] = $social == 'email' ? 'email@' . $_SERVER[ 'HTTP_HOST' ] : 'http://';
	$field_id         = $field[ 'name' ];

	$value = ! empty( $company_id ) ? get_post_meta( $company_id, $field_id, true ) : '';
	$value = ! is_array( $value ) ? trim( $value ) : $value;

	$params = apply_filters( 'jlt_company_render_social_field_params', compact( 'field', 'field_id', 'value' ), $company_id );
	extract( $params );
	$object = array( 'ID' => $company_id, 'type' => 'post' );

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
