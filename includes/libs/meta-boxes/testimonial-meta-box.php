<?php
    if( !function_exists('jlt_testimonial_meta_boxs') ):
        function jlt_testimonial_meta_boxs(){
            // Declare helper object
            $prefix = '_jlt_wp_post';
            $helper = new JLT_Meta_Boxes_Helper($prefix, array(
                'page' => 'testimonial'
            ));
            // Post type: Gallery
            $meta_box = array(
                'id' => "{$prefix}_meta_box_testimonial",
                'title' => __('Testimonial options', 'job-listings'),
                'fields' => array(
                    array(
                        'id' => "{$prefix}_image",
                         'label' => __( 'Your Image', 'job-listings' ),
                        'type' => 'image',
                    ),
                    array(
                        'id' => "{$prefix}_name",
                         'label' => __( 'Your Name', 'job-listings' ),
                        'type' => 'text',
                    ),
                    array(
                        'id' => "{$prefix}_position",
                         'label' => __( 'Your Position', 'job-listings' ),
                        'type' => 'text',
                    ),
                )
            );

            $helper->add_meta_box($meta_box);
        }
        add_action('add_meta_boxes', 'jlt_testimonial_meta_boxs');
    endif;
?>