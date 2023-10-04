<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Zip_Code
 * @subpackage Cleaning_Delivery_Zip_Code/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cleaning_Delivery_Zip_Code
 * @subpackage Cleaning_Delivery_Zip_Code/admin
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Zip_Code_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = get_option( 'cdzc_settings' );

		add_action( 'admin_menu', array($this, 'pt_cdzc_settings_menu') );
		add_action( 'admin_init', array($this, 'pt_cdzc_serving_zipcodes_admin_init') );

		add_action( 'admin_init', array($this, 'cdzc_settings') );

		add_action( 'wp_ajax_export_registered_users_in_zipcode', array($this, 'export_registered_users_in_zipcode') );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cleaning_Delivery_Zip_Code_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cleaning_Delivery_Zip_Code_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cleaning-delivery-zip-code-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cleaning_Delivery_Zip_Code_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cleaning_Delivery_Zip_Code_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cleaning-delivery-zip-code-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'frontend_ajax_object',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			)
		);
	}

	public function pt_cdzc_settings_menu() {
		
		// Create top-level menu item
		add_menu_page( 
            'Cleaning Delivery Zip Code', //$page_title
            'CD Zip Code',//$menu_title
            'manage_options', // $capability
            'cd-zip-code', // $menu_slug
            array($this, 'cd_zip_code_callback' ), // $function
			'dashicons-admin-site-alt2',
			31
		); // $icon_url
 
		// Create a sub-menu under the top-level menu
		add_submenu_page( 
			'cd-zip-code', // $parent_slug
			'Requested Zip Code', //$page_title
			'Requested Zip Code', //$menu_title
			'manage_options', //$capability
			'cd-requested-zip-code', //$menu_slug
			array($this, 'cd_requested_zip_code_callback' ) ); //$function

		// Create a sub-menu under the top-level menu
		add_submenu_page( 
			'cd-zip-code', // $parent_slug
			'Users Requested', //$page_title
			'Users Requested', //$menu_title
			'manage_options', //$capability
			'cd-users-requested-zip-code', //$menu_slug
			array($this, 'cd_users_requested_zip_code_callback' ) ); //$function

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
			<h2>Cleaning Delivery Zip Code – Settings</h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php //settings_fields($option_group); ?>
				<?php // $option_group of register setting func
				settings_fields( 'cdzc_settings' ); ?>
				<?php //do_settings_sections($page); ?>
				<?php // $page of add_settings_section func
				do_settings_sections( 'cd-zip-code-settings' ); ?> 
				<input type="submit" value="Submit" class="button-primary" />
			</form>
		</div>
	<?php }

	public function pt_cdzc_serving_zipcodes_admin_init() {
		add_action( 'admin_post_save_edit_zipcode', array($this, 'process_serving_zipcode_number') );
		add_action( 'admin_post_delete_zipcode_record', array($this, 'delete_serving_zipcode_number') );
   	}

	/**
	 * Main Screen for Serving Zip Code Listing Callback
	 */
	public function cd_zip_code_callback() {
		global $wpdb; 
		$add_zip_code_slug = add_query_arg( array( 
			'page' => 'cd-zip-code',
			'id' => 'new' )); ?>
		<!-- Top-level menu -->
		<div id="pt-general" class="wrap">
			<h2>Serving Zip Codes 
				<?php if ( empty( $_GET['id'] ) ) { ?>
				<a class="add-new-h2" href="<?php echo $add_zip_code_slug; ?>">Add New Zip Code</a>
				<?php } ?>
			</h2>
			<?php settings_errors(); ?>

			<!-- Display zip code list if no parameter sent in URL -->
			<?php if ( empty( $_GET['id'] ) ) {
				$pt_query = 'select * from ';
				$pt_query .= $wpdb->get_blog_prefix() . 'zipcode_serving ';
				$pt_query .= 'ORDER by id DESC';
				
				$zip_codes = $wpdb->get_results( $wpdb->prepare( $pt_query, '' ), ARRAY_A );
			?>
			<h3>Manage Zip Codes</h3>
			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<input type="hidden" name="action" value="delete_zipcode_record" />
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field( 'zipcode_record_deletion' ); ?>

				<table class="wp-list-table widefat fixed" >
					<thead>
						<tr>
							<th style="width: 30px">Sr. #</th>
							<th style="width: 30px"></th>
							<th>Zip Code</th>
						</tr>
					</thead>
					<?php
						// Display bugs if query returned results
						if ($zip_codes) {
							$counter = 1;
							foreach ( $zip_codes as $zip_code ) {
								echo '<tr style="background: #FFF">';
								echo '<td>'. $counter .'</td>';
								echo '<td><input type="checkbox" name="zipcodes[]" value="';
								echo esc_attr( $zip_code['id'] ) . '" /></td>';
								echo '<td><a href="';
								echo add_query_arg( array('page' => 'cd-zip-code', 'id' => $zip_code['id'] ));
								echo '">' . $zip_code['zipcode'] . '</a></td>';
								$counter++;
							}
						} else {
							echo '<tr style="background: #FFF">';
							echo '<td colspan=4>No Record Found</td></tr>';
						}      
					?>
				</table>
				<br />
				<input type="submit" value="Delete Selected" class="button-primary"/>
			</form>
			
		<?php } elseif ( isset($_GET['id']) && ($_GET['id']=='new' || is_numeric($_GET['id'])) ) {
				$zipcode_id = $_GET['id'];
				$zipcode_data = array();
				$mode = 'new';

				// Query database if numeric id is present
				if ( is_numeric($zipcode_id) ) {
					$pt_query = 'select * from ' . $wpdb->get_blog_prefix();
					$pt_query .= 'zipcode_serving where id = ' . $zipcode_id;
					$zipcode_data = $wpdb->get_row( $wpdb->prepare( $pt_query, '' ), ARRAY_A );
					// Set variable to indicate page mode
					if ( $zipcode_data ) 
						$mode = 'edit';
				} else {
					$zipcode_data['zipcode'] = '';
				}

				// Display title based on current mode
				if ( $mode == 'new' ) {
					echo '<h3>Add New Zip Code</h3>';
				} elseif ( $mode == 'edit' ) {
					echo '<h3>Edit Zip Code # ' . $zipcode_data['zipcode'] . ' </h3> ';
				} ?>
				<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
					<input type="hidden" name="action" value="save_edit_zipcode" />
					<input type="hidden" name="id" value="<?php echo esc_attr( $zipcode_id ); ?>" />

					<!-- Adding security through hidden referrer field -->
					<?php wp_nonce_field( 'zipcode_add_edit' ); ?>

					<!-- Display knife editing form -->
					<table>
						<tr>
							<td style="width: 150px">Zip Code</td>
							<td><input type="text" name="zipcode" size="60" 
									value="<?php echo esc_attr($zipcode_data['zipcode']); ?>"/>
							</td>
						</tr>
					</table>
					<input type="submit" value="Submit" class="button-primary"/>
				</form>
		</div>
	<?php }
	}


	public function cdzc_settings() {
		// Register a setting group with a validation function
		// so that post data handling is done automatically for us
		register_setting( 
			'cdzc_settings', //$option_group, UNIQUE NAME
			'cdzc_settings', //$option_name, SAME AS IN DATABASE
			array( $this, 'cdzc_validate_options' )); //$args, CALL BACK VALIDATING FUNC 

			
		// Add a new settings section within the group
		add_settings_section( 
			'cdzc_requested_zipcode_section', //$id, unique name
			'Zip Code Highlight', //$title
			array( $this, 'cdzc_requested_zipcode_section_callback' ),//$callback
			'cd-zip-code-settings' );//$page

		// Add each field with its name and function to use for
		// our new settings, put them in our new section
		add_settings_field( 
			'requested_zipcode_highlight', //$id
			'Number to hightlight the requested zip code count', //$title, a label that will be display next to the field
			array( $this, 'cdzc_display_text_field' ), //$callback
			'cd-zip-code-settings', //$page
			'cdzc_requested_zipcode_section', //$section
			array( 'name' => 'requested_zipcode_highlight' ) ); //$args

		
		// Add a new settings section within the group
		add_settings_section( 
			'cdzc_notices_section', //$id, unique name
			'Notices Section', //$title
			array( $this, 'cdzc_notices_section_callback' ),//$callback
			'cd-zip-code-settings' );//$page

		// No ZipCode Added - Clicked 'Check' button
		add_settings_field( // for display textarea
			'no_zipcode_added',//$id
			'No zip code added', //$title
			array( $this, 'cdzc_display_text_area' ), //$callback
			'cd-zip-code-settings',//$page
			'cdzc_notices_section', //$section
			array( 'name' => 'no_zipcode_added' ) );//$args

		// No Product Selected - Clicked 'Add to Cart' button
		add_settings_field( // for display textarea
			'no_product_selected',//$id
			'No product selected', //$title
			array( $this, 'cdzc_display_text_area' ), //$callback
			'cd-zip-code-settings',//$page
			'cdzc_notices_section', //$section
			array( 'name' => 'no_product_selected' ) );//$args

		// No Product Selected - Clicked 'Add to Cart' button
		add_settings_field( // for display textarea
			'no_valid_email',//$id
			'No Valid Email', //$title
			array( $this, 'cdzc_display_text_area' ), //$callback
			'cd-zip-code-settings',//$page
			'cdzc_notices_section', //$section
			array( 'name' => 'no_valid_email' ) );//$args

		// Add each field with its name and function to use for
		// our new settings, put them in our new section
		add_settings_field( // for display textarea
			'not_providing_services',//$id
			'Not providing services', //$title
			array( $this, 'cdzc_display_text_area' ), //$callback
			'cd-zip-code-settings',//$page
			'cdzc_notices_section', //$section
			array( 'name' => 'not_providing_services' ) );//$args

		add_settings_field( // for display textarea
		    'user_already_requested',//$id
		    'User already requested', //$title
		    array( $this, 'cdzc_display_text_area' ), //$callback
		    'cd-zip-code-settings',//$page
		    'cdzc_notices_section', //$section
		    array( 'name' => 'user_already_requested' ) );//$args

		add_settings_field( // for display textarea
		    'thankyou_for_email',//$id
		    'Subscribing to newsletter', //$title
		    array( $this, 'cdzc_display_text_area'), //$callback
		    'cd-zip-code-settings',//$page
		    'cdzc_notices_section', //$section
			array( 'name' => 'thankyou_for_email' ) );//$args
		add_settings_field( // for display textarea
			'create_account_checkout',//$id
			'Create account on checkout page', //$title
			array( $this, 'cdzc_display_text_area'), //$callback
			'cd-zip-code-settings',//$page
			'cdzc_notices_section', //$section
			array( 'name' => 'create_account_checkout' ) );//$args
		add_settings_field( // for display textarea
			'check_spam_mail',//$id
			'Check spam folder for email', //$title
			array( $this, 'cdzc_display_text_area'), //$callback
			'cd-zip-code-settings',//$page
			'cdzc_notices_section', //$section
			array( 'name' => 'check_spam_mail' ) );//$args
		add_settings_field( // for display textarea
			'delivery_note_on_thankyou',//$id
			'Delivery not on thank you page', //$title
			array( $this, 'cdzc_display_text_area'), //$callback
			'cd-zip-code-settings',//$page
			'cdzc_notices_section', //$section
			array( 'name' => 'delivery_note_on_thankyou' ) );//$args
		
	}

	public function cdzc_validate_options( $input ) {
		$input['version'] = $this->version;
		return $input;
	}

	// Declare a body for the cdzc_notices_section_callback function
	public function cdzc_notices_section_callback() { ?>
		<p>Add notices to show on frontend.</p>
	<?php }

	// Declare a body for the cdzc_requested_zipcode_section_callback function
	public function cdzc_requested_zipcode_section_callback() { ?>
		<p>Add number to get highlight the number of request.</p>
	<?php }
	
	public function cdzc_display_text_area( $data = array() ) {
		extract ( $data ); ?>
		<textarea type="text" name="cdzc_settings[<?php echo $name; ?>]"rows="3" cols="100"><?php if(isset($this->options[$name])) { echo esc_html($this->options[$name]); } ?></textarea>
	<?php }

	// Provide an implementation for the ch3sapi_display_text_field function
	public function cdzc_display_text_field( $data = array() ) {
		extract( $data ); ?>
		<input type="text" name="cdzc_settings[<?php echo $name; ?>]" value="<?php echo esc_html( $this->options[$name] ); ?>"/><br />
	<?php }

	/**
	 * Screen for requested zipcode listing callback
	 */
	public function cd_requested_zip_code_callback() {
		global $wpdb; ?>
		<!-- Top-level menu -->
		<div id="pt-general" class="wrap">
			<h2>Requested Zip Codes </h2>

			<!-- Display requested zip code list if no parameter sent in URL -->
			<?php $pt_query = 'select * from ';
				$pt_query .= $wpdb->get_blog_prefix() . 'zipcode_requested ';
				$pt_query .= 'ORDER by request_count DESC';
				
				$zip_codes = $wpdb->get_results( $wpdb->prepare( $pt_query, '' ), ARRAY_A );
			?>
			<h3>Request received for the following Zip Codes given with count.</h3>
			
			<table class="wp-list-table cd-table-center fixed" >
				<thead>
					<tr>
						<th style="width: 30px">Sr. #</th>
						<th style="width: 200px">Requested Zip Code</th>
						<th># of Request</th>
						<th># of Users Registered</th>
						<th>Action</th>
					</tr>
				</thead>
				<?php
					// Display bugs if query returned results
					$css_class = '';
					if ($zip_codes) {
						$counter = 1;
						foreach ( $zip_codes as $zip_code ) {
							
							if ($zip_code['request_count'] >= $this->options['requested_zipcode_highlight']) {
								$css_class = 'highlight';
							} else {
								$css_class = '';
							}

							$table = $wpdb->get_blog_prefix().'zipcode_requested_users';
						
							$zipcode = $zip_code['zipcode'];
							$count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE zipcode = '$zipcode'");

							echo '<tr style="background: #FFF" class="'. $css_class .'">';
							echo '<td>'. $counter .'</td>';
							echo '<td>'. $zip_code['zipcode'] .'</td>';
							echo '<td class="pt-request-count">'. $zip_code['request_count'] .'</td>';
							echo '<td class="">'. $count .'</td>';
							if($count) {
								echo '<td ><input type="submit" value="Export To CSV" class="button-primary export-users" data-zip="'. $zipcode .'"/></td>';
							} else {
								echo '<td ><input type="submit" value="Export To CSV" class="button-primary export-users" data-zip="'. $zipcode .'" disabled/></td>';
							}
							$counter++;
						}
					} else {
						echo '<tr style="background: #FFF">';
						echo '<td colspan=4>No Record Found</td></tr>';
					}      
				?>
			</table>

			<!-- <table>
				<tbody>
					<tr>
						<td></td>
						<td>Under 50</td>
						<td><span class="under-50"></span></td>
					</tr>

					<tr>
						<td></td>
						<td>Under 100</td>
						<td><span class="under-100"></span></td>
					</tr>

					<tr>
						<td></td>
						<td>Under 150</td>
						<td><span class="under-150"></span></td>
					</tr>
				</tbody>
			</table> -->
		</div>
		<?php
	}

	/**
	 * Screen for users requested zipcode listing callback
	 */
	public function cd_users_requested_zip_code_callback() {
		global $wpdb; ?>
		<!-- Top-level menu -->
		<div id="pt-general" class="wrap">
			<h2>Users Requested for Zip Codes </h2>

			<!-- Display requested zip code list if no parameter sent in URL -->
			<?php $pt_query = 'select * from ';
				$pt_query .= $wpdb->get_blog_prefix() . 'zipcode_requested_users ';
				$pt_query .= 'ORDER by zipcode DESC';
				
				$zip_codes = $wpdb->get_results( $wpdb->prepare( $pt_query, '' ), ARRAY_A );
			?>
			<h3>Users requested for the following Zip Codes given with email address</h3>
			
			<table class="wp-list-table cd-table-center fixed" >
				<thead>
					<tr>
						<th style="width: 30px">Sr. #</th>
						<th style="width: 200px">Requested Zip Code</th>
						<th>User email</th>
					</tr>
				</thead>
				<?php
					// Display bugs if query returned results
					if ($zip_codes) {
						$counter = 1;
						foreach ( $zip_codes as $zip_code ) {
							echo '<tr style="background: #FFF">';
							echo '<td>'. $counter .'</td>';
							echo '<td>'. $zip_code['zipcode'] .'</td>';
							echo '<td>'. $zip_code['user_email'] .'</td>';
							$counter++;
						}
					} else {
						echo '<tr style="background: #FFF">';
						echo '<td colspan=4>No Record Found</td></tr>';
					}      
				?>
			</table>
		</div>
		<?php
	}

	/**
	 * Save/Update new Serving Zip Code
	 */
	public function process_serving_zipcode_number() {
    
		// Check if user has proper security level
		if ( !current_user_can( 'manage_options' ) )
			wp_die( 'Not allowed' );

		// Check if nonce field is present for security
		check_admin_referer( 'zipcode_add_edit' );

		global $wpdb;
		// Place all user submitted values in an array (or empty
		// strings if no value was sent)
		$zipcode_data = array();
		$zipcode_data['zipcode'] = ( isset($_POST['zipcode']) ? $_POST['zipcode'] : '' );
		
		// Call the wpdb insert or update method based on value
		// of hidden bug_id field
		if ( isset($_POST['id']) && $_POST['id']=='new') {
			$wpdb->insert( $wpdb->get_blog_prefix() . 'zipcode_serving', $zipcode_data );
		} elseif ( isset($_POST['id']) && is_numeric($_POST['id']) ) {
			$wpdb->update( $wpdb->get_blog_prefix() . 'zipcode_serving', $zipcode_data, array('id' => $_POST['id']) );
		}

		// Redirect the page to the user submission form
		wp_redirect( add_query_arg('page', 'cd-zip-code', admin_url('admin.php')) );
		exit;
	}

	/**
	 * Delete selected Zip Codes number(s)
	 */
	public function delete_serving_zipcode_number() {
		// Check that user has proper security level
		if ( !current_user_can( 'manage_options' ) )
			wp_die( 'Not allowed' );

		// Check if nonce field is present
		check_admin_referer( 'zipcode_record_deletion' );

		// If bugs are present, cycle through array and call SQL
		// command to delete entries one by one 
		if ( !empty( $_POST['zipcodes'] ) ) {
			// Retrieve array of bugs IDs to be deleted
			$zipcodes_to_delete = $_POST['zipcodes'];

			global $wpdb;

			foreach ( $zipcodes_to_delete as $zipcode_to_delete ) {
				$query = 'DELETE from ' . $wpdb->get_blog_prefix();
				$query .= 'zipcode_serving ';
				$query .= 'WHERE id = ' . intval( $zipcode_to_delete );
				$wpdb->query( $wpdb->prepare( $query ) );
			}
		}

		// Redirect the page to the user submission form
		wp_redirect( add_query_arg( 'page', 'cd-zip-code', admin_url('admin.php' ) ) );
		exit; 
	}

	/**
	 * Create a CSV file and put users email against selected zipcode
	 */
	public function export_registered_users_in_zipcode() {
		if (!empty($_REQUEST['zip_code'])) {
				
			global $wpdb;
			$table = $wpdb->get_blog_prefix().'zipcode_requested_users';		
			$zipcode = $_REQUEST['zip_code'];

			$pt_query = "SELECT * FROM $table WHERE zipcode = '$zipcode'";
			$users = $wpdb->get_results( $wpdb->prepare( $pt_query, '' ), ARRAY_A );

			echo "email\n";
			foreach ( $users as $user ):
				echo $user['user_email']."\n";
			endforeach;
			exit();
		}
		
	}

}
