<?php

/**
 * Project: job-listings - class-jlt-job-factory.php
 * Author: Edgar
 * Website: nootheme.com
 */
class JLT_Job_Factory {

	/**
	 * The job (post) ID.
	 *
	 * @var int
	 */
	public $id = 0;

	/**
	 * $post Stores post data.
	 *
	 * @var $post WP_Post
	 */
	public $post = null;

	public function __construct( $job ) {
		if ( is_numeric( $job ) ) {
			$this->id   = absint( $job );
			$this->post = get_post( $this->id );
		} elseif ( $job instanceof JLT_Job_Factory ) {
			$this->id   = absint( $job->id );
			$this->post = $job->post;
		} elseif ( isset( $job->ID ) ) {
			$this->id   = absint( $job->ID );
			$this->post = $job;
		}
		$this->company_id   = $this->company_id();
		$this->company_logo = $this->company_logo();
		$this->company_name = apply_filters( 'the_title', get_the_title( $this->company_id ) );
		$this->company_url  = get_the_permalink( $this->company_id );
	}

	public function get_id() {
		return $this->id;
	}

	public function company_id() {
		$company_id = jlt_get_job_company( $this->get_id() );

		return apply_filters( 'jlt_get_company_id', $company_id );
	}

	public function company_logo( $size = 80 ) {
		$size = apply_filters( 'jlt_company_logo_size', $size );

		return JLT_Company::get_company_logo( $this->company_id(), $size );
	}

	public function content() {
		return apply_filters( 'jlt_job_content', get_the_content() );
	}

	public function info() {
		$fields = jlt_get_job_custom_fields();
		$html   = array();
		if ( ! empty( $fields ) ) {

			foreach ( $fields as $field ) {
				// if( isset( $field['is_tax'] ) )
				// 	continue;
				if ( $field[ 'name' ] == '_closing' ) // reserve the _closing field
				{
					continue;
				}
				if ( $field[ 'name' ] == '_location_address' ) // reserve the _closing field
				{
					continue;
				}

				$id = jlt_job_custom_fields_name( $field[ 'name' ], $field );
				if ( isset( $field[ 'is_tax' ] ) ) {
					$value = jlt_job_get_tax_value();
					$value = implode( ',', $value );
				} else {
					$value = jlt_get_post_meta( get_the_ID(), $id, '' );
				}

				if ( ! empty( $value ) ) {
					$html[] = array(
						'field' => $field,
						'id'    => $id,
						'value' => $value,
					);
				}
			}
		}

		return $html;
	}

