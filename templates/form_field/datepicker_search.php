<input class="jlt-form-control jlt-form-datepicker-search jlt-form-datepicker-start" type="text"
       value="<?php echo $_start_date; ?>" name="<?php echo esc_attr( $field_id ) . '_start'; ?>"
       placeholder="<?php echo __( 'Start Date', 'job-listings' ); ?>"/>
<input class="jlt-form-control jlt-form-datepicker-search jlt-form-datepicker-end" type="text"
       value="<?php echo $_end_date; ?>"
       name="<?php echo esc_attr( $field_id ) . '_end'; ?>" placeholder="<?php echo __( 'End Date', 'job-listings' ); ?>"/>