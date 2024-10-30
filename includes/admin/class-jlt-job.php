<?php
if ( ! class_exists( 'JLT_Job' ) ) :
	class JLT_Job {

		protected static $_instance = null;

		/**
		 * JLT_Job constructor.
		 */
		public function __construct() {
		}

	}

	new JLT_Job();
endif;