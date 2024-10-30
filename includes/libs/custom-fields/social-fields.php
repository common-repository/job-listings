<?php
if ( ! function_exists( 'jlt_get_social_fields' ) ) :
	function jlt_get_social_fields() {
		$social_fields = array(
			'website'       => array(
				'label' => __( 'Website', 'job-listings' ),
				'icon'  => 'jltfa-link',
			),
			'facebook'      => array(
				'label'       => __( 'Facebook', 'job-listings' ),
				'icon'        => 'jltfa-facebook',
				'icon_square' => 'jltfa-facebook-square',
				'alt_icon'    => 'jltfa-facebook-f',
			),
			'twitter'       => array(
				'label'       => __( 'Twitter', 'job-listings' ),
				'icon'        => 'jltfa-twitter',
				'icon_square' => 'jltfa-twitter-square',
			),
			'instagram'     => array(
				'label' => __( 'Instagram', 'job-listings' ),
				'icon'  => 'jltfa-instagram',
			),
			'googleplus'    => array( // Be careful with google plus field name
				'label'       => __( 'Google+', 'job-listings' ),
				'icon'        => 'jltfa-google-plus',
				'icon_square' => 'jltfa-google-plus-square',
			),
			'linkedin'      => array(
				'label'       => __( 'LinkedIn', 'job-listings' ),
				'icon'        => 'jltfa-linkedin',
				'icon_square' => 'jltfa-linkedin-square',
			),
			'email_address' => array(
				'label'    => __( 'Email', 'job-listings' ),
				'icon'     => 'jltfa-envelope-o',
				'alt_icon' => 'jltfa-envelope',
			),
			'pinterest'     => array(
				'label'       => __( 'Pinterest', 'job-listings' ),
				'icon'        => 'jltfa-pinterest',
				'icon_square' => 'jltfa-pinterest-square',
				'alt_icon'    => 'jltfa-pinterest-p',
			),
			'youtube'       => array(
				'label'       => __( 'Youtube', 'job-listings' ),
				'icon'        => 'jltfa-youtube',
				'icon_square' => 'jltfa-youtube-square',
				'alt_icon'    => 'jltfa-youtube-play',
			),
			'tumblr'        => array(
				'label'       => __( 'Tumblr', 'job-listings' ),
				'icon'        => 'jltfa-tumblr',
				'icon_square' => 'jltfa-tumblr-square',
			),
			'behance'       => array(
				'label'       => __( 'Behance', 'job-listings' ),
				'icon'        => 'jltfa-behance',
				'icon_square' => 'jltfa-behance-square',
			),
			'flickr'        => array(
				'label' => __( 'Flickr', 'job-listings' ),
				'icon'  => 'jltfa-flickr',
			),
			'vimeo'         => array(
				'label'       => __( 'Vimeo', 'job-listings' ),
				'icon'        => 'jltfa-vimeo',
				'icon_square' => 'jltfa-vimeo-square',
			),
			'github'        => array(
				'label'       => __( 'GitHub', 'job-listings' ),
				'icon'        => 'jltfa-github',
				'icon_square' => 'jltfa-github-square',
				'alt_icon'    => 'jltfa-github-alt',
			),
			'vk'            => array(
				'label' => __( 'VKontakte', 'job-listings' ),
				'icon'  => 'jltfa-vk',
			),
		);

		return apply_filters( 'jlt_social_fields', $social_fields );
	}
endif;
