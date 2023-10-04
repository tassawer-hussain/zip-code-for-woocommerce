<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tassawer.com/
 * @since             1.0.0
 * @package           Cleaning_Delivery_Zip_Code
 *
 * @wordpress-plugin
 * Plugin Name:       Cleaning Delivery Zip Code
 * Plugin URI:        http://www.cleaningdelivery.com/
 * Description:       Manage zip codes where currently providing services and keep a record of requestng zipcodes.
 * Version:           1.0.0
 * Author:            Tassawer
 * Author URI:        https://tassawer.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cleaning-delivery-zip-code
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CLEANING_DELIVERY_ZIP_CODE_VERSION', '1.0.0' );

define( 'CDZC_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cleaning-delivery-zip-code-activator.php
 */
function activate_cleaning_delivery_zip_code() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cleaning-delivery-zip-code-activator.php';
	Cleaning_Delivery_Zip_Code_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cleaning-delivery-zip-code-deactivator.php
 */
function deactivate_cleaning_delivery_zip_code() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cleaning-delivery-zip-code-deactivator.php';
	Cleaning_Delivery_Zip_Code_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cleaning_delivery_zip_code' );
register_deactivation_hook( __FILE__, 'deactivate_cleaning_delivery_zip_code' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cleaning-delivery-zip-code.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cleaning_delivery_zip_code() {

	$plugin = new Cleaning_Delivery_Zip_Code();
	$plugin->run();

}

/**
 * Admin notice in case of WooCommerce not active.
 */
function pt_woo_admin_notice__error() {
    $class = 'notice notice-error';
    $message = __( 'Irks! WooCommerce is not active. Cleaning Delivery Zip Code need WooCommerce to be install and activate.', 'sample-text-domain' );
 
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	run_cleaning_delivery_zip_code();   
} else {
	add_action( 'admin_notices', 'pt_woo_admin_notice__error' );
}



