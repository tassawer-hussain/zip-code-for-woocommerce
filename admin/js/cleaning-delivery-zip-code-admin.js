(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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


	$(function() {
		$(".export-users").on('click', function() {
			var zip_code = $(this).data('zip');

			jQuery.ajax({
				type: 'POST',
				url: frontend_ajax_object.ajaxurl,
				data: {
					action: "export_registered_users_in_zipcode",
					zip_code: zip_code,
				},
				success: function (data) {
					console.log(data);

					/*
					* Make CSV downloadable
					*/
					var downloadLink = document.createElement("a");
					var fileData = ['\ufeff'+data];

					var blobObject = new Blob(fileData,{
						type: "text/csv;charset=utf-8;"
					});

					var url = URL.createObjectURL(blobObject);
					downloadLink.href = url;
					downloadLink.download = "cd-"+ zip_code+".csv";

					/*
					* Actually download CSV
					*/
					document.body.appendChild(downloadLink);
					downloadLink.click();
					document.body.removeChild(downloadLink);





				},
				error: function (xhr, ajaxOptions, thrownError) {
					alert(thrownError);
				}
			});
			
		});
	});

})( jQuery );
