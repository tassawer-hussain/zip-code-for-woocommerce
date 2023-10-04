<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://tassawer.com/
 * @since      1.0.0
 *
 * @package    Cleaning_Delivery_Zip_Code
 * @subpackage Cleaning_Delivery_Zip_Code/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cleaning_Delivery_Zip_Code
 * @subpackage Cleaning_Delivery_Zip_Code/public
 * @author     Tassawer <hello@tassawer.com>
 */
class Cleaning_Delivery_Zip_Code_Public {

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
	private $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = get_option( 'cdzc_settings' );


		add_shortcode('cdzc_input_field', array( $this, 'cdzc_zipcode_field_shortcode' ));

		// Is serving Ajax
		add_action( 'wp_ajax_nopriv_cdzc_is_serving_zipcode', array( $this, 'cdzc_is_serving_zipcode' ) );
		add_action( 'wp_ajax_cdzc_is_serving_zipcode', array( $this, 'cdzc_is_serving_zipcode' ) );

		// Add products to cart
		add_action( 'wp_ajax_nopriv_add_selected_products_into_cart', array( $this, 'add_selected_products_into_cart' ) );
		add_action( 'wp_ajax_add_selected_products_into_cart', array( $this, 'add_selected_products_into_cart' ) );

		// Save User email
		add_action( 'wp_ajax_nopriv_cdzc_save_useremail', array( $this, 'cdzc_save_useremail' ) );
		add_action( 'wp_ajax_cdzc_save_useremail', array( $this, 'cdzc_save_useremail' ) );

		// Zipcode read-only
		add_action( 'wp_footer', array( $this, 'cdzc_read_only_zipcode' ));
		add_action( 'woocommerce_before_checkout_form', array( $this, 'cdzc_collect_useremail_on_checkout' ));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cleaning-delivery-zip-code-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name.'-isotope', plugin_dir_url( __FILE__ ) . 'js/isotope.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( $this->plugin_name.'-ajax', plugin_dir_url( __FILE__ ) . 'js/cleaning-delivery-zip-code-public-ajax.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( $this->plugin_name.'-ajax', 'frontend_ajax_object',
		array(
			'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
			'siteurl'	=> site_url(),
			'settings' 	=> $this->settings,
			)
		);
		
