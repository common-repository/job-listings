<?php

/**
 * Project: job-listings - class-jlt-resume-factory.php
 * Author: Edgar
 * Website: nootheme.com
 */
class JLT_Company_Factory {

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

	public function __construct( $company ) {
		if ( is_numeric( $company ) ) {
			$this->id   = absint( $company );
			$this->post = get_post( $this->id );
		} elseif ( $company instanceof JLT_Company_Factory ) {
			$this->id   = absint( $company->id );
			$this->post = $company->post;
		} elseif ( isset( $company->ID ) ) {
			$this->id   = absint( $company->ID );
			$this->post = $company;
		}
	}

	public function get_id() {
		return $this->id;
	}

	public function logo($size = 80) {
		return JLT_Company::get_company_logo( $this->get_id(), $size );
	}

	public function job_count() {
		return JLT_Company::count_jobs( $this->get_id() );
	}

	public function social() {
		$all_socials = jlt_get_social_fields();
		$socials     = jlt_get_company_socials();
		$html        = array();
		foreach ( $socials as $social ) {
			if ( ! isset( $all_socials[ $social ] ) ) {
				continue;
			}
			$data  = $all_socials[ $social ];
			$value = get_post_meta( $this->get_id(), "_{$social}", true );
			if ( ! empty( $value ) ) {
				$url = $social == 'email' ? 'mailto:' . $value : esc_url( $value );

				$html[] = array(
					'label' => $data[ 'label' ],
					'icon'  => $data[ 'icon' ],
					'link'  => $url,
				);
			}
		}

		return $html;
	}

	public function jobs() {
		return JLT_Company::get_company_jobs( $this->get_id(), array(), - 1 );
	}

	public function info() {
		$fields = jlt_get_company_custom_fields();
		$html   = array();

		foreach ( $fields as $field ) {
			if ( $field[ 'name' ] == '_logo' || $field[ 'name' ] == '_cover_image' ) {
				continue;
			}

			$id = jlt_company_custom_fields_name( $field[ 'name' ], $field );

			$value = jlt_get_post_meta( $this->get_id(), $id, '' );
			
			if ( ! empty( $value ) ) {
				$html[] = array(
					'field' => $field,
					'id'    => $id,
					'value' => $value,
				);
			}
		}

		return $html;
	}

}

if ( ! function_exists( 'jlt_get_company' ) ) :
	function jlt_get_company( $company ) {
		return new JLT_Company_Factory( $company );
	}
endif;