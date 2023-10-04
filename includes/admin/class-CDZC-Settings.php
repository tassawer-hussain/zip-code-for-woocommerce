<?php 

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if( ! class_exists( 'CDZC_Settings' )) {
    class CDZC_Settings {

        private $options;

        public function __construct() {
            $this->options = get_option( 'cdzc_settings' );
            add_action( 'admin_init', array($this, 'cdzc_settings') );
            
        }

        public function cdzc_settings() {
            // Register a setting group with a validation function
            // so that post data handling is done automatically for us
            register_setting( 
                'ch3sapi_settings', //$option_group, UNIQUE NAME
                'ch3sapi_options', //$option_name, SAME AS IN DATABASE
                array( $this, 'ch3sapi_validate_options' )); //$args, CALL BACK VALIDATING FUNC 

            // Add a new settings section within the group
            add_settings_section( 
                'ch3sapi_main_section', //$id, unique name
                'Main Settings', //$title
                array( $this, 'ch3sapi_main_setting_section_callback' ),//$callback
                'cd-zip-code-settings' );//$page

            // Add each field with its name and function to use for
            // our new settings, put them in our new section
            add_settings_field( // for display textarea
                'not_providing_services',//$id
                'Text Area/Box', //$title
                array( $this, 'ch3sapi_display_text_field' ), //$callback
                'cd-zip-code-settings',//$page
                'ch3sapi_main_section', //$section
                array( 'name' => 'not_providing_services' ) );//$args

            // add_settings_field( // for display textarea
            //     'user_already_requested',//$id
            //     'Text Area/Box', //$title
            //     array( $this, 'ch3sapi_display_text_area' ), //$callback
            //     'ch3sapi_settings_section',//$page
            //     'ch3sapi_main_section', //$section
            //     array( 'name' => 'user_already_requested' ) );//$args

            // add_settings_field( // for display textarea
            //     'thankyou_for_email',//$id
            //     'Text Area/Box', //$title
            //     array( $this, 'ch3sapi_display_text_area'), //$callback
            //     'ch3sapi_settings_section',//$page
            //     'ch3sapi_main_section', //$section
            //     array( 'name' => 'thankyou_for_email' ) );//$args
            
            // add_settings_field( 
            //         'ga_account_name', //$id
            //         'Account Name', //$title, a label that will be display next to the field
            //         array( $this, 'ch3sapi_display_text_field'), //$callback
            //         'ch3sapi_settings_section', //$page
            //         'ch3sapi_main_section', //$section
            //         array( 'name' => 'ga_account_name' ) ); //$args
            add_settings_field( 
                    'track_outgoing_links',//$id
                    'Track Outgoing Links', //$title
                    array( $this, 'ch3sapi_display_check_box'), //$callback
                    'cd-zip-code-settings',//$page
                    'ch3sapi_main_section', //$section
                    array( 'name' => 'track_outgoing_links' ) );
        }

        public function ch3sapi_validate_options( $input ) {
            $input['version'] = VERSION;
            return $input;
        }

        // Declare a body for the ch3sapi_main_setting_section_callback function
        public function ch3sapi_main_setting_section_callback() { ?>
            <p>This is the main configuration section.</p>
        <?php }

        public function ch3sapi_display_text_area( $data = array() ) {
            extract ( $data );
            $options = get_option( 'cdzc_settings' );  ?>
            <textarea type="text"
                    name="ch3sapi_options[<?php echo $name; ?>]"  
                    rows="5" cols="30">
                <?php if(isset($options[$name])) { echo esc_html($options[$name]); } ?></textarea>
        <?php }

        // Provide an implementation for the ch3sapi_display_text_field function
        public function ch3sapi_display_text_field( $data = array() ) {
            extract( $data );
            $options = get_option( 'ch3sapi_options' ); ?>
            <input type="text" 
                name="ch3sapi_options[<?php echo $name; ?>]"
                value="<?php echo esc_html( $options[$name] ); ?>"/><br />
        <?php }

        // Declare and define the ch3sapi_display_check_box function
        public function ch3sapi_display_check_box( $data = array() ) {
            extract ( $data );
            $options = get_option( 'ch3sapi_options' ); ?>
            <input type="checkbox" 
                name="ch3sapi_options[<?php echo $name;  ?>]" 
                <?php if ( isset( $options[$name] ) && $options[$name] ) echo ' checked="checked"';?>/>
        <?php }

        public function cdzc_settings_admin_menu() {
            // Create a sub-menu under the top-level menu
            add_submenu_page( 
                'cd-zip-code', // $parent_slug
                'CD Zip Code Settings', //$page_title
                'Settings', //$menu_title
                'manage_options', //$capability
                'cd-zip-code-settings', //$menu_slug
                array($this, 'cd_zip_code_settings_callback' ) ); //$function
        }

        public function cd_zip_code_settings_callback() { ?>
            <div id="ch3sapi-general" class="wrap">
                <h2>My Google Analytics – Settings API</h2>
                <form name="ch3sapi_options_form_settings_api" method="post" action="options.php">
                    <?php //settings_fields($option_group); ?>
                    <?php // $option_group of register setting func
                    settings_fields( 'ch3sapi_settings' ); ?>
                    <?php //do_settings_sections($page); ?>
                    <?php // $page of add_settings_section func
                    do_settings_sections( 'cd-zip-code-settings' ); ?> 
                    <input type="submit" value="Submit" class="button-primary" />
                </form>
            </div>
        <?php }

    }

    $cszp_settings = new CDZC_Settings();
    $cszp_settings->cd_zip_code_settings_callback();
}