		wp_enqueue_script( $this->plugin_name.'-gmap', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCxa7CWP7E1dMr6zTG-753L-IHqH_b7_Ik&libraries=places', array( 'jquery' ), null, true );
		wp_enqueue_script( $this->plugin_name.'-ubi', plugin_dir_url( __FILE__ ) . 'js/jquery.geocomplete.js', array( 'jquery' ), null, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cleaning-delivery-zip-code-public.js', array( 'jquery', 'bootstrap' ), $this->version, true );
	}

	/**
	 * User has place an order already
	 */
	public function cdzc_has_bought( ) {
		global $wpdb;
		
		$meta_key   = '_customer_user';
		$meta_value = (int) get_current_user_id();
		
		$paid_order_statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );
		
		$count = $wpdb->get_var( $wpdb->prepare("
			SELECT COUNT(p.ID) FROM {$wpdb->prefix}posts AS p
			INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
			WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $paid_order_statuses ) . "' )
			AND p.post_type LIKE 'shop_order'
			AND pm.meta_key = '%s'
			AND pm.meta_value = %s
			LIMIT 1
		", $meta_key, $meta_value ) );
	
		// Return a boolean value based on orders count
		return $count > 0 ? true : false;
	}

	/**
	 * Shortocde to show zipcode field on frontend
	 */
	public function cdzc_zipcode_field_shortcode() {

		$zip_requested = $_COOKIE['non_logged_user_check_for'];
		global $wpdb;
		$table = $wpdb->get_blog_prefix().'zipcode_serving';
		$count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE zipcode = '$zip_requested'");

		if( (is_user_logged_in() && $this->cdzc_has_bought()) || (!empty($zip_requested) && $count)) {
			$notice = '<p class="cdzc-alerts success">You are booking laundry for Zip Code <strong>'.$zip_requested.'</strong>.</p>';
			$html = '';
			$html .= '<div class="cdzc-container">';
			$html .= '	<div class="cdzc-row">';
			$html .= '		<div class="cdzc-col">';
			$html .= '			<div class="cdzc-notices" id="cdzc-notices">'.$notice;
			$html .= '			</div>';
			$html .= '			<div class="cdzc-spacing cdzc-input-zipcode">';
			$html .= '				<div class="form-wrapper">';
			$html .= '					<label>Zip Code</label>';
			$html .= '					<input type="text" name="serving-zipcode" id="serving-zipcode" value="" placeholder="Enter zip code" />';
			$html .= '					<input type="submit" id="check-serving-zipcode" value="Check" />';
			$html .= '				</div>';
			$html .= '			</div>';
			$html .= '			<div class="cdzc-spacing cdzc-woo-items">';
			$html .= $this->cdzc_return_woo_items();
			$html .= '			</div>';
			$html .= '		</div>';
			$html .= '	</div>';
			$html .= '</div>';
		} else if ( !empty($zip_requested) ) {
			$notice = '<p class="cdzc-alerts error">You are booking laundry for Zip Code <strong>'.$zip_requested.'</strong>.</p>';
			$html = '';
			$html .= '<div class="cdzc-container">';
			$html .= '	<div class="cdzc-row">';
			$html .= '		<div class="cdzc-col">';
			$html .= '			<div class="cdzc-notices" id="cdzc-notices">'.$notice;
			$html .= '			</div>';
			$html .= '			<div class="cdzc-input-useremail">';
			$html .= '			</div>';
			$html .= '			<div class="cdzc-spacing cdzc-input-zipcode">';
			$html .= '				<div class="form-wrapper">';
			$html .= '					<label>Zip Code</label>';
			$html .= '					<input type="text" name="serving-zipcode" id="serving-zipcode" value="" placeholder="Enter zip code" />';
			$html .= '					<input type="submit" id="check-serving-zipcode" value="Check" />';
			$html .= '				</div>';
			$html .= '			</div>';
			$html .= '			<div class="cdzc-spacing cdzc-woo-items">';
			$html .= '			</div>';
			$html .= '		</div>';
			$html .= '	</div>';
			$html .= '</div>';
		} else {
			$html = '';
			$html .= '<div class="cdzc-container">';
			$html .= '	<div class="cdzc-row">';
			$html .= '		<div class="cdzc-col">';
			$html .= '			<div class="cdzc-notices" id="cdzc-notices">';
			$html .= '			</div>';
			$html .= '			<div class="cdzc-input-useremail">';
			$html .= '			</div>';
			$html .= '			<div class="cdzc-spacing cdzc-input-zipcode">';
			$html .= '				<div class="form-wrapper">';
			$html .= '					<label>Zip Code</label>';
			$html .= '					<input type="text" name="serving-zipcode" id="serving-zipcode" value="" placeholder="Enter zip code" />';
			$html .= '					<input type="submit" id="check-serving-zipcode" value="Check" />';
			$html .= '				</div>';
			$html .= '			</div>';
			$html .= '			<div class="cdzc-spacing cdzc-woo-items">';
			$html .= '			</div>';
			$html .= '		</div>';
			$html .= '	</div>';
			$html .= '</div>';
		}
    
		return $html;

	}

	/**
	 * Ajax call to check we are serving in this area or not.
	 */
	public function cdzc_is_serving_zipcode() {

		if (!empty($_REQUEST['zipcode'])) {

			$cookie_expire_on = time() + 60 * 60 * 24;
			setcookie('non_logged_user_check_for', $_REQUEST['zipcode'], $cookie_expire_on, '/');
		
			global $wpdb;
			$prefix = $wpdb->get_blog_prefix();
			$table = $prefix.'zipcode_serving';
		
			$zipcode = $_REQUEST['zipcode'];
			$count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE zipcode = '$zipcode'");
			
			// fetch products
			$woo_items = $this->cdzc_return_woo_items();

			// We are serving in this zip code
			if($count) {
				$zipcode_status = 'serving';
				setcookie('zipcode_status', 'serving', $cookie_expire_on, '/');
				$notice = '<p class="cdzc-alerts success">You are booking laundry for Zip Code '.$zipcode.'.</p>';
			} else {
				$zipcode_status = 'non-serving';
				setcookie('zipcode_status', 'non-serving', $cookie_expire_on, '/');
				$notice = str_replace("{{zipcode}}", "<strong>$zipcode</strong>", $this->settings['not_providing_services']);
				$notice = '<p class="cdzc-alerts error">'. $notice.'</p>';
				// we are not serving in this zip code
				//$user_ip = $this->cdzc_get_client_ip();
				if( $this->cdzc_is_cookies_exist('is_requested', $zipcode) != 2 ) {
					// User not requested for this Zip code
					$this->cdzc_set_ip_cookies('is_requested', $zipcode);

					// Check someone already requested for this ZIP Code
					$table = $prefix.'zipcode_requested';
					$count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE zipcode = '$zipcode'");

					// Already requested by someone else
					if($count) {

						$pt_query = "SELECT * FROM $table WHERE zipcode = '$zipcode'";
						$zip_codes = $wpdb->get_results( $wpdb->prepare( $pt_query, '' ), ARRAY_A );

						$zip_codes = $zip_codes[0];
						$request_count = $zip_codes['request_count'];
						$request_count = intval($request_count);
						$request_count++;
						$zip_codes['request_count'] = $request_count;

						$wpdb->update( $wpdb->get_blog_prefix() . 'zipcode_requested', $zip_codes, array('id' => $zip_codes['id']) );
						
					} else {

						$zipcode_data = array();
						$zipcode_data['zipcode'] = $zipcode;
						$zipcode_data['request_count'] = 1;
						$wpdb->insert( $wpdb->get_blog_prefix() . 'zipcode_requested', $zipcode_data );
					}
				}
			}

			$message = array(
				'status' => 'success',
				'zipcode_status' => $zipcode_status,
				'products' => $woo_items,
				'notice' => $notice,
				'collectemail' => $this->cdzc_collect_email_html($zipcode),
			);
			echo json_encode($message);
			wp_die();

		} else {
			$message = array(
				'status' => 'error',
				'result' => '<p class="cdzc-alerts error">'. $this->settings['no_zipcode_added'].'</p>',
			);
			echo json_encode($message);
			wp_die();
		}

	}

	public function cdzc_save_useremail() {
		if (!empty($_REQUEST['useremail']) && !empty($_REQUEST['zipcode']) ) {
			global $wpdb;
			$prefix = $wpdb->get_blog_prefix();
			$table = $prefix.'zipcode_requested_users';
		
			$useremail = $_REQUEST['useremail'];
			$zipcode = $_REQUEST['zipcode'];

			$count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE user_email = '$useremail' AND zipcode = $zipcode");

			if($count) {
				$notice = str_replace("{{zipcode}}", "<strong>$zipcode</strong>", $this->settings['user_already_requested']);
				$notice = '<p class="cdzc-alerts error">'. $notice.'</p>';
				$message = array(
					'status' => 'error',
					'result' => $notice,
				);
				echo json_encode($message);
				wp_die();
			} else {
				$zipcode_data = array();
				$zipcode_data['zipcode'] = $zipcode;
				$zipcode_data['user_email'] = $useremail;
				$result = $wpdb->insert( $wpdb->get_blog_prefix() . 'zipcode_requested_users', $zipcode_data );

				$message = array(
					'status' => 'error',
					'result' => '<p class="cdzc-alerts success">'. $this->settings['thankyou_for_email'].'</p>',
				);
				echo json_encode($message);
				wp_die();
			}
		} else {
			$message = array(
				'status' => 'error',
				'result' => '<p class="cdzc-alerts error">'. $this->settings['no_valid_email'].'</p>',
			);
			echo json_encode($message);
			wp_die();
		}

	}

	public function cdzc_collect_email_html($zipcode) {
		$html = '';
		$html .= '<div class="form-wrapper">';
		$html .= '	<label>Email</label>';
		$html .= '	<input type="email" name="user-email" id="user-email" data-zipcode="'.$zipcode.'" value="" placeholder="Email Address" autofocus />';
		$html .= '	<input type="submit" id="add-user-email" value="Submit" />';
		$html .= '</div>';

		return $html;
	}

	// set cookies with IP
	public function cdzc_set_ip_cookies($ip, $zip) {
		$cookie_expire_on = time() + 60 * 60 * 24 * 30;
		$cookies_exist = $this->cdzc_is_cookies_exist($ip, $zip);

		if( $cookies_exist == 0 ) {
			setcookie($ip, $zip, $cookie_expire_on, '/');
		} else if( $cookies_exist == 1 ) {
			$zips = $_COOKIE[$ip];
			$zips = $zips . "," . $zip;
			setcookie($ip, $zips, $cookie_expire_on, '/');
		}
	}

	/**
	 * check cookies already exist or not
	 * 0 - Not exit
	 * 1 - Exist but not added Ip
	 * 2 - Exist with IP
	 */ 

	public function cdzc_is_cookies_exist($ip, $zip) {
    
    	if( !isset( $_COOKIE[$ip] ) ) {
        	return 0;
		} else {
        	$zipcodes = explode(",", $_COOKIE[$ip]);
        	foreach($zipcodes as $zipcode) {
				if($zipcode == $zip)
					return 2;					
			}
        	return 1;
		}
	}

	// Function to get the client IP address
	public function cdzc_get_client_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}

