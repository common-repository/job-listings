<?php

class JLT_Template_Loader {

	/**
	 * JLT_Template_Loader constructor.
	 */
	public function __construct() {

		//		add_filter( 'template_include', array( $this, 'template_loader' ) );

		add_action( 'loop_start', array( $this, 'archive_job_content' ) );
		add_action( 'loop_end', array( $this, 'archive_job_content' ) );
		//
		//		add_action( 'loop_start', array( $this, 'template_single' ) );
		//		add_action( 'loop_end', array( $this, 'template_single' ) );

		add_filter( 'the_content', array( $this, 'template_single_content' ) );

		add_action( 'pre_get_posts', array( $this, 'remove_default_paging' ) );
		add_filter( 'navigation_markup_template', array( $this, 'remove_single_navigation' ) );
	}

	public function remove_default_paging() {

		$job_taxes = jlt_get_job_taxonomies();

		if ( is_post_type_archive( 'job' ) || is_tax( $job_taxes ) || is_post_type_archive( 'resume' ) ) {
			add_filter( 'navigation_markup_template', '__return_empty_string' );
		}
	}

	public function remove_single_navigation() {
		if ( is_singular( 'job' ) || is_singular( 'company' ) ) {
			__return_empty_string();
		}
	}

	public function template_single_content( $content ) {

		if ( is_singular( 'job' ) ) {

			// If we have a single-job.php template, display them.
			if ( '' != locate_template( 'single-job.php' ) ) {
				return $content;
			} else {
				ob_start();
				$this->single_job_content();
				$content = ob_get_clean();
			}
		}
		if ( is_singular( 'company' ) ) {

			// If we have a single-job.php template, display them.
			if ( '' != locate_template( 'single-company.php' ) ) {
				return $content;
			} else {
				ob_start();
				$this->single_company_content();
				$content = ob_get_clean();
			}
		}

		return $content;
	}

	public function template_single( $query ) {

		// Make sure this is a main query

		if ( ! $query->is_main_query() ) {
			return;
		}

		// If we have a single-job.php template, display them.

		// Only on single listing pages

		//		if ( is_singular( 'job' ) ) {
		//
		//			// If we have a single-job.php template, display them.
		//			if ( '' != locate_template( 'single-job.php' ) ) {
		//				return;
		//			}
		//
		//			if ( 'loop_start' === current_filter() ) {
		//				ob_start();
		//			} else {
		//				ob_end_clean();
		//			}
		//			$this->single_job_content();
		//		}

		if ( is_singular( 'company' ) ) {

			// If we have a single-company.php template, display them.
			if ( '' != locate_template( 'single-company.php' ) ) {
				return;
			}

			if ( 'loop_start' === current_filter() ) {
				ob_start();
			} else {
				ob_end_clean();
			}
			$this->single_company_content();
		}
	}

	public function single_job_content() {
		global $post;
		jlt_setup_job_data( $post );

		$can_view = jlt_can_view_job( $post->ID );

		if ( $can_view ) {
			jlt_get_template( 'content-single-job.php' );
		} else {
			jlt_get_template( 'job/cannot-view.php' );
		}
	}

	public function single_company_content() {
		global $post;
		jlt_setup_company_data( $post );
		jlt_get_template( 'content-single-company.php' );
	}

	public function archive_job_content( $query ) {

		if ( ! $query->is_main_query() ) {
			return;
		}

		$job_taxes = jlt_get_job_taxonomies();

		if ( is_post_type_archive( 'job' ) || is_tax( $job_taxes ) ) {

			// If we have a archive-job.php template, display them.
			if ( is_post_type_archive( 'job' ) && '' != locate_template( 'archive-job.php' ) ) {
				return;
			}
			if ( is_tax( 'job_category' ) && '' != locate_template( 'taxonomy-job_category.php' ) ) {
				return;
			}
			if ( is_tax( 'job_location' ) && '' != locate_template( 'taxonomy-job_location.php' ) ) {
				return;
			}
			if ( is_tax( 'job_type' ) && '' != locate_template( 'taxonomy-job_type.php' ) ) {
				return;
			}
			if ( is_tax( 'job_tag' ) && '' != locate_template( 'taxonomy-job_tag.php' ) ) {
				return;
			}

			global $job_query;

			if ( 'loop_start' === current_filter() ) {
				ob_start();
			} else {
				ob_end_clean();
			}

			$job_query = jlt_job_listings( $query );

			jlt_get_template( 'job/loop/loop-before.php', compact( 'job_query' ) );

			if ( $job_query->have_posts() ) {

				while ( $job_query->have_posts() ) {

					// Setup listing data
					$job_query->the_post();

					global $post;
					jlt_setup_job_data( $post );
					jlt_get_template( 'content-job.php' );
				}
			} else {
				jlt_get_template( 'job/loop/not-founds.php' );
			}

			jlt_get_template( 'job/loop/loop-after.php', compact( 'job_query' ) );

			wp_reset_query();

		} elseif ( is_post_type_archive( 'company' ) ) {

			if ( 'loop_start' === current_filter() ) {
				ob_start();
			} else {
				ob_end_clean();
			}

			$company_query = jlt_company_listings( $query );

			jlt_get_template( 'employer/loop/loop-before.php', compact( 'company_query' ) );

			if ( $company_query->have_posts() ) {

				while ( $company_query->have_posts() ) {

					// Setup listing data
					$company_query->the_post();

					global $post;

					jlt_setup_company_data( $post );
					jlt_get_template( 'content-company.php' );
				}
			}

			jlt_get_template( 'employer/loop/loop-after.php', compact( 'company_query' ) );

			wp_reset_query();
		}
	}

	public static function template_path() {
		$template_path = 'job-listings/';

		return apply_filters( 'jlt_template_path', $template_path );
	}

	public static function template_loader( $template ) {
		$find = array();
		$file = '';

		if ( $file ) {
			$template = locate_template( array_unique( $find ) );
			if ( ! $template ) {
				$template = JLT_PLUGIN_DIR . '/templates/' . $file;
			}
		}

		return $template;
	}
}

new JLT_Template_Loader();