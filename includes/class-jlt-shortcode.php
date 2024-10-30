<?php

class JLT_Shortcode {
	public function __construct() {
		add_shortcode( 'job_submit_form', array( $this, 'job_submit_form_shortcode' ) );
		add_shortcode( 'company_list', array( $this, 'company_listings_shortcode' ) );
		add_shortcode( 'job_category_list', array( $this, 'job_category_listings' ) );
		add_shortcode( 'job_short_item', array( $this, 'job_short_item' ) );
	}

	/**
	 * Shortcode [job_submit_form]
	 *
	 * @since 1.0.0
	 *
	 */
	public function job_submit_form_shortcode() {
		if ( ! isset( $_POST[ 'action' ] ) || empty( $_POST[ 'action' ] ) ) {
			if ( empty( $_GET[ 'action' ] ) ) {
				$GLOBALS[ 'action' ] = '';
			} else {
				$GLOBALS[ 'action' ] = sanitize_text_field( $_GET[ 'action' ] );
			}
		} else {
			$GLOBALS[ 'action' ] = sanitize_text_field( $_POST[ 'action' ] );
		}

		global $action;

		$steps = jlt_get_page_post_job_steps();

		$step_keys = array_keys( $steps );
		if ( ! in_array( $action, $step_keys ) ) {
			$action = $step_keys[ 0 ];
		}

		$next_step = current( array_slice( $step_keys, array_search( $action, $step_keys ) + 1, 1 ) );

		jlt_page_post_job_login_check( $action );

		JLT_Job_Form_Hander::display( $action, $next_step );
	}

	/**
	 * Shortcode [company_list]
	 *
	 * @since 1.0.0
	 *
	 * @param      $atts
	 * @param null $content
	 */
	public function company_listings_shortcode( $atts, $content = null ) {
		$a = new WP_Query();
	}

	/**
	 * Shortcode [job_category_list]
	 *
	 * @since 1.0.0
	 *
	 * @param      $atts
	 * @param null $content
	 */

	public function job_category_listings( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'ids'        => '',
			'orderby'    => 'title',
			'order'      => 'ASC',
			'hide_empty' => '1',
			'number'     => '',
			'parent'     => '',
			'columns'    => '3',
		), $atts );

		ob_start();
		//		Before
		jlt_get_template( 'shortcode/job-category-listings-before.php', $atts );

		$list_cat = explode( ',', $atts[ 'ids' ] );

		$args = array(
			'orderby'    => $atts[ 'orderby' ],
			'order'      => $atts[ 'order' ],
			'hide_empty' => $atts[ 'hide_empty' ],
			'number'     => $atts[ 'number' ],
			'include'    => $list_cat,
			'parent'     => $atts[ 'parent' ],
		);

		$categories = get_terms( 'job_category', $args );

		foreach ( $categories as $key => $cat ) :
			$cate_name    = $cat->name;
			$job_count    = $cat->count;
			$cate_link    = get_term_link( $cat );
			$job_category = array(
				'category_link' => $cate_link,
				'category_name' => $cate_name,
				'job_count'     => $job_count,
			);
			jlt_get_template( 'shortcode/job-category-listings.php', $job_category );

		endforeach;

		// After
		jlt_get_template( 'shortcode/job-category-listings-after.php' );

		return ob_get_clean();
	}

	/**
	 * Shortcode [job_short_item]
	 *
	 * @since 1.0.0
	 *
	 * @param      $atts
	 * @param null $content
	 */

	public function job_short_item( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'id' => '',
		), $atts );

		ob_start();
		jlt_get_template( 'shortcode/job-short-item.php', $atts );

		return ob_get_clean();
	}

}

new JLT_Shortcode();