	/**
	 * Ajax call to add selected products into cart on 'Add To Cart' button click
	 */
	function add_selected_products_into_cart() {
		if (empty($_REQUEST['laundry_items'])) {
			$message = array(
				'status' => 'error',
				'result' => '<p>Please select atleat one products.</p>',
			);
			echo json_encode($message);
			wp_die();
		} else {
			global $woocommerce;
			$woocommerce->cart->empty_cart();
			$laundry_items = $_REQUEST['laundry_items'];
			foreach($laundry_items as $product => $quantity) {
				WC()->cart->add_to_cart( $product, $quantity );
			} 


			// Get items with quantity from cart
			// $cd_cart = array();
			// global $woocommerce;
			// $items = $woocommerce->cart->get_cart();
			// foreach($items as $item => $values) { 
			// 	$cd_cart[$values['data']->get_id()] = $values['quantity'];
			// }
			
			// $laundry_items = $_REQUEST['laundry_items'];
			// foreach($laundry_items as $product => $quantity) {
			// 	if(array_key_exists($product, $cd_cart)) {
			// 		$cartId = WC()->cart->generate_cart_id( $product );
			// 		$cartItemKey = WC()->cart->find_product_in_cart( $cartId );
			// 		WC()->cart->remove_cart_item( $cartItemKey );
			// 		WC()->cart->add_to_cart( $product, $quantity );
			// 	} else {
			// 		WC()->cart->add_to_cart( $product, $quantity );
			// 	}
			// }
			$message = array(
				'status' => 'success',
				'result' => '',
			);
			echo json_encode($message);
			wp_die();
		}
	}
	
