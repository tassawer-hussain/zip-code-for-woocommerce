<?php 

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( ! class_exists( 'CDZC_Shortcode' )) {
    class CDZC_Shortcode {

		public function __construct() {
			add_shortcode('cd_zip_code', array($this, 'cd_zipcode_input_field'));
		}

		public function cd_zipcode_input_field() {

			$html = '';
			$html .= '<div class=""';


		}


	}
}