/* ------------------------------------------------------------------------------------
   This module contains javascripts functions used only for the SCD multivendor functionality.
   ----------------------------------------------------------------------------------- */
function scd_Convert_wholesale_price(obj, curr) {
var base = settings.baseCurrency;
if (jQuery(obj).attr('basecurrency') !== undefined) {
base = jQuery(obj).attr('basecurrency');
}

if(base == curr){
console.log('base currency== target');
// no conversion to perform
return;
}

var rate = scd_get_convert_rate(base, curr);

// Check if there is a manual override options defined for this currency
if(jscd_options.customCurrencyOptions !== "" && jscd_options.customCurrencyOptions[curr] !==undefined) {
var currency_options = jscd_options.customCurrencyOptions[curr];

// If a custom exchange rate has been specified, use it
if((base===settings.baseCurrency) && (currency_options["rate"] !== "")){
rate = parseFloat(currency_options.rate);
}

// If an increase on top percentage has been specified, apply it
if(currency_options["inc"] !== ""){
rate = rate* (1 + parseFloat(currency_options["inc"])/100);
}
}

var price= jQuery(obj).html();
   
 price =  price.match(/\d+(?:\.\d+)?/g) ;

price = price * rate;
price = price.toFixed(jscd_options.decimalPrecision);
price = scd_humanizeNumber(price);

// Add currency symbol

var currency_attributes = scd_get_currency_symbol(curr);

if((jscd_options.useCurrencySymbol) && (currency_attributes.symbol !== undefined)) {

currency_symbol = '<span class="woocommerce-Price-currencySymbol">' + currency_attributes.symbol + '</span>';

currency_code = '<span class="woocommerce-Price-currencySymbol">' + scd_getTargetCurrency() + '</span>';

switch (currency_attributes.position) {
case 'left':
price = currency_symbol + price;
break;
case 'right':
price = price + currency_symbol;
break;
case 'left_space':
price = currency_symbol + ' ' + price;
break;
case 'right_space':
price = price + ' ' + currency_symbol;
break;
case 'left_country':
price = currency_code + ' ' + price + currency_symbol;
break;
case 'right_country':
price = currency_symbol + price + ' ' + currency_code;
break;
default:
price = price + currency_symbol;
break;
}
}
else
{
price = price + '<span class="scd-currency-symbol">' + ' ' + curr + '</span>';
}

jQuery(obj).html(price);

jQuery(obj).attr('basecurrency', curr); // this ensures that we will not convert this element again

}

jQuery(document).ready(function () {

jQuery( ".variations_form" ).on( "woocommerce_variation_select_change", function () {
    // Fires whenever variation selects are changed
} );

jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
    // Fired when the user selects all the required dropdowns / attributes
    // and a final variation is selected / shown
    var target_currency = scd_getTargetCurrency();
   if ( scd_isConvertRateAvailable(target_currency) ) {
    var elts=jQuery('.woocommerce-variation-wholesale strong:eq(0)');
            for (var i=0;i<elts.length;i++){
            //if(elts!==undefined){
               scd_Convert_wholesale_price(elts[i],target_currency);    
           //}
        }
  }
} );

