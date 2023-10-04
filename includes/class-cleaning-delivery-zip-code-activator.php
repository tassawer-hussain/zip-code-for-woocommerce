<?php

/**
 * Fired during plugin activation
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Zip_Code
 * @subpackage Cleaning_Delivery_Zip_Code/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cleaning_Delivery_Zip_Code
 * @subpackage Cleaning_Delivery_Zip_Code/includes
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Zip_Code_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;
        $prefix = $wpdb->get_blog_prefix();
        
        $creation_query =
        'CREATE TABLE IF NOT EXISTS ' . $prefix . 'zipcode_serving (
                `id` int(20) NOT NULL AUTO_INCREMENT,
                `zipcode` int(10) NOT NULL DEFAULT 0,
                `services` text,
                PRIMARY KEY (`id`)
                );'; 
        $tble_creation = $wpdb->query( $creation_query );

        $creation_query =
        'CREATE TABLE IF NOT EXISTS ' . $prefix . 'zipcode_requested (
                `id` int(20) NOT NULL AUTO_INCREMENT,
                `zipcode` int(10) NOT NULL DEFAULT 0,
                `request_count` int(10) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
                );'; 
        $tble_creation = $wpdb->query( $creation_query );

        $creation_query =
        'CREATE TABLE IF NOT EXISTS ' . $prefix . 'zipcode_requested_users (
                `id` int(20) NOT NULL AUTO_INCREMENT,
                `zipcode` int(10) NOT NULL DEFAULT 0,
                `user_email` text,
                PRIMARY KEY (`id`)
                );'; 
        $tble_creation = $wpdb->query( $creation_query );
        
        add_option( 'cdzc_settings', array(
            'not_providing_services' => 'Sorry, we are not providing services in this area at the moment.',
            'user_already_requested' => 'Oops! You have already request for this zip code. We will notify you once we start operating in your area.',
            'thankyou_for_email' => 'Thank you for subscribing for our newsletter. We will notify you once we start operating in your area.',
            'requested_zipcode_highlight' => '50',
        ) );
			
	}

}