	/**
	 * Return product categories
	 */
	public function cdzc_return_woo_product_categories() {

		$cat_args = array(
			'orderby'    => 'name',
			'order'      => 'asc',
			'hide_empty' => true,
		);
		
		$product_categories = get_terms( 'product_cat', $cat_args );

		$html = '';
		if( !empty($product_categories) ){
			$html .= '<div class="button-group filter-button-group"><button data-filter="*" class="active">show all</button>';
			foreach ($product_categories as $key => $category) {
				$html .= '<button data-filter=".'. $category->slug .'">'. $category->name .'</button>';
			}
			$html .= '<button data-filter=" " >Items Added</button>';
			$html .= '</div>';
		}

		return $html;
	}

	public function cdzc_get_product_cats($post_ID) {
		$cats = '';
		$terms = get_the_terms( $post_ID, 'product_cat' );
		foreach ($terms as $term) {
			$cats .= $term->slug." ";
		}
		return $cats;
	}

	/**
	 * CONFIRMED
	 * Return Woo Items, If we are serving in the zipcode entered by the user
	 */
	public function cdzc_return_woo_items() { 
		
		// variables to hold loop data
		$product_filters = ''; // hold filter buttons
		$product_listing = ''; // hold products with category name and description
		$product_selected = '';
		$img = '';
		$subtotal = 0;

		// Get items with quantity from cart
		$cd_cart = array();
		global $woocommerce;
		$items = $woocommerce->cart->get_cart();
		foreach($items as $item => $values) { 
			$cd_cart[$values['data']->get_id()] = $values['quantity'];
		}
		
		/** Get product categories */
		$cat_args = array(
			'orderby'    => 'name',
			'order'      => 'asc',
			'hide_empty' => true,
		);
		$product_categories = get_terms( 'product_cat', $cat_args );

		if( !empty($product_categories) ) {
			$product_filters 	.= '<div class="button-group filter-button-group"><button data-filter="*" class="active">show all</button>';
			$product_listing 	.= '<ul class="products">';
			$product_selected 	.= '<div class="atc-btn-wrapper">
			<div class="woocommerce cdzc-woo">
				<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
					<thead>
						<tr>
							<th class="product-remove">&nbsp;</th>
							<th class="product-thumbnail">&nbsp;</th>
                            <th class="product-name">Product</th>
							<th class="product-price">Price</th>
							<th class="product-quantity">Quantity</th>
							<th class="product-subtotal">Total</th>
						</tr>
					</thead>
					<tbody class="added-items">';
			foreach ($product_categories as $key => $category) {
				$product_filters .= '<button data-filter=".'. $category->slug .'">'. $category->name .'</button>';

				// Product loop
				$args = array( 
					'post_type' => 'product',
					'posts_per_page' => -1,
					'product_cat' => $category->slug,
					'orderby' => 'date' );
				$loop = new WP_Query( $args );

				if($loop->have_posts()): 
					$product_listing .= "<li class='gutter-sizer'></li><li class='product product-full $category->slug' data-category='$category->slug'><h2 class='cat-title'>$category->name</h2>";
					$product_listing .= "<p class='cat-desc'>$category->description</p></li>";
					while ( $loop->have_posts() ) : $loop->the_post(); global $product;
		
						$pro_id = $loop->post->ID;
						$pro_cats = $this->cdzc_get_product_cats($loop->post->ID);
						$pro_title = get_the_title();
		
						if ($product->is_on_sale()) {
							$pro_price = get_post_meta( $pro_id, '_sale_price', true );						
						} else {
							$pro_price = get_post_meta( $pro_id, '_regular_price', true );
						}
		
						$val = array_key_exists($pro_id, $cd_cart) ? $cd_cart[$pro_id] : 0;
						$pro_class = ($val) ? 'th-pro-selected' : 'pro-hide';
						$checked = ($val) ? 'checked' : '';
		
						$turnaround = get_post_meta( $pro_id, 'cdsm_turnaround', true );
						$turnaround_time = "<br><strong>Turnaround Time:</strong> ";
						$turnaround_time .= ($turnaround == 1) ? $turnaround." day" : $turnaround." days";
		
						$si_turnaround = ($turnaround > 1) ? $turnaround_time : '';
		
						$short_dec = get_the_excerpt();
						$tooltip_content = !empty($short_dec) ? $short_dec.$turnaround_time : str_replace('<br>', '', $turnaround_time);
		
						$product_listing .= '<li class="product '. $pro_cats .'" data-category="'. $pro_cats .'"><div class="cdzc-product-wrapper">';
						
						if(has_post_thumbnail( $pro_id )) {
							$product_listing .= get_the_post_thumbnail($pro_id, 'shop_catalog');
							$img = get_the_post_thumbnail_url($pro_id, 'thumbnail');
						} else {
							$product_listing .= '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" width="300px" height="300px" />';
							$img = woocommerce_placeholder_img_src();
						}
						
						$pro_total = number_format((float)($pro_price * $val), 2, '.', '');
						$subtotal += floatval($pro_total);
		
						$product_listing .= '<h3>'. get_the_title() .'</h3>';
						$product_listing .= '<span class="price">'. $product->get_price_html() .'</span>';
						if( $pro_id != 89) {
							$product_listing .= '<div class="input-group cdzc-input-group">';
							$product_listing .= '	<input type="button" value="-" class="button-minus" data-field="quantity">';
							$product_listing .= '	<input type="number" step="1" min="0" max="100" value="'. $val .'" name="quantity" class="quantity-field" id="'. $pro_id .'">';
							$product_listing .= '	<input type="button" value="+" class="button-plus" data-field="quantity">';
							$product_listing .= '</div>';
						} else {
							$product_listing .= '<div class="input-group cdzc-input-group">';
							$product_listing .= '	<input id="89-minus" type="button" value="-" class="button-minus" data-field="quantity" style="display:none;">';
							$product_listing .= '	<input type="number" step="1" min="0" max="100" value="'. $val .'" name="quantity" class="quantity-field" id="'. $pro_id .'" style="display:none;">';
							$product_listing .= '	<input id="89-plus" type="button" value="+" class="button-plus" data-field="quantity" style="display:none;">';
							$product_listing .= '  <input type="checkbox" name="'. $pro_id .'" '.$checked.'><label for="wash" id="wash-89"> Yes</label>';
							$product_listing .= '</div>';
						}
						$product_listing .= '</div><span class="glyphicon glyphicon-info-sign cd-pro-info" data-html="true" data-toggle="tooltip" data-placement="left" data-original-title="'.$tooltip_content.'"></span></li>';
						
						$product_selected .= '<tr class="cdzc-select-pro woocommerce-cart-form__cart-item cart_item '. $pro_class .'" data-id="'. $pro_id .'">';
						$product_selected .= '<td class="product-remove"><input type="button" class="cart-btn remove cdzc-remove" value="Remove" data-product_id="'. $pro_id .'" /></td>';
						$product_selected .= '<td class="product-thumbnail"><img width="50" src="'. $img .'" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail"></td>';
						$product_selected .= '<td class="product-name" data-title="Product">'. get_the_title() . $si_turnaround .'</td>';
						$product_selected .= '<td class="product-price" data-title="Price" data-price="'. $pro_price .'">'. wc_price($pro_price) .'</td>';
						$product_selected .= '<td class="product-quantity" data-title="Quantity">'. $val .'</td>';
						$product_selected .= '<td class="product-subtotal" data-title="Total"><span class="woocommerce-Price-amount amount"><bdi>';
						$product_selected .= '<span class="woocommerce-Price-currencySymbol">$</span><span class="cd-price">'. $pro_total .'</span></bdi></span></td>';
						$product_selected .= '</tr>';
						
					endwhile;
				endif;
				wp_reset_query();

			} // foreach - $category
			$product_filters .= '<button data-filter=" " >Items Added</button>';
			$product_filters .= '</div>';

			$product_listing .= '</ul>';

			$subtotal = number_format((float)$subtotal, 2, '.', '');
			$product_selected .= '</tbody></table>
				<div class="cart-collaterals">
					<div class="cart_totals ">
						<table cellspacing="0" class="shop_table shop_table_responsive">
							<tbody>
								<tr class="cart-subtotal">
									<th>Subtotal</th>
									<td data-title="Subtotal" class="cd-booking-subtotal"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>
									<span class="cd-price-subtotal">'. $subtotal .'</span></bdi></span></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<input type="button" value="Proceed to checkout" class="cart-btn" id="cart-btn" />
			</div>';
		}

		$html = '';
		$html .= '<div class="product-row">';
		$html .= $product_filters;
		$html .= $product_listing;
		$html .= $product_selected;
		$html .= '</div>';

		return $html;
	}
	