	public function get_tag() {
		$tags = get_the_terms( $this->get_id(), 'job_tag' );
		if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
			return apply_filters( 'jlt_job_get_tag', $tags );
		} else {
			return array();
		}
	}

	public function get_tag_html( $prefix = '', $sep = ', ' ) {
		$tags = $this->get_tag();
		if ( ! empty( $tags ) ) {
			$prefix   = apply_filters( 'jlt_job_tag_html_prefix', $prefix );
			$sep      = apply_filters( 'jlt_job_tag_html_sep', $sep );
			$tag_html = '';
			$html     = '';
			foreach ( $tags as $tag ) {
				$tag_html .= '<a href="' . get_term_link( $tag->term_id, 'job_tag' ) . '" title="' . esc_attr( sprintf( __( "View all jobs in: &ldquo;%s&rdquo;", 'job-listings' ), $tag->name ) ) . '">' . ' ' . $tag->name . '</a>' . $sep;
			}
			$html[] = '<div class="jlt-tags job-tags entry-tags">' . apply_filters( 'jlt_job_tag_html_prefix', $prefix );
			$html[] = trim( $tag_html, $sep );
			$html[] = '</div>';

			return apply_filters( 'jlt_job_tag_html', implode( $html, "\n" ) );
		}
	}

	public function get_category() {
		$categories = get_the_terms( $this->get_id(), 'job_category' );
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			return $categories;
		}

		return array();
	}

	public function get_category_html( $prefix = '', $sep = ', ' ) {
		$cats = $this->get_category();
		if ( ! empty( $cats ) ) {
			$prefix   = apply_filters( 'jlt_job_category_html_prefix', $prefix );
			$sep      = apply_filters( 'jlt_job_category_html_sep', $sep );
			$cat_html = '';
			$html     = '';
			foreach ( $cats as $cat ) {
				$cat_html .= '<a href="' . get_term_link( $cat->term_id, 'job_tag' ) . '" title="' . esc_attr( sprintf( __( "View all jobs in: &ldquo;%s&rdquo;", 'job-listings' ), $cat->name ) ) . '">' . ' ' . $cat->name . '</a>' . $sep;
			}
			$html[] = '<div class="jlt-tags job-categories">' . $prefix;
			$html[] = trim( $cat_html, $sep );
			$html[] = '</div>';

			return apply_filters( 'jlt_job_category_html', implode( $html, "\n" ) );
		}
	}

	public function get_type() {
		$types = get_the_terms( $this->get_id(), 'job_type' );
		if ( ! empty( $types ) && ! is_wp_error( $types ) ) {
			return $types;
		}

		return array();
	}

	public function get_type_html( $prefix = '' ) {
		$type = $this->get_type();
		if ( ! empty( $type ) ) {
			$type   = $type[ 0 ];
			$prefix = apply_filters( 'jlt_job_type_html_prefix', $prefix );
			$html   = array();
			$html[] = '<div class="jlt-tags jlt-job-type">' . $prefix;
			$html[] = '<a href="' . get_term_link( $type->term_id, 'job_type' ) . '" title="' . esc_attr( sprintf( __( "View all jobs in: &ldquo;%s&rdquo;", 'job-listings' ), $type->name ) ) . '">' . ' ' . $type->name . '</a>';
			$html[] = '</div>';

			return apply_filters( 'jlt_job_type_html', implode( $html, "\n" ) );
		}
	}

	public function get_location() {
		$types = get_the_terms( $this->get_id(), 'job_location' );
		if ( ! empty( $types ) && ! is_wp_error( $types ) ) {
			return $types;
		}

		return array();
	}

	public function get_address_html() {
		$address = get_post_meta( $this->id, '_location_address', true );
		if ( ! empty( $address ) ) {
			$html   = '';
			$html[] = '<div class="jlt-tags job-address">' . __( 'Complete Address:', 'job-listings' );
			$html[] = $address;
			$html[] = '</div>';

			return apply_filters( 'jlt_job_address_html', implode( $html, "\n" ) );
		}
	}

	public function get_location_html( $prefix = '', $sep = ', ' ) {

		$locations = $this->get_location();

		if ( ! empty( $locations ) ) {

			$html = '';

			$prefix   = apply_filters( 'jlt_job_location_html_prefix', $prefix );
			$sep      = apply_filters( 'jlt_job_location_html_sep', $sep );
			$cat_html = '';

			foreach ( $locations as $cat ) {
				$cat_html .= '<a href="' . get_term_link( $cat->term_id, 'job_location' ) . '" title="' . esc_attr( sprintf( __( "View all jobs in: &ldquo;%s&rdquo;", 'job-listings' ), $cat->name ) ) . '">' . ' ' . $cat->name . '</a>' . $sep;
			}
			$html[] = '<div class="jlt-tags job-location">' . $prefix;
			$html[] = trim( $cat_html, $sep );
			$html[] = '</div>';

			return apply_filters( 'jlt_job_location_html', implode( $html, "\n" ) );
		}
	}

	public function has_applied() {
		return JLT_Member::is_candidate() ? JLT_Application::has_applied( 0, $this->get_id() ) : false;
	}

	public function can_apply() {
		return jlt_can_apply_job( $this->get_id() );
	}

	public function applications_count() {
		$applications = get_posts( array(
			'post_type'        => 'application',
			'posts_per_page'   => - 1,
			'post_parent'      => $this->get_id(),
			'post_status'      => array( 'publish', 'pending', 'rejected' ),
			'suppress_filters' => false,
		) );

		return absint( count( $applications ) );
	}

	public function closing() {
		$closing = jlt_get_post_meta( $this->get_id(), '_closing' );
		$closing = ! is_numeric( $closing ) ? strtotime( $closing ) : $closing;
		$closing = ! empty( $closing ) ? date_i18n( get_option( 'date_format' ), $closing ) : '';

		return $closing;
	}

	public function notice_email() {
		$notify_email = get_post_meta( $this->get_id(), '_application_email', true );

		return $notify_email;
	}

	public function featured() {
		$featured = jlt_get_post_meta( $this->get_id(), '_featured' );
		if ( empty( $featured ) ) {
			update_post_meta( $this->get_id(), '_featured', 'no' );
		}

		return $featured;
	}

}

if ( ! function_exists( 'jlt_get_job' ) ) :
	function jlt_get_job( $job ) {
		return new JLT_Job_Factory( $job );
	}
endif;