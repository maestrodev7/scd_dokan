jQuery(document).ready(function () {
    //--------------------------------------------------------------------------------------------
    // set base currency if vendor select Base currency only 
    jQuery(document).on('change', '#scd-currency-option', function (e) {
        e.preventDefault();
        if (jQuery(this).children("option:selected").val().indexOf('base-currency') !== -1) {
            jQuery('#scd-currency-list').val(settings.baseCurrency);
        }
    });
    // use currency on default currency only if vendor select other currency
    jQuery(document).on('change', '#scd-currency-list', function (e) {
        e.preventDefault();
        if (jQuery(this).children("option:selected").val() !== settings.baseCurrency) {
            jQuery('#scd-currency-option').val('only-default-currency');
        }
    });
    //-----------------------------------------------------------------------------

	var data = {
         'action': 'scd_dokan_get_user_currency'
	}
	jQuery.post(scd_ajax.ajax_url, data, function(response) {
		console.log(response);
		
		                           localStorage.response = response;
            let user_currency0 = ((response.replace(/[0-9]/g, '')).replace(/\s/g, '')).toString();
            if ((response.indexOf('FALSE0') === -1)) {
                // dokan-support-listing dokan-support-topic-wrapper
                var user_currency = response.split('<')[0].replace(' ', '').toString()[0] + response.split('<')[0].replace(' ', '').toString()[1] + response.split('<')[0].replace(' ', '').toString()[2];
                localStorage.response = user_currency;
                var elements = jQuery('.dokan-dashboard .amount');
                var len = elements.length;
                if ((jQuery('#woocommerce-order-items')[0] === undefined)) {
                    for (var i = 0; i < len; i++) {
                        if (user_currency !== "") {
                            scd_simpleConvert(elements[i], user_currency0, i);
                        } else {
                            scd_simpleConvert(elements[i], user_currency, i);
                        }
                    }
                }
            }
		
	});




    jQuery(document).on('click', '#scd-save-currency-option', function (e) {
        e.preventDefault();
		
        var user_currency_option = jQuery('#scd-currency-option').val();
		
		   var data = {
                'action': 'scd_update_user_currency_option',
                'user_currency_option': user_currency_option
			}
			jQuery.post(scd_ajax.ajax_url, data, function(response) {
				console.log(response);
				jQuery('#scd-action-status').html(response);
			});

        // save user currency
        var user_currency = jQuery('#scd-currency-list').val();
		
	   var data = {
					'action': 'scd_update_user_currency',
					'user_currency': user_currency
		}
		jQuery.post(scd_ajax.ajax_url, data, function(response) {
			console.log(response);
			jQuery('#scd-action-status').html(response);
		});
    });


    //////// Start set reconvert shipping flat rate cost ///////
    jQuery(document).on('click', '.edit', function () {
        var user_currency = localStorage.response;
        var base = settings.baseCurrency;
        jQuery('#method_cost').val(Math.round(price_converter(jQuery('#method_cost').val(), user_currency, base)));
    });
    jQuery(document).on('click', 'select#method_tax_status option', function () {
        var user_currency = localStorage.response;
        var base = settings.baseCurrency;
        setTimeout(function () { jQuery('#method_cost').val(Math.round(price_converter(jQuery('#method_cost').val(), user_currency, base))); }, 1000);
    });
    //////// End set reconvert shipping flat rate cost ///////

});


jQuery(window).load(function () {


});