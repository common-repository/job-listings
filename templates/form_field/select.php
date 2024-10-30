<?php if ($is_multiple_select) : ?>
<select id="<?php echo esc_attr($input_id) ?>" <?php echo $attrs; ?> name="<?php echo esc_attr($field_id); ?>[]"
        multiple="multiple" data-placeholder="<?php echo $placeholder; ?>">
    <?php else : ?>
    <select id="<?php echo esc_attr($input_id) ?>" <?php echo $attrs; ?> name="<?php echo esc_attr($field_id); ?>" ">
        <?php endif; ?>
        <?php
        foreach ($field_value as $key => $label) :
            if (is_array($value)) {
                $selected = in_array($key, $value) ? 'selected="selected"' : '';
            } else {
                $selected = ($key == $value) ? 'selected="selected"' : '';
            }
            $class = !empty($key) ? $key : '';
            ?>
            <option value="<?php echo $key; ?>" <?php echo $selected; ?> class="<?php echo esc_attr($class); ?>"><?php echo esc_html($label); ?></option>
            <?php
        endforeach;
        ?>
    </select>