//wholesale price conversion
  var tc = scd_getTargetCurrency();
   if ( scd_isConvertRateAvailable(tc) ) {
    var elts=jQuery('.woocommerce-variation-wholesale strong:eq(0)');
    if(elts!==undefined){
   // scd_Convert_wholesale_price(elts[0],tc);    
    }
  }
  
 /* 
jQuery('.scd_dokan_menu a').click(function (e) {
        e.preventDefault();
        //jQuery('.scd_dokan_menu').toggleClass('active');
        jQuery('.scd_dokan_menu').toggleClass('active').siblings().removeClass('active');
		
		 jQuery.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            'action': 'scd_show_user_currency',
            }
        }).done(function(response) {
                              // dokan-support-listing dokan-support-topic-wrapper
                    jQuery('.dokan-dashboard-content').html(response);
                    console.log(jQuery('#scd-currency-list'));
                    if((localStorage.response.indexOf('FALSE0')!==-1)){
                        jQuery('#scd-currency-list').val(settings.baseCurrency);
                    }
        });
		
    });
    */
	
   jQuery('#scd-wcv-select-currencies').val(null).trigger('change');
    jQuery(document).on("click", ".scd_wcv_select",function () {
        var key = '';
       
        var newKeys, oldKeys;

        oldKeys = jQuery('#scd-bind-select-curr').val().toString().split(',');

        if (!jQuery('#scd-wcv-select-currencies').val() == '')
            newKeys = jQuery('#scd-wcv-select-currencies').val().toString().split(',');
        else {
            newKeys = '';
        }

        if (jQuery('#scd-bind-select-curr').val() !== '') {

            if (newKeys.length >= oldKeys.length) {

                if (newKeys.length > 0) {
                    key = newKeys[newKeys.length - 1];
                    for (var id = 0; id < newKeys.length; id++) {
                        if (oldKeys.includes(newKeys[id]) == false)
                            key = newKeys[id];
                    }
                }

                var myregselect = '<option id="reg_' + key + '" value=' + key + ' >Regular price (' + key + ')</option>';
                var mysalselect = '<option id="reg_' + key + '" value=' + key + ' >Sale price (' + key + ')</option>';
                jQuery('#scd_regularCurrency').append(myregselect);
                jQuery('#scd_saleCurrency').append(mysalselect);
                jQuery('#scd-bind-select-curr').val(jQuery('#scd-wcv-select-currencies').val());

            } else {
                for (var k = 0; k < oldKeys.length; k++) {
                    if (newKeys.indexOf(oldKeys[k]) == -1) {
                        jQuery('#scd_regularCurrency option[value="' + oldKeys[k] + '"]').remove();
                        jQuery('#scd_saleCurrency option[value="' + oldKeys[k] + '"]').remove();
                    }
                }
            }
            jQuery('#scd-bind-select-curr').val(jQuery('#scd-wcv-select-currencies').val());
        } else {
            if (newKeys.length > 0) {
                key = newKeys[newKeys.length - 1];
            }
            var myregselect = '<option id="reg_' + key + '" value=' + key + ' >Regular price (' + key + ')</option>';
            var mysalselect = '<option id="sale_' + key + '" value=' + key + ' >Sale price (' + key + ')</option>';
            jQuery('#scd_regularCurrency').append(myregselect);
            jQuery('#scd_saleCurrency').append(mysalselect);
            jQuery('#scd-bind-select-curr').val(jQuery('#scd-wcv-select-currencies').val());

        }

        if (jQuery(this).val() !== null) {
            var tabCurr = jQuery(this).val().toString().split(',');
            if (tabCurr.length > 0) {
                var regularBloc = '';
                var saleBloc = '';
                var newpriceField = '';
                var priceField = jQuery('#priceField').val();
                var tabC;
                for (var i = 0; i < tabCurr.length; i++) {
                    regularBloc = 'regular_' + tabCurr[i] + '_';
                    saleBloc = '-sale_' + tabCurr[i] + '_';
                    var regularPrice = '', salePrice = '';
                    if (priceField.indexOf(regularBloc) > -1) {
                        regularPrice = priceField.substr(priceField.indexOf(regularBloc) + regularBloc.length,
                                priceField.indexOf(saleBloc) - priceField.indexOf(regularBloc) - regularBloc.length);

                        tabC = priceField.toString().split(',');
                        var pos = -1;
                        for (var j = 0; j < tabC.length; j++) {
                            if (tabC[j].indexOf('sale_' + tabCurr[i]) > -1) {
                                pos = j;
                            }
                        }

                        if (pos > -1) {
                            var tc = tabC[pos].toString().split('_');
                            if (tc.length > 0) {
                                salePrice = tc[tc.length - 1];
                            }
                        }
                    }
                    if (i == 0) {
                        newpriceField = 'regular_' + tabCurr[i] + '_' + regularPrice + '-sale_' + tabCurr[i] + '_' + salePrice;
                    } else {
                        newpriceField = newpriceField + ',regular_' + tabCurr[i] + '_' + regularPrice + '-sale_' + tabCurr[i] + '_' + salePrice;
                    }
                }
                jQuery('#priceField').val(newpriceField);
            }
        }
    });

    // binding '#scd_regularCurrency' and #scd_saleCurrency'
    jQuery('#scd_regularCurrency').change(function () {
        jQuery('#scd_saleCurrency').val(jQuery('#scd_regularCurrency').val()).change();
        //jQuery('#scd_regularPriceCurrency').val( jQuery('#regularField_'+jQuery('#scd_regularCurrency').val()).val());
        //jQuery('#scd_salePriceCurrency').val( jQuery('#saleField_'+jQuery('#scd_saleCurrency').val()).val());
        var priceField = jQuery('#priceField').val();

        var regularBloc = 'regular_' + jQuery('#scd_regularCurrency').val() + '_';
        var saleBloc = '-sale_' + jQuery('#scd_regularCurrency').val() + '_';
        var price = priceField.substr(priceField.indexOf(regularBloc) + regularBloc.length,
                priceField.indexOf(saleBloc) - priceField.indexOf(regularBloc) - regularBloc.length);
        jQuery('#scd_regularPriceCurrency').val(price);

        var tabCurr = priceField.toString().split(',');
        var pos = -1;
        for (var j = 0; j < tabCurr.length; j++) {
            if (tabCurr[j].indexOf('sale_' + jQuery('#scd_saleCurrency').val()) > -1) {
                pos = j;
            }
        }

        if (pos > -1) {
            var tc = tabCurr[pos].toString().split('_');
            if (tc.length > 0) {
                jQuery('#scd_salePriceCurrency').val(tc[tc.length - 1]);

            }
        }

    });
    // end binding

    // start save regular price entered for each currency when hoverout field  
    jQuery('#scd_regularPriceCurrency').focusout(function () {
        // jQuery('#regularField_'+jQuery('#scd_regularCurrency').val()).val(jQuery(this).val());

        var priceField = jQuery('#priceField').val();
        var regularBloc = 'regular_' + jQuery('#scd_regularCurrency').val() + '_';
        var saleBloc = '-sale_' + jQuery('#scd_regularCurrency').val() + '_';

        priceField = priceField.substr(0, priceField.indexOf(regularBloc)) + regularBloc + jQuery(this).val() +
                priceField.substr(priceField.indexOf(saleBloc));
        jQuery('#priceField').val(priceField);

    });
    // end save regular price

    // start save sale price entered for each currency when hoverout field  
    jQuery('#scd_salePriceCurrency').focusout(function () {
        //jQuery('#saleField_'+jQuery('#scd_saleCurrency').val()).val(jQuery(this).val());

        var priceField = jQuery('#priceField').val();
        var tabCurr = priceField.toString().split(',');
        var pos = -1;
        for (var j = 0; j < tabCurr.length; j++) {
            if (tabCurr[j].indexOf('sale_' + jQuery('#scd_saleCurrency').val()) > -1) {
                pos = j;
            }
        }
        if (pos > -1) {
            tabCurr[pos] = tabCurr[pos].substr(0, tabCurr[pos].indexOf('sale')) + 'sale_' + jQuery('#scd_saleCurrency').val() + '_' + jQuery(this).val();
            priceField = tabCurr[0];
            for (var j = 1; j < tabCurr.length; j++) {
                priceField = priceField + ',' + tabCurr[j];
            }

            jQuery('#priceField').val(priceField);
        }
    });
    // end save sale price
});

