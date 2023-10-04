(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    var zipcode;

	function isEmpty(value){
        return (value == null || value.length === 0);
    }

    function validateEmail(email) {
        const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
      }

	/**
	 * Handle Zipcode Check Ajax Request
	 */
	$('#check-serving-zipcode').on('click', function() {
        
        $('.cdzc-woo-items').html('');
        zipcode = $('#serving-zipcode').val();
        localStorage.setItem("zipcode", zipcode);
        
        if(isEmpty(zipcode)) {
            $('.cdzc-input-zipcode .form-wrapper').css('border', '1px solid red');
            $('.cdzc-notices').html('<p class="cdzc-alerts error">'+ frontend_ajax_object.settings.no_zipcode_added +'</p>');
        } else {
            $('.cdzc-input-zipcode .form-wrapper').css('border', '1px solid #0098d6');
            $('.cdzc-notices').html('');
            $('.cdzc-input-useremail').html('');

            $.ajax({
                type: 'POST',
                url: frontend_ajax_object.ajaxurl,
                // dataType: 'json',
                data: {
                    action: "cdzc_is_serving_zipcode",
                    zipcode: zipcode,
                },
                beforeSend: function() {
                    $('body').append("<div class='loader'><div class='loader-wrapper'><div class='bubble1'></div><div class='bubble2'></div></div></div>");
                },
                success: function (response) {
                    if(response) {
                        response = JSON.parse(response);
                        if(response['status'] == 'error') {
                            $('.cdzc-notices').html( response['result'] );
                        } else if(response['status'] == 'success') {
							$('.cdzc-woo-items').html(response['products']);
							$('.cdzc-notices').html( response['notice'] );

							if(response['zipcode_status'] == "non-serving") {
                                $('.cdzc-input-useremail').addClass('cdzc-spacing').html(response['collectemail']);
                            } else {
                                $('.cdzc-input-useremail').removeClass('cdzc-spacing');
                            }

                            // init Isotope
                            var $grid = $('.products').isotope({
                                layoutMode: 'fitRows',
								itemSelector: '.product',
                            });
							
							$('.glyphicon.cd-pro-info').tooltip();

                            // bind filter button click
                            $(document).on( 'click', '.filter-button-group button', function() {
                                var filterValue = $( this ).attr('data-filter');
                                // use filterFn if matches value
                                $grid.isotope({ filter: filterValue });
                            });
   
                            var highestBox = 0;
                            jQuery('.products .product').each(function(){
                                if(jQuery(this).height() > highestBox){  
                                    highestBox = jQuery(this).height();  
                                }
                            })
                            jQuery('.products .product').height(highestBox);
                            jQuery('button[data-filter="*"]').trigger('click');

                        }
                    }
                },
                complete: function(){
                    $('.loader').remove();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                }
            });
        }
    });

    /**
     * Add selected products into cart
     */
    $(document).on('click', '#cart-btn', function() {

        var itemsSelected = {};
        $('.quantity-field').each(function() {
            if($(this).val() > 0) {
                itemsSelected[$(this).attr('id')] = $(this).val();
            }
        });
        
        if(Object.keys(itemsSelected).length === 0) {
            $('.cdzc-notices').html('<p class="cdzc-alerts error">'+ frontend_ajax_object.settings.no_product_selected +'</p>');
            $('html, body').animate({
                scrollTop: $("#cdzc-notices").offset().top - 100,
            }, 2000);
        } else {
            $('.cdzc-notices').html('');
            $.ajax({
                type: 'POST',
                url: frontend_ajax_object.ajaxurl,
                // dataType: 'json',
                data: {
                    action: "add_selected_products_into_cart",
                    laundry_items: itemsSelected,
                },
                beforeSend: function() {
                    $('body').append("<div class='loader'><div class='loader-wrapper'><div class='bubble1'></div><div class='bubble2'></div></div></div>");
                },
                success: function (response) {
                    localStorage.clear();
                    response = JSON.parse(response);
                    if(response['status'] == 'error') {
                        $('.cdzc-notices').html('<p class="cdzc-alerts error">'+ response['result'] + '</p>');
                    } else if(response['status'] == 'success') {
                        var cdzc_cart = frontend_ajax_object.siteurl + '/checkout';
                        window.location.replace(cdzc_cart);
                    }
                },
                complete: function(){
                    $('.loader').remove();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                }
            });
        }
    });

    /**
	 * Handle Registering Email for newsletter
	 */
	$(document).on('click', '#add-user-email', function() {
        
        var useremail = $('#user-email').val();
		var zipcode = $('#user-email').data('zipcode');
        
        if( isEmpty(useremail) || !validateEmail(useremail)) {
            $('.cdzc-input-useremail .form-wrapper').css('border', '1px solid red');
            $('.cdzc-notices').html('<p class="cdzc-alerts error">'+ frontend_ajax_object.settings.no_valid_email +'</p>');
        } else {
            $('.cdzc-input-useremail .form-wrapper').css('border', '1px solid #0098d6');
            $('.cdzc-notices').html('');
            $.ajax({
                type: 'POST',
                url: frontend_ajax_object.ajaxurl,
                // dataType: 'json',
                data: {
                    action: "cdzc_save_useremail",
                    useremail: useremail,
                    zipcode: zipcode,
                },
                beforeSend: function() {
                    $('body').append("<div class='loader'><div class='loader-wrapper'><div class='bubble1'></div><div class='bubble2'></div></div></div>");
                },
                success: function (response) {
                    if(response) {
                        response = JSON.parse(response);
                        if(response['status'] == 'error') {
                            $('.cdzc-notices').html( response['result'] );
                        }
                    }
                },
                complete: function(){
                    $('.loader').remove();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                }
            });
        }
    });

})( jQuery );