	/**
	 * Read only Zipcode
	 */
	public function cdzc_read_only_zipcode() {
		if(is_checkout()) {
			if( ! is_user_logged_in() ) {
				$zip = $_COOKIE['non_logged_user_check_for']; ?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						if( jQuery("#ship-to-different-address-checkbox").prop('checked') == true) {
							jQuery('#shipping_postcode').val('<?php echo $zip; ?>');
						} else {
							jQuery('#billing_postcode').val('<?php echo $zip; ?>');
						}

						jQuery('#ship-to-different-address-checkbox').change(function() {
							if(this.checked) {
								jQuery('#shipping_postcode').val('<?php echo $zip; ?>');
								jQuery('#billing_postcode').val('');
							} else {
								jQuery('#billing_postcode').val('<?php echo $zip; ?>');
								jQuery('#shipping_postcode').val('');
							}
						});

					});
				</script>

		<?php }
		}
	}

	/**
	 * woocommerce_before_checkout_form
	 */
	public function cdzc_collect_useremail_on_checkout() {
		$zip_status = $_COOKIE['zipcode_status'];
		if($zip_status == 'non-serving') {
			$zipcode = $_COOKIE['non_logged_user_check_for'];
			$notice = str_replace("{{zipcode}}", "<strong>$zipcode</strong>", $this->settings['not_providing_services']);
			$collectEmail = $this->cdzc_collect_email_html($zipcode);
			echo '
			<div class="cdzc-col cdzc-col-checkout" >
				<div class="cdzc-notices" id="cdzc-notices"><p class="cdzc-alerts error">'. $notice .'</p></div>
				<div class="cdzc-input-useremail cdzc-spacing">'. $collectEmail .'</div>
			</div>';
		}
	}

	/*
	public function cdzc_read_only_zipcode() {
		if(is_checkout()) {

			if( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				$shipping_zipcode = get_user_meta($user_id, 'shipping_postcode', true);
				$billing_zipcode = get_user_meta($user_id, 'billing_postcode', true);
				$zip = ( !empty($shipping_zipcode) ) ? $shipping_zipcode : $billing_zipcode; 
			} else {
				$zip = $_COOKIE['non_logged_user_check_for'];
			} ?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				if( jQuery("#ship-to-different-address-checkbox").prop('checked') == true) {
					jQuery('#shipping_postcode').val('<?php echo $zip; ?>');
					jQuery('#shipping_postcode').attr('readonly', 'readonly');
				} else {
					jQuery('#billing_postcode').val('<?php echo $zip; ?>');
					jQuery('#billing_postcode').attr('readonly', 'readonly');
				}

				jQuery('#ship-to-different-address-checkbox').change(function() {
		            if(this.checked) {
						jQuery('#shipping_postcode').val('<?php echo $zip; ?>');
						jQuery('#shipping_postcode').attr('readonly', 'readonly');
						jQuery('#billing_postcode').val('');
						jQuery('#billing_postcode').removeAttr('readonly');
					} else {
						jQuery('#billing_postcode').val('<?php echo $zip; ?>');
						jQuery('#billing_postcode').attr('readonly', 'readonly');
						jQuery('#shipping_postcode').val('');
						jQuery('#shipping_postcode').removeAttr('readonly');
					}
				});

			});
		</script>

		<?php }
	} */

}
