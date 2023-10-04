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

	var temp_products;
	$( window ).load(function() {
		temp_products = JSON.parse(localStorage.getItem("temp_products") || "[]");
		console.log("# of temp products: " + temp_products.length);
		temp_products.forEach(function(product, index) {
			if(product.id == "89") {
				$('input[name="89"]').prop("checked", true);
			}
			$("input#"+product.id).val(product.quantity).trigger('change');
			console.log("[" + index + "]: " + product.id + " - " + product.quantity);
		});

	});

	/**
	 * Set product with quantity in local storage
	 */
	function update_product_in_localstorage(id, quantity) {
		var isFound = false;
		
		temp_products = JSON.parse(localStorage.getItem("temp_products") || "[]");
		for (var i = 0; i < temp_products.length; i++) {
			if(id === temp_products[i].id){  //look for match with id
				isFound = 	true;
				if(quantity > 0) {
					temp_products[i].quantity = quantity;
					break;  //exit loop since you found the person
				} else {
					temp_products.splice(i, 1);
				}
			}
		}

		if(isFound) {
			localStorage.setItem("temp_products", JSON.stringify(temp_products));  //put the object back
		} else {
			var product = {
				id: id,
				quantity: quantity
			};
			temp_products.push(product);
			// Saving
			localStorage.setItem("temp_products", JSON.stringify(temp_products));
		}

	}

	function update_subtotal() {
		var total = 0;
		$( ".cd-price" ).each(function( index ) {
			var subTotal = parseFloat( $( this ).html() );
			total +=  subTotal;
		});
		total = total.toFixed(2)
		$('.cd-price-subtotal').html(total);

		var selected = $('.added-items .th-pro-selected');
		selected.removeClass('cdtr-bg-style');
		selected.each(function(index) {
			if( (index%2) == 0) {
				$( this ).addClass('cdtr-bg-style');
			}
		});
	}

	function incrementValue(e) {
		e.preventDefault();
        var fieldName = $(e.target).data('field');
        var parent = $(e.target).closest('div');
        var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);
		var proID = parent.find('input[name=' + fieldName + ']').attr('id');
		
		if (!isNaN(currentVal)) {
        	if(currentVal < 100) {
				// Setting Product in local storage
				update_product_in_localstorage(proID, currentVal+1);
		
				parent.find('input[name=' + fieldName + ']').val(currentVal + 1);
				$('tr[data-id="'+proID+'"]').removeClass('pro-hide').addClass('th-pro-selected');
				$('tr[data-id="'+proID+'"] .product-quantity').html(currentVal + 1);
				
				var proPrice = $('tr[data-id="'+proID+'"] .product-price').data('price');
				var subTotal = (currentVal + 1) * proPrice;
				subTotal = Math.round((subTotal + Number.EPSILON) * 100) / 100;
				subTotal = subTotal.toFixed(2);
				$('tr[data-id="'+proID+'"] .product-subtotal .cd-price').html(subTotal);
				// $('tr[data-id="'+proID+'"] .product-subtotal .cd-price').html((currentVal + 1) * proPrice);
            }
        } else {
			parent.find('input[name=' + fieldName + ']').val(0);
			$('tr[data-id="'+proID+'"]').addClass('pro-hide').removeClass('th-pro-selected');
		}
		
		update_subtotal();
    }
    
    function decrementValue(e) {
        e.preventDefault();
        var fieldName = $(e.target).data('field');
        var parent = $(e.target).closest('div');
		var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);
		var proID = parent.find('input[name=' + fieldName + ']').attr('id');
	
		if (!isNaN(currentVal) && currentVal > 0) {
			// Setting Product in local storage
			update_product_in_localstorage(proID, currentVal-1);
			parent.find('input[name=' + fieldName + ']').val(currentVal - 1);
			console.log(parent.find('input[name=' + fieldName + ']').val(currentVal - 1));
			if( (currentVal - 1) == 0) {
				$('tr[data-id="'+proID+'"]').addClass('pro-hide').removeClass('th-pro-selected');
			}
			$('tr[data-id="'+proID+'"] .product-quantity').html(currentVal - 1);

			var proPrice = $('tr[data-id="'+proID+'"] .product-price').data('price');
			var subTotal = (currentVal - 1) * proPrice;
			subTotal = Math.round((subTotal + Number.EPSILON) * 100) / 100;
			subTotal = subTotal.toFixed(2);
			$('tr[data-id="'+proID+'"] .product-subtotal .cd-price').html(subTotal);

        } else {
			parent.find('input[name=' + fieldName + ']').val(0);
		}
		
		update_subtotal();
    }
    
    $(document).on('click', '.input-group.cdzc-input-group .button-plus', function(e) {
		incrementValue(e);
    });
    
    $(document).on('click', '.input-group.cdzc-input-group .button-minus', function(e) {
		decrementValue(e);
	});

	$(document).on('change', 'input[name="quantity"]', function() {
		var currentVal = parseInt($(this).val(), 10);
		var proID = $(this).attr('id');

		// Setting Product in local storage
		update_product_in_localstorage(proID, currentVal);

		$('tr[data-id="'+proID+'"] .product-quantity').html(currentVal);
		var proPrice = $('tr[data-id="'+proID+'"] .product-price').data('price');
		var subTotal = currentVal * proPrice;
		subTotal = Math.round((subTotal + Number.EPSILON) * 100) / 100;
		subTotal = subTotal.toFixed(2);
		$('tr[data-id="'+proID+'"] .product-subtotal .cd-price').html(subTotal);

		if (!isNaN(currentVal) && currentVal > 0 && currentVal <= 100) {
			$('tr[data-id="'+proID+'"]').removeClass('pro-hide').addClass('th-pro-selected');
        } else {
			$('tr[data-id="'+proID+'"]').addClass('pro-hide').removeClass('th-pro-selected');
		}
		
		update_subtotal();
	});

	// init Isotope
	var $grid = $('.products').isotope({
		layoutMode: 'fitRows',
		itemSelector: '.product',
	});

	// bind filter button click
	$(document).on( 'click', '.filter-button-group button', function() {
		$('.filter-button-group button').removeClass('active');
		$(this).addClass('active');

		var filterValue = $( this ).attr('data-filter');
		// use filterFn if matches value
		$grid.isotope({ filter: filterValue });
	});

	var zipcode = localStorage.getItem("zipcode");

	$(document).on('click', 'input.cdzc-remove', function() {
		var product_id = $(this).attr('data-product_id');
		if( product_id == "89" ) {
			$('input[name="89"]').prop("checked", false);
		}
		$('input[id="'+ product_id +'"]').val(0).trigger('change');
	});

	$('.glyphicon.cd-pro-info').tooltip();

	/** Wash & Fold - Handle Checkbox Click */
	$(document).on('click', 'input[name="89"]', function() {
		if ($(this).prop('checked')==true){ 
			//do something
			$('#89-plus').trigger('click');
		} else {
			$('#89-minus').trigger('click');
		}
	});

	/**Checkout Billing Fields */
	/* Add Hidden Fields For Locatlity & Sublocality */
	var city_field_html = $('#billing_city_field').html();
	city_field_html += '<input type="hidden" name="billing_city_locality" id="billing_city_locality" data-geo="locality">' +
		'<input type="hidden" name="billing_city_sublocality" id="billing_city_sublocality" data-geo="sublocality">';
	$('#billing_city_field').html(city_field_html);
	/* Apply IDs To Necessary Fields */
	$('#billing_postcode').attr('data-geo', 'postal_code');
	/* Geo Code For Address AutoComplete */
	$('#billing_address_1').geocomplete({
		details: ".woocommerce-billing-fields, .woocommerce-address-fields__field-wrapper",
		types: ["geocode", "establishment"],
		detailsAttribute: "data-geo",
	}).bind("geocode:result", function(event, result){
		$('#billing_address_1').val(result.name);
		var th = result.address_components;
		var state = th.find(x => x.types.includes('administrative_area_level_1')).long_name;
		var state_short = th.find(x => x.types.includes('administrative_area_level_1')).short_name;
		
		$('#billing_state option[value="'+state_short+'"]').attr('selected', 'selected');
		$('#select2-billing_state-container').text(state);
		
		var address_locality = $('#billing_city_locality').val();
		var address_sublocality = $('#billing_city_sublocality').val();

		if(address_sublocality !== '' && address_locality === ''){
			$('#billing_city').val(address_sublocality);
		}
		else if(address_sublocality === '' && address_locality !== ''){
			$('#billing_city').val(address_locality);
		}
		else if(address_sublocality !== '' && address_locality !== ''){
			$('#billing_city').val(address_sublocality);
		}
	});

	/* Add Hidden Fields For Locatlity & Sublocality */
	var city_field_html = $('#shipping_city_field').html();
	city_field_html += '<input type="hidden" name="shipping_city_locality" id="shipping_city_locality" data-geo="locality">' +
		'<input type="hidden" name="shipping_city_sublocality" id="shipping_city_sublocality" data-geo="sublocality">';
	$('#shipping_city_field').html(city_field_html);
	/* Apply IDs To Necessary Fields */
	$('#shipping_postcode').attr('data-geo', 'postal_code');
	/* Geo Code For Address AutoComplete */
	$('#shipping_address_1').geocomplete({
		details: ".woocommerce-shipping-fields, .woocommerce-address-fields__field-wrapper",
		types: ["geocode", "establishment"],
		detailsAttribute: "data-geo",
	}).bind("geocode:result", function(event, result){
		$('#shipping_address_1').val(result.name);
		var th = result.address_components;
		var state = th.find(x => x.types.includes('administrative_area_level_1')).long_name;
		var state_short = th.find(x => x.types.includes('administrative_area_level_1')).short_name;
		$('#shipping_state option[value="'+state_short+'"]').attr('selected', 'selected');
		$('#select2-shipping_state-container').text(state);
		
		var address_locality = $('#shipping_city_locality').val();
		var address_sublocality = $('#shipping_city_sublocality').val();

		if(address_sublocality !== '' && address_locality === ''){
			$('#shipping_city').val(address_sublocality);
		}
		else if(address_sublocality === '' && address_locality !== ''){
			$('#shipping_city').val(address_locality);
		}
		else if(address_sublocality !== '' && address_locality !== ''){
			$('#shipping_city').val(address_sublocality);
		}
	});

	/**
	 * Checkout Address validation
	 */
	// $('#billing_address_1').geocomplete();
	// $('#shipping_address_1').geocomplete();

	// $('#billing_country').attr('data-geo', 'country');
	// $('#billing_city').attr('data-geo', 'locality');
	// $('#billing_state').attr('data-geo', 'administrative_area_level_1');
	// $('#billing_postcode').attr('data-geo', 'postal_code');
	// /**
	//  * Trigger AutoComplete Address
	//  */
	// $("#billing_address_1").geocomplete({
	// 	details: ".woocommerce-billing-fields__field-wrapper",
	// 	detailsAttribute: "data-geo",
	// 	types: ["geocode", "establishment"],
	// });

	// $('#shipping_country').attr('data-geo', 'country');
	// $('#shipping_city').attr('data-geo', 'locality');
	// $('#shipping_state').attr('data-geo', 'administrative_area_level_1');
	// $('#shipping_postcode').attr('data-geo', 'postal_code');
	// /**
	//  * Trigger AutoComplete Address
	//  */
	// $("#shipping_address_1").geocomplete({
	// 	details: ".woocommerce-shipping-fields__field-wrapper",
	// 	detailsAttribute: "data-geo",
	// 	types: ["geocode", "establishment"],
	// });

})( jQuery );