/*<?php
/* -------------------------------------------------------
  This module contains functions used only for the SCD multivendor functionality.
  It is included by the index.php file.
  ------------------------------------------------------- *

//    add_action('dokan_get_all_cap','scd_dokan_capability');
//    function scd_dokan_capability($capabilities) {
//        $capabilities['menu']['dokan_view_scd_currency_menu']=__( 'View scd currency menu', 'dokan-lite' );
//        return $capabilities;
//    }

function scd_save_product_prices($post_id, $data) {
    $scd_userRole = scd_get_user_role();
    $scd_userID = get_current_user_id();
    $scd_currencyVal = '';
    if (isset($data['scd_currencyVal'])) {
        //if ($_POST['scd_currencyVal'] !== '') {
        $scd_currencyVal = $data['scd_currencyVal'];
        //}
    }

    $priceField = '';
    if (isset($data['priceField'])) {
        if ($data['priceField'] !== '') {
            $priceField = $data['priceField'];
        }
    }
    // save data
    $user_curr = scd_get_user_currency();
    if ($user_curr !== FALSE && isset($data['scd_sale_price'])) {
        $scd_currencyVal = $user_curr;
        $priceField = 'regular_' . $scd_currencyVal . '_' . $data['scd_regular_price'] . '-sale_' . $scd_currencyVal . '_' . $data['scd_sale_price'];
    }

    $curr_opt = scd_get_user_currency_option();
    if ($user_curr !== FALSE && $user_curr !== get_option('woocommerce_currency') && $curr_opt == 'only-default-currency') {
        $scd_currencyVal = $user_curr;
        $priceField = 'regular_' . $scd_currencyVal . '_' . $data['regular_price'] . '-sale_' . $scd_currencyVal . '_' . $data['sale_price'];
        //save the equivalent price entered by user in base currency
        $converted = scd_function_convert_subtotal($data['regular_price'], get_option('woocommerce_currency'), $scd_currencyVal, 18, TRUE);

        update_post_meta($post_id, '_regular_price', $converted);
        if ($data['sale_price'] !== '') {
            $converted = scd_function_convert_subtotal($data['sale_price'], get_option('woocommerce_currency'), $scd_currencyVal, 18, TRUE);
            update_post_meta($post_id, '_sale_price', $converted);
            update_post_meta($post_id, '_price', $converted);
        } else {
            update_post_meta($post_id, '_price', $converted);
        }
    } elseif ($user_curr !== FALSE) {
        
    }
    if ($priceField !== '')
        update_post_meta($post_id, 'scd_other_options', array(
            "currencyUserID" => $scd_userID,
            "currencyUserRole" => $scd_userRole,
            "currencyVal" => $scd_currencyVal,
            "currencyPrice" => $priceField
        ));
}

//dokan marketplace plugin
add_action('dokan_get_dashboard_nav', 'scd_dokan_menu', 10, 1);

function scd_dokan_menu($menu) {

    $menu['scd_dokan_menu'] = array(
        'title' => __('SCD Currency', 'dokan-lite'),
        'icon' => '<i class="fa fa-cog"></i>',
        'url' => '#',
        'pos' => 70,
        'permission' => 'dokan_view_overview_menu'
    );
    return $menu;
}

add_action('dokan_after_new_product_content_area', 'scd_dokan_fields', 11);
add_action('dokan_product_edit_after_main', 'scd_dokan_fields', 12);

function scd_dokan_fields() {
    global $post;
    $regprice = '';
    $saleprice = '';
    $wholesale_price = '';
    $earning='';
    $scd_curr = get_post_meta($post->ID, 'scd_other_options', true);
    $currencyVal = '';
    if (isset($scd_curr['currencyVal'])) {
        $currencyVal = $scd_curr['currencyVal'];
    }
    $user_curr = scd_get_user_currency();
    $user_curr_opt = scd_get_user_currency_option();
    if ($user_curr == FALSE || $user_curr_opt == 'selected-currencies') {
        ob_start();
        ?>
        <h6 style="margin-right: -15px;">Set currency : </h6>
        <input type="hidden" name="scd_currencyVal" id="scd-bind-select-curr" value="<?php echo $currencyVal; ?>"/>
        <select data-placeholder="Set currency by product" class="scd_wcv_select" multiple="true" 
                id="scd-wcv-select-currencies" name="scd_currencyVal_seleted">
            <optgroup label="Fixed currencies">
                <?php
                foreach (scd_get_list_currencies() as $key => $val) {
                    if (strpos($currencyVal, $key) !== false) {
                        echo "<option selected='true'  value='$key' >$key - $val</option>";
                    } else {
                        echo "<option  value='$key' >$key - $val</option>";
                    }
                    // }
                }
                ?>
            </optgroup>
        </select>
        <p id="bind-scd-price-curr"></p>      
        <?php
        scd_custom_product_general_fields();
        $contents = ob_get_contents();
        ob_end_clean();
    } else {
        $curr_symbol = get_woocommerce_currency_symbol($user_curr);
        list($regprice, $saleprice) = scd_get_product_custom_price_for_currency($post->ID, $user_curr);
        if (empty($regprice)) {
            $regprice = get_post_meta($post->ID, '_regular_price', true);
            if (!empty($regprice))
                $regprice = scd_function_convert_subtotal($regprice, get_option('woocommerce_currency'), $user_curr, 2);
            $saleprice = get_post_meta($post->ID, '_sale_price', true);
            if (!empty($saleprice))
                $saleprice = scd_function_convert_subtotal($saleprice, get_option('woocommerce_currency'), $user_curr, 2);
        }
        if ($user_curr_opt == 'base-and-default-currency') {
            $contents = ' <div class="content-half-part">
                                                <label for="scd_regular_price" class="dokan-form-label">SCD Price</label>
                                                <div class="dokan-input-group">
                                                    <span class="dokan-input-group-addon">' . $curr_symbol . '</span>
                                                    <input type="number" class="dokan-form-control dokan-product-regular-price" name="scd_regular_price" placeholder="0.00" value="' . $regprice . '" min="0" step="any">
                                                </div>
                                            </div>
                                            <div class="content-half-part sale-price">
                                                <label for="scd_sale_price" class="form-label">
                                                    SCD Discounted Price  </label>
                                                <div class="dokan-input-group">
                                                    <span class="dokan-input-group-addon">' . $curr_symbol . '</span>
                                                    <input type="number" class="dokan-form-control dokan-product-sales-price" name="scd_sale_price" placeholder="0.00" value="' . $regprice . '" min="0" step="any">
                                                </div>
                                            </div>';
        } elseif ($user_curr_opt == 'only-default-currency') {
            $contents = '';
            $wholesale_price = get_post_meta($post->ID, 'scd_dokan_wholesale_meta', true);
            
            $product= wc_get_product($post->ID);
            if ($product)
            $earning=dokan()->commission->calculate_commission($post->ID, $saleprice );
        } else { //base vurrency
            $contents = '';
        }
    }
    $contents = str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string) $contents), "\0..\37'\\")));
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            var currencyVal = "<?php echo $currencyVal; ?>";
            var tab = {};
            if (currencyVal.trim() !== '')
                tab = currencyVal.split(',');
            var curr_opt = "<?php echo $user_curr_opt; ?>";
            var cont = "<?php echo $contents; ?>";
            //n content to append
            // set price in selected currencies or in user defqult currency + base currency 
            if (cont.trim().length > 0) {
                if (jQuery('#scd-wcv-select-currencies').val() == undefined) {
                    jQuery('.dokan-price-container').first().append(cont);
                }
                jQuery('#scd_regularCurrency').empty();
                jQuery('#scd_saleCurrency').empty();
                for (var i = 0; i < tab.length; i++) {
                    jQuery('.scd_wcv_select[value="' + tab[i] + '"]').attr('selected', true);
                    var myregselect = '<option id="reg_' + tab[i] + '" value=' + tab[i] + ' >Regular price (' + tab[i] + ')</option>';
                    var mysalselect = '<option id="sale_' + tab[i] + '" value=' + tab[i] + ' >Sale price (' + tab[i] + ')</option>';

                    jQuery('#scd_regularCurrency').append(myregselect);
                    jQuery('#scd_saleCurrency').append(mysalselect);
                }
                jQuery(".scd_wcv_select").data("placeholder", "Set currency per product...").chosen();
                jQuery(".scd_wcv_select").click(function () {

                });
            } else if (curr_opt == 'only-default-currency') {
                var symb='<?php echo $curr_symbol; ?>';
                jQuery('.dokan-input-group-addon').html('<?php echo $curr_symbol; ?>');
                jQuery('#dokan-wholesale-price').siblings('label').html('<?php echo'Wholesale Price:(' . $curr_symbol . ')'; ?>');
                jQuery('.dokan-product-regular-price').val('<?php echo $regprice; ?>');
                jQuery('.dokan-product-sales-price').val('<?php echo $saleprice; ?>');
                jQuery('#dokan-wholesale-price').val('<?php echo $wholesale_price; ?>');
               
                var earn= '<?php echo $earning; ?>';
                jQuery('.vendor-earning').html('(  You Earn : '+symb+'  <span class="vendor-price">'+earn+'</span>)');
            }

            //jQuery(".scd_wcv_select").val(currencyVal);
        });
    </script>
    <?php
}

add_action('dokan_product_updated', 'scd_save_wholesale_data', 99);

function scd_save_wholesale_data($post_id) {
    if (!$post_id) {
        return;
    }

    if (!is_user_logged_in()) {
        return;
    }

    if (!isset($_POST['wholesale'])) {
        return;
    }

    $user_curr = scd_get_user_currency();
    $curr_opt = scd_get_user_currency_option();
    if ($user_curr == FALSE || $curr_opt !== 'only-default-currency') {
        return;
    }
    $price = !empty($_POST['wholesale']['price']) ? wc_format_decimal($_POST['wholesale']['price']) : '';
    update_post_meta($post_id, 'scd_dokan_wholesale_meta', $price);
    if ($price != '') {
        $price = scd_function_convert_subtotal($price, get_option('woocmmerce_currency'), $user_curr, 2, true);
    }
    $wholesale_data = [
        'enable_wholesale' => !empty($_POST['wholesale']['enable_wholesale']) ? sanitize_text_field($_POST['wholesale']['enable_wholesale']) : 'no',
        'price' => $price,
        'quantity' => !empty($_POST['wholesale']['quantity']) ? sanitize_text_field($_POST['wholesale']['quantity']) : 0
    ];

    update_post_meta($post_id, '_dokan_wholesale_meta', $wholesale_data);
}

add_action('dokan_new_product_added', 'scd_dokan_save_meta', 12, 2);
add_action('dokan_product_updated', 'scd_dokan_save_meta', 12, 2);

function scd_dokan_save_meta($post_id, $data) {
    if (isset($_POST))
        $data = $_POST;
    $data['regular_price'] = $data['_regular_price'];
    $data['sale_price'] = $data['_sale_price'];
    scd_save_product_prices($post_id, $data);
}

/*
 *  manage variable product with dokan
 *

//apply_filters( 'dokan_prepare_for_calculation', $earning, $commission_rate, $commission_type, $additional_fee, $product_price, $this->order_id );
//add_filter('dokan_prepare_for_calculation','scd_dokan_vendor_earning',10,6);
function scd_dokan_vendor_earning($earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id) {
    $user_curr = scd_get_user_currency();
    $user_curr_opt = scd_get_user_currency_option();
    if ($user_curr !== FALSE && $user_curr_opt == 'only-default-currency') {
        $earning = scd_function_convert_subtotal($earning, get_option('woocommerce_currency'), $user_curr, 2);
    }
    return $earning;
}

//product variation
add_action('dokan_variation_options_pricing', 'scd_dokan_variable_product', 10, 3);

function scd_dokan_variable_product($loop, $variation_data, $variation) {
    $user_curr = scd_get_user_currency();
    $user_curr_opt = scd_get_user_currency_option();
    $curr_symbol = get_woocommerce_currency_symbol();
    $base_curr = get_woocommerce_currency_symbol();
    $regprice = '';
    $saleprice = '';
    $wholesale_price = '';
    if ($user_curr !== FALSE && $user_curr_opt == 'only-default-currency') {
        $curr_symbol = get_woocommerce_currency_symbol($user_curr);
        list($regprice, $saleprice) = scd_get_product_custom_price_for_currency($variation->ID, $user_curr);
        if (empty($regprice)) {
            $regprice = get_post_meta($variation->ID, '_regular_price', true);
            $regprice = scd_function_convert_subtotal($regprice, get_option('woocommerce_currency'), $user_curr, 2);
            $saleprice = get_post_meta($variation->ID, '_sale_price', true);
            $saleprice = scd_function_convert_subtotal($saleprice, get_option('woocommerce_currency'), $user_curr, 2);
        }
        $wholesale_price = get_post_meta($variation->ID, 'scd_dokan_wholesale_meta', true);
    }
    echo'<script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery(\'input[name="variable_regular_price[' . $loop . ']"]\').val('.get_post_meta($variation->ID, '_regular_price', true).');
                jQuery(\'input[name="variable_sale_price[' . $loop . ']"]\').val('.get_post_meta($variation->ID, '_sale_price', true).');
                jQuery(\'input[name="variable_regular_price[' . $loop . ']"]\').attr("type", "hidden");
                jQuery(\'input[name="variable_sale_price[' . $loop . ']"]\').attr("type", "hidden");
                jQuery(\'input[name="variable_regular_price[' . $loop . ']"]\').attr("id", "destination_variable_regular_price'.$loop.'");
                jQuery(\'input[name="variable_sale_price[' . $loop . ']"]\').attr("id", "destination_variable_sale_price'.$loop.'");
                jQuery("#source_variable_regular_price'.$loop.'").keyup(function (){
                    var src= document.getElementById("source_variable_regular_price'.$loop.'"); 
                    var dest= document.getElementById("destination_variable_regular_price'.$loop.'"); 
                    dest.value=src.value * '.scd_function_convert_subtotal(1, $user_curr,  get_option('woocommerce_currency'), 18).'; 
                });
                jQuery("#source_variable_sale_price'.$loop.'").keyup(function (){
                    var src= document.getElementById("source_variable_sale_price'.$loop.'"); 
                    var dest= document.getElementById("destination_variable_sale_price'.$loop.'"); 
                    dest.value=src.value * '.scd_function_convert_subtotal(1, $user_curr, get_option('woocommerce_currency'), 18).'; 
                });
            });
        </script>';
    echo '<input type="hidden" class="scd_variable_product_fill" value="1" name="scd_variable_regular_fill_' . $loop . '" />';
    echo '<input type="hidden" class="scd_variable_product_loop" value="' . $loop . '" name="scd_variable_regular_price_' . $loop . '" />';
    echo '<div class="dokan-form-group dokan-clearfix dokan-price-container">
            <div class="content-half-part">
                <div class="dokan-input-group">
                    <input type="text" size="100" id="source_variable_regular_price'.$loop.'" name="scd_variable_regular_price[' . $loop . ']" value="' . $regprice . '" class="wc_input_price dokan-form-control dokan-product-regular-price-variable" placeholder="Variation price (required)">
                </div>
            </div>
            <div class="content-half-part sale-price" style="margin-left:-10px;">
                <div class="dokan-input-group">
                    <input type="text" size="110" id="source_variable_sale_price'.$loop.'" name="scd_variable_sale_price[' . $loop . ']" value="' . $saleprice . '" class="wc_input_price dokan-form-control dokan-product-sales-price-variable">
                </div>
            </div>
        </div>';//variable_wholesale_price
    echo '<input type="hidden" id="scd_dokan_wholesale_price_' . $loop . '" class="scd_dokan_wholesale_price" value="' . $wholesale_price . '" />';
     $_product= wc_get_product($variation->ID);
      if ($_product)
     $earning=dokan()->commission->calculate_commission( $variation->ID, $saleprice ); 
    echo '<input type="hidden" id="scd_dokan_vendor_earning_' . $loop . '" class="scd_dokan_vendor_earning" value="' . $earning . '" />';
    ?>
    <script type="text/javascript">
        var curr_symb = "<?php echo $curr_symbol; ?>";
        var base_curr = "<?php echo $base_curr; ?>";

        jQuery('.variation-topbar-heading').click(function (e) {
            e.preventDefault();
            var selfs = jQuery(this);

            var is_filled = selfs.parent().find('.scd_variable_product_fill').val();

            if (is_filled == 1) {
                jQuery('.dokan-product-regular-price-variable').siblings('label').html('Regular price(' + curr_symb + ')');
                jQuery('.show_if_variation_wholesale label:even').html('Wholesale price (' + curr_symb + ')');
                var new_lab = 'Sale price (' + curr_symb + ') <a href="#" class="sale_schedule">Schedule</a><a href="#" class="cancel_sale_schedule" style="display:none">Cancel schedule</a>';
                var lab_sale = jQuery('.dokan-product-sales-price-variable').siblings('label').html().toString();
                base_curr = base_curr.toString();

                lab_sale = lab_sale.replace(base_curr, curr_symb);
                jQuery('.dokan-product-sales-price-variable').siblings('label').html(new_lab);
                
                setTimeout(function () {
                    //<input type="hidden" class="scd_variable_product_loop" value="'.$loop.'" name="scd_variable_regular_price_'.$loop.'" />
                    var loops = selfs.parent().find('.scd_variable_product_loop').val();
                    var regs = '#scd_dokan_regular_price_' + loops;
                    regs = jQuery(regs).val();
                    var sals = '#scd_dokan_sale_price_' + loops;
                    sals = jQuery(sals).val();
                    
                   var vendor_earn='#scd_dokan_vendor_earning_'+loops;
                   jQuery('.vendor-earning').html('(  You Earn : ' + curr_symb + '<span class="vendor-price">'+jQuery(vendor_earn).val()+'</span> )');
                
                    var wholesals = '#scd_dokan_wholesale_price_' + loops;
                    wholesals = jQuery(wholesals).val();

                    var myInput = [];
                    var iname;
                    myInput = document.getElementsByTagName("input");
                    iname = "variable_regular_price[" + loops + "]".toString();
                    /*for (var i = 0; i < myInput.length; i++) {
                        if (myInput[i].name === iname) {
                            myInput[i].value = regs;
                        }
                        //sale price
                        if (myInput[i].name === "variable_sale_price[" + loops + "]") {
                            myInput[i].value = sals;
                        }
                        //wholesale price
                        if (myInput[i].name === "variable_wholesale_price[" + loops + "]") {
                            myInput[i].value = wholesals;
                        }

                    }*
                    selfs.parent().find('.scd_variable_product_fill').val(0);
                }, 1000)

            }
        });

    </script>
    <?php
}

//save variations with dokan
add_action('woocommerce_save_product_variation', 'scd_dokan_save_variable_product', 999, 2);

//add_action( 'dokan_save_product_variation', 'scd_dokan_save_variable_product',10,2);
function scd_dokan_save_variable_product($variation_id, $i) {
    if (!isset($_POST['action']) || (isset($_POST['action']) && $_POST['action'] !== 'dokan_save_variations'))
        return;
    $scd_userRole = scd_get_user_role();
    $scd_userID = get_current_user_id();
    $variation_ids = array();
    $data = array();
    //parse_str( $_POST['formdata'], $data );
    $data = $_POST;
    $variable_post_id = isset($data['variable_post_id']) ? $data['variable_post_id'] : $variation_ids;
    $max_loop = max(array_keys($variable_post_id));

    $variable_regular_price = isset($data['scd_variable_regular_price']) ? $data['scd_variable_regular_price'] : array();
    $variable_sale_price = isset($data['scd_variable_sale_price']) ? $data['scd_variable_sale_price'] : array();
    //variable_wholesale_price
    $variable_wholesale_price = isset($data['variable_wholesale_price']) ? $data['variable_wholesale_price'] : array();

    $regular_price = wc_format_decimal($variable_regular_price[$i]);
    $sale_price = ( $variable_sale_price[$i] === '' ? '' : wc_format_decimal($variable_sale_price[$i]) );

    // save data
    $user_curr = scd_get_user_currency();
    $curr_opt = scd_get_user_currency_option();
    if ($user_curr !== FALSE && $curr_opt == 'only-default-currency') {
        $scd_currencyVal = $user_curr;
        $priceField = 'regular_' . $scd_currencyVal . '_' . $regular_price . '-sale_' . $scd_currencyVal . '_' . $sale_price;
        //save the equivalent price entered by user in base currency
        $converted = scd_function_convert_subtotal($regular_price, get_option('woocommerce_currency'), $scd_currencyVal, 18, TRUE);
        update_post_meta($variation_id, '_regular_price', $converted);
        if ($sale_price !== '') {
            $converted = scd_function_convert_subtotal($sale_price, get_option('woocommerce_currency'), $scd_currencyVal, 18, TRUE);
            update_post_meta($variation_id, '_sale_price', $converted);
            update_post_meta($variation_id, '_price', $converted);
        } else {
            update_post_meta($variation_id, '_price', $converted);
        }
    }
    if ($priceField !== '')
        update_post_meta($variation_id, 'scd_other_options', array(
            "currencyUserID" => $scd_userID,
            "currencyUserRole" => $scd_userRole,
            "currencyVal" => $scd_currencyVal,
            "currencyPrice" => $priceField
        ));
    //}
}

add_action('dokan_process_product_meta', 'scd_save_variation_wholesale_data', 199);
add_action('dokan_ajax_save_product_variations', 'scd_save_variation_wholesale_data', 99);

function scd_save_variation_wholesale_data($post_id) {
    if (!$post_id) {
        return;
    }

    if (!is_user_logged_in()) {
        return;
    }

    if (!isset($_POST['variable_wholesale_enable'])) {
        return;
    }
    $user_curr = scd_get_user_currency();
    $curr_opt = scd_get_user_currency_option();
    if ($user_curr == FALSE || $curr_opt !== 'only-default-currency') {
        return;
    }
    $data = [];
    $original_prices = [];
    foreach ($_POST['variable_wholesale_price'] as $loop => $price) {
        $original_prices[$loop] = $price;
        $price = scd_function_convert_subtotal($price, get_option('woocommerce_currency'), $user_curr, 2, TRUE);
        $data[$loop] = [
            'enable_wholesale' => !empty($_POST['variable_wholesale_enable'][$loop]) ? sanitize_text_field($_POST['variable_wholesale_enable'][$loop]) : 'no',
            'price' => wc_format_decimal($price),
            'quantity' => !empty($_POST['variable_wholesale_quantity'][$loop]) ? sanitize_text_field($_POST['variable_wholesale_quantity'][$loop]) : 0
        ];
    }

    foreach ($data as $key => $wholesale_data) {
        update_post_meta($_POST['variable_post_id'][$key], '_dokan_wholesale_meta', $wholesale_data);
        update_post_meta($_POST['variable_post_id'][$key], 'scd_dokan_wholesale_meta', $original_prices[$key]);
    }
}

//apply_filters( 'woocommerce_get_formatted_order_total', $formatted_total, $this, $tax_display, $display_refunded );

add_action('dokan_order_inside_content', 'scd_order_main_content', 31);

function scd_order_main_content() {
    ?>
    <style type="text/css">
        td.dokan-order-earning span.amount,td.dokan-order-total span.amount{display: none;}
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            var order_select = jQuery('td.dokan-order-select input');
            var nbrows = order_select.length;
            var order_list = '';
            for (var ind = 0; ind < nbrows; ind++) {
                order_list = order_list + jQuery(order_select[ind]).val() + ',';
            }

            jQuery.post(
                    ajaxurl,
                    {
                        action: 'scd_dokan_get_order_total_and_earning',
                        order_list: order_list
                    },
            function (response) {
                if (response.success) {
                    var leng = response.data.length;
                    var earns = jQuery('td.dokan-order-earning');
                    var totals = jQuery('td.dokan-order-total');
                    for (var i = 0; i < leng; i++) {
                        jQuery(totals[i]).html(response.data[i].total);
                        jQuery(earns[i]).html(response.data[i].earning);
                    }
                }
                jQuery('td span.amount').css('display', 'block');
            }
            );

        });

    </script>
    <?php
}

add_action('wp_ajax_scd_dokan_get_order_total_and_earning', 'scd_dokan_get_order_total_and_earning');

function scd_dokan_get_order_total_and_earning() {
    if (isset($_POST['order_list'])) {
        $order_list = explode(',', $_POST['order_list']);
        $resp = array();////=
        $args['decimals'] = scd_options_get_decimal_precision();
        $args['price_format'] = get_woocommerce_price_format();
        foreach ($order_list as $order_id) {
            if ($order_id !== '') {
                $order = wc_get_order($order_id);
                $args['currency'] = $order->get_currency();
                $args['price_format'] = scd_change_currency_display_format($args['price_format'], $order->get_currency());
                $resp[] = array(
//                       'currency'=> get_woocommerce_currency_symbol($order->get_currency()),
                    'total' => scd_format_converted_price_to_html($order->get_total(), $args),
                    'earning' => scd_format_converted_price_to_html(dokan()->commission->get_earning_by_order($order), $args)
                );
            }
        }
        wp_send_json_success($resp);
    }
    wp_send_json_success();
}

//add_action( 'dokan_seller_dashboard_widget_counter' ,'scd_dokan_seller_dashboard_widget_counter');
function scd_dokan_seller_dashboard_widget_counter() {
    ?>
    <style type="text/css">
        .big-counter ul li div.count span.amount{display: none;}
    </style>   
    <script type="text/javascript">

    </script>
    <?php
}

//return apply_filters( 'dokan_seller_total_sales', $earnings );
//includes/functions.php
add_filter('dokan_seller_total_sales', 'scd_dokan_seller_total_sales', 9999, 1);

function scd_dokan_seller_total_sales($total_sales) {
    global $wpdb;
    $seller_id = get_current_user_id();

    $cache_group = 'dokan_seller_data_' . $seller_id;
    $cache_key = 'dokan-earning-' . $seller_id;
    $total_sales = wp_cache_get($cache_key, $cache_group);

    $orders = $wpdb->get_results("SELECT order_id, order_total FROM {$wpdb->prefix}dokan_orders WHERE seller_id = " . $seller_id . " AND order_status IN('wc-completed', 'wc-processing', 'wc-on-hold')");
    $basecurrency = get_option('woocommerce_currency');
    $totals = 0;
    foreach ($orders as $row) {

        $currency = get_post_meta($row->order_id, '_order_currency', true);

        if ($currency == $basecurrency) {
            $totals = $totals + $row->order_total;
        } else {
            $totals = $totals + scd_function_convert_subtotal($row->order_total, $basecurrency, $currency, 2, true);
        }
    }
    $total_sales = $totals;
    wp_cache_set($cache_key, $total_sales, $cache_group);
    dokan_cache_update_group($cache_key, $cache_group);

    return $total_sales;
}*/