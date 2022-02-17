<?php
/* -------------------------------------------------------
  This module contains functions used only for the SCD multivendor functionality.
  It is included by the index.php file.
  ------------------------------------------------------- */

//    add_action('dokan_get_all_cap','scd_dokan_capability');
//    function scd_dokan_capability($capabilities) {
//        $capabilities['menu']['dokan_view_scd_currency_menu']=__( 'View scd currency menu', 'dokan-lite' );
//        return $capabilities;
//    }

function scd_save_product_prices($post_id, $data)
{
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

        //saving product price in for the product list
        update_post_meta($post_id, '_list_regular_price', $data['regular_price']);
        update_post_meta($post_id, '_list_sale_price', $data['sale_price']);

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
/*
//dokan marketplace plugin
add_action('dokan_get_dashboard_nav', 'scd_dokan_menu', 10, 1);

function scd_dokan_menu($menu)
{

    $menu['scd_dokan_menu'] = array(
        'title' => __('SCD Currency', 'dokan-lite'),
        'icon' => '<i class="fa fa-cog"></i>',
        'url' => '#',
        'pos' => 70,
        'permission' => 'dokan_view_overview_menu'
    );
    return $menu;
}
*/


add_filter( 'dokan_get_dashboard_nav',  'scd_dokan_menu_new' , 11, 1 );

    function scd_dokan_menu_new( $urls ) {
        $urls['scd_currency'] = array(
            'title' => __( 'SCD Currency', 'dokan-lite' ),
            'icon'  => '<i class="fa fa-cog"></i>',
            'url'   => dokan_get_navigation_url( 'scd_currency' ),
            'pos'   => 70,
			'permission' => 'dokan_view_overview_menu'
        );

        return $urls;
    }
	
add_filter( 'dokan_query_var_filter', 'register_scd_queryvar');

    function register_scd_queryvar( $vars ) {
        $vars[] = 'scd_currency';
        return $vars;
    }	
	
	
	add_filter( 'dokan_set_template_path', 'load_scd_templates' , 10, 3 );

    function load_scd_templates( $template_path, $template, $args ) {
		
        if ( isset( $args['is_scd_currency'] ) && $args['is_scd_currency'] ) {
					
            return WP_PLUGIN_DIR . '/scd_dokan_marketplace/templates';
        }

        return $template_path;
    }


add_action( 'dokan_load_custom_template', 'load_scd_currency_template');

    function load_scd_currency_template( $query_vars ) {

        if ( isset( $query_vars['scd_currency'] ) ) {
         		
                dokan_get_template_part( 'scd-currency-choose', '', array( 'is_scd_currency' => true ) );
				
                return;
        }
    }

/*	
add_filter( 'dokan_query_var_filter', 'dokan_load_scd_menu' );
function dokan_load_scd_menu( $query_vars ) {
    $query_vars['scd_currency'] = 'scd_currency';
    return $query_vars;
}
add_filter( 'dokan_get_dashboard_nav', 'dokan_add_scd_menu' );
function dokan_add_scd_menu( $urls ) {
    $urls['scd_currency'] = array(
        'title' => __( 'SCD Currency', 'dokan'),
        'icon'  => '<i class="fa fa-cog"></i>',
        'url'   => dokan_get_navigation_url( 'scd_currency' ),
        'pos'   => 70
    );
    return $urls;
}
add_action( 'dokan_load_custom_template', 'dokan_load_scd_template' );
function dokan_load_scd_template( $query_vars ) {
    if ( isset( $query_vars['scd_currency'] ) ) {
				
        WP_PLUGIN_DIR . '/scd_dokan_marketplace/templates/scd-currency-choose.php';
		
       }
}
*/


	
add_action('dokan_after_new_product_content_area', 'scd_dokan_fields', 11);
add_action('dokan_product_edit_after_main', 'scd_dokan_fields', 12);

function scd_dokan_fields()
{
    global $post;
    $regprice = '';
    $saleprice = '';
    $wholesale_price = '';
    $earning = '';
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
        <input type="hidden" name="scd_currencyVal" id="scd-bind-select-curr" value="<?php echo $currencyVal; ?>" />
        <select data-placeholder="Set currency by product" class="scd_wcv_select" multiple="true" id="scd-wcv-select-currencies" name="scd_currencyVal_seleted">
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

            $product = wc_get_product($post->ID);
            if ($product)
                $earning = dokan()->commission->calculate_commission($post->ID, $saleprice);
        } else { //base vurrency
            $contents = '';
        }
    }
    $contents = str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string) $contents), "\0..\37'\\")));
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
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
                jQuery(".scd_wcv_select").click(function() {

                });
            } else if (curr_opt == 'only-default-currency') {
                var symb = '<?php echo $curr_symbol; ?>';
                var salep = '<?php echo $saleprice; ?>';
                var regp = '<?php echo $regprice; ?>';
                jQuery('.dokan-input-group-addon').html('<?php echo $curr_symbol; ?>');
                jQuery('#dokan-wholesale-price').siblings('label').html('<?php echo 'Wholesale Price:(' . $curr_symbol . ')'; ?>');
                jQuery('.dokan-product-regular-price').val('<?php echo $regprice; ?>');

                if (regp != salep) {
                    jQuery('.dokan-product-sales-price').val('<?php echo $saleprice; ?>');
                }

                jQuery('#dokan-wholesale-price').val('<?php echo $wholesale_price; ?>');

                var earn = '<?php echo $earning; ?>';
                jQuery('.vendor-earning').html('(  You Earn : ' + symb + '  <span class="vendor-price">' + earn + '</span>)');
            }

            //jQuery(".scd_wcv_select").val(currencyVal);
        });
    </script>
<?php
}

add_action('dokan_product_updated', 'scd_save_wholesale_data', 99);

function scd_save_wholesale_data($post_id)
{
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
        $price = scd_function_convert_subtotal($price, get_option('woocommerce_currency'), $user_curr, 2, true);
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

function scd_dokan_save_meta($post_id, $data)
{
    if (isset($_POST))
        $data = $_POST;
    $data['regular_price'] = $data['_regular_price'];
    $data['sale_price'] = $data['_sale_price'];

    //Fixing compatibility in scd_for_dokan and woocommerce
    // $data['_sale_price'] = scd_function_convert_subtotal($data['_sale_price'], $user_curr, get_option('woocommerce_currency'), 18, true);
    // $data['_regular_price'] = scd_function_convert_subtotal($data['_regular_price'], $user_curr, get_option('woocommerce_currency') , 18, true);
    scd_save_product_prices($post_id, $data);
    return;
}

/*
 *  manage variable product with dokan
 */

//apply_filters( 'dokan_prepare_for_calculation', $earning, $commission_rate, $commission_type, $additional_fee, $product_price, $this->order_id );
//add_filter('dokan_prepare_for_calculation','scd_dokan_vendor_earning',10,6);
function scd_dokan_vendor_earning($earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id)
{
    $user_curr = scd_get_user_currency();
    $user_curr_opt = scd_get_user_currency_option();
    if ($user_curr !== FALSE && $user_curr_opt == 'only-default-currency') {
        $earning = scd_function_convert_subtotal($earning, get_option('woocommerce_currency'), $user_curr, 2);
    }
    return $earning;
}

//product variation
add_action('dokan_variation_options_pricing', 'scd_dokan_variable_product', 10, 3);

function scd_dokan_variable_product($loop, $variation_data, $variation)
{
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
    echo '<script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery(\'input[name="variable_regular_price[' . $loop . ']"]\').attr("type", "hidden");
                jQuery(\'input[name="variable_sale_price[' . $loop . ']"]\').attr("type", "hidden");
                jQuery(\'input[name="variable_regular_price[' . $loop . ']"]\').attr("id", "destination_variable_regular_price' . $loop . '");
                jQuery(\'input[name="variable_sale_price[' . $loop . ']"]\').attr("id", "destination_variable_sale_price' . $loop . '");
                jQuery("#source_variable_regular_price' . $loop . '").keyup(function (){
                    var src= document.getElementById("source_variable_regular_price' . $loop . '"); 
                    var dest= document.getElementById("destination_variable_regular_price' . $loop . '"); 
                    var price1  = src.value * ' . scd_function_convert_subtotal(1, $user_curr,  get_option('woocommerce_currency'), 18) . ';
                    if(price1){
                        dest.value=price1; 
                    }else{
                        dest.value=""; 
                    }
                });
                jQuery("#source_variable_sale_price' . $loop . '").keyup(function (){
                    var src= document.getElementById("source_variable_sale_price' . $loop . '"); 
                    var dest= document.getElementById("destination_variable_sale_price' . $loop . '"); 
                     var price2  = src.value * ' . scd_function_convert_subtotal(1, $user_curr,  get_option('woocommerce_currency'), 18) . ';
                    if(price2){
                        dest.value=price2; 
                    }else{
                        dest.value=""; 
                    }
                });
            });
        </script>';
    echo '<input type="hidden" class="scd_variable_product_fill" value="1" name="scd_variable_regular_fill_' . $loop . '" />';
    echo '<input type="hidden" class="scd_variable_product_loop" value="' . $loop . '" name="scd_variable_regular_price_' . $loop . '" />';
    if ($regprice) {
        echo '<script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery(\'input[name="variable_regular_price[' . $loop . ']"]\').val(' . get_post_meta($variation->ID, '_regular_price', true) . ');
        });
    </script>';
        echo '<div class="dokan-form-group dokan-clearfix dokan-price-container">
            <div class="content-half-part">
                <div class="dokan-input-group">
                    <input required type="text" size="100" id="source_variable_regular_price' . $loop . '" name="scd_variable_regular_price[' . $loop . ']" value="' . $regprice . '" class="wc_input_price dokan-form-control dokan-product-regular-price-variable" placeholder="Variation price (required)">
                </div>
            </div>';
    } else {
        echo '<div class="dokan-form-group dokan-clearfix dokan-price-container">
            <div class="content-half-part">
                <div class="dokan-input-group">
                    <input required type="text" size="100" id="source_variable_regular_price' . $loop . '" name="scd_variable_regular_price[' . $loop . ']" value="" class="wc_input_price dokan-form-control dokan-product-regular-price-variable" placeholder="Variation price (required)">
                </div>
            </div>';
    }
    if ($saleprice) {
        echo '<script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery(\'input[name="variable_regular_price[' . $loop . ']"]\').val(' . get_post_meta($variation->ID, '_sale_price', true) . ');
            });
        </script>';
        if ($saleprice == $regprice) {
            echo '<div class="content-half-part sale-price" style="margin-left:-10px;">
                <div class="dokan-input-group">
                    <input type="text" size="110" id="source_variable_sale_price' . $loop . '" name="scd_variable_sale_price[' . $loop . ']" value="" class="wc_input_price dokan-form-control dokan-product-sales-price-variable">
                </div>
                </div>
            </div>';
        } else {
            echo '<div class="content-half-part sale-price" style="margin-left:-10px;">
                    <div class="dokan-input-group">
                        <input type="text" size="110" id="source_variable_sale_price' . $loop . '" name="scd_variable_sale_price[' . $loop . ']" value="' . $saleprice . '" class="wc_input_price dokan-form-control dokan-product-sales-price-variable">
                    </div>
                </div>
            </div>'; //variable_wholesale_price   
        }
    } else {
        echo '<script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery(\'input[name="variable_regular_price[' . $loop . ']"]\').val("");
            });
        </script>';
        echo '<div class="content-half-part sale-price" style="margin-left:-10px;">
                <div class="dokan-input-group">
                    <input type="text" size="110" id="source_variable_sale_price' . $loop . '" name="scd_variable_sale_price[' . $loop . ']" value="" class="wc_input_price dokan-form-control dokan-product-sales-price-variable">
                </div>
            </div>
        </div>';
    }
    echo '<input type="hidden" id="scd_dokan_wholesale_price_' . $loop . '" class="scd_dokan_wholesale_price" value="' . $wholesale_price . '" />';
    $_product = wc_get_product($variation->ID);
    if ($_product)
        $earning = dokan()->commission->calculate_commission($variation->ID, $saleprice);
    echo '<input type="hidden" id="scd_dokan_vendor_earning_' . $loop . '" class="scd_dokan_vendor_earning" value="' . $earning . '" />';
?>
    <script type="text/javascript">
        var curr_symb = "<?php echo $curr_symbol; ?>";
        var base_curr = "<?php echo $base_curr; ?>";

        jQuery('.variation-topbar-heading').click(function(e) {
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

                setTimeout(function() {
                    //<input type="hidden" class="scd_variable_product_loop" value="'.$loop.'" name="scd_variable_regular_price_'.$loop.'" />
                    var loops = selfs.parent().find('.scd_variable_product_loop').val();
                    var regs = '#scd_dokan_regular_price_' + loops;
                    regs = jQuery(regs).val();
                    var sals = '#scd_dokan_sale_price_' + loops;
                    sals = jQuery(sals).val();

                    var vendor_earn = '#scd_dokan_vendor_earning_' + loops;
                    jQuery('.vendor-earning').html('(  You Earn : ' + curr_symb + '<span class="vendor-price">' + jQuery(vendor_earn).val() + '</span> )');

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

                    }*/
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
function scd_dokan_save_variable_product($variation_id, $i)
{
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
    $sale_price = ($variable_sale_price[$i] === '' ? '' : wc_format_decimal($variable_sale_price[$i]));

    /*saving product price in for the product list*/
    update_post_meta($variation_id, '_list_regular_price', $regular_price);
    update_post_meta($variation_id, '_list_sale_price', $sale_price);


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

function scd_save_variation_wholesale_data($post_id)
{
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

//add_action('dokan_order_inside_content', 'scd_order_main_content', 31);

function scd_order_main_content()
{
?>
    <style type="text/css">
        td.dokan-order-earning span.amount,
        td.dokan-order-total span.amount {
            display: none;
        }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            var order_select = jQuery('td.dokan-order-select input');
            var nbrows = order_select.length;
            var order_list = '';
            for (var ind = 0; ind < nbrows; ind++) {
                order_list = order_list + jQuery(order_select[ind]).val() + ',';
            }

            jQuery.post(
                ajaxurl, {
                    action: 'scd_dokan_get_order_total_and_earning',
                    order_list: order_list
                },
                function(response) {
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

function scd_dokan_get_order_total_and_earning()
{
    if (isset($_POST['order_list'])) {
        $order_list = explode(',', $_POST['order_list']);
        $resp = array(); ////=
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
function scd_dokan_seller_dashboard_widget_counter()
{
?>
    <style type="text/css">
        .big-counter ul li div.count span.amount {
            display: none;
        }
    </style>
    <script type="text/javascript">

    </script>
<?php
}

//return apply_filters( 'dokan_seller_total_sales', $earnings );
//includes/functions.php
add_filter('dokan_seller_total_sales', 'scd_dokan_seller_total_sales', 9999, 1);

function scd_dokan_seller_total_sales($total_sales)
{
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
    //dokan_cache_update_group($cache_key, $cache_group);

    return $total_sales;
}

//calculate vendor earning
add_filter('dokan_get_seller_earnings', 'scd_dokan_get_seller_earnings', 999, 2);

function scd_dokan_get_seller_earnings($earning, $seller_id)
{
    global $wpdb;
    $on_date = '';

    $status = dokan_withdraw_get_active_order_status_in_comma();
    $cache_group = 'dokan_seller_data_' . $seller_id;
    $cache_key = 'dokan_seller_earnings_' . $seller_id;
    $earning = wp_cache_get($cache_key, $cache_group);
    $on_date = $on_date ? date('Y-m-d', strtotime($on_date)) : current_time('mysql');
    $trn_type = 'dokan_refund';
    $refund_status = 'approved';
    $basecurrency = get_option('woocommerce_currency');
    $installed_version = get_option('dokan_theme_version');

    if (!$installed_version || version_compare($installed_version, '2.8.2', '>')) {
        $debit = $wpdb->get_results($wpdb->prepare(
            "SELECT trn_id, debit AS earnings
                    FROM {$wpdb->prefix}dokan_vendor_balance
                    WHERE
                        vendor_id = %d AND DATE(balance_date) <= %s AND status IN ($status) AND trn_type = 'dokan_orders'",
            $seller_id,
            $on_date
        ));
        $debit_balance = 0;
        foreach ($debit as $value) {
            $currency = get_post_meta($value->trn_id, '_order_currency', true);
            if ($currency == $basecurrency) {
                $debit_balance += $value->earnings;
            } else {
                $debit_balance += scd_function_convert_subtotal($value->earnings, $basecurrency, $currency, 2, true);
            }
        }
        $credit = $wpdb->get_results($wpdb->prepare(
            "SELECT trn_id, credit AS earnings
                    FROM {$wpdb->prefix}dokan_vendor_balance
                    WHERE
                        vendor_id = %d AND DATE(balance_date) <= %s AND trn_type = %s AND status = %s",
            $seller_id,
            $on_date,
            $trn_type,
            $refund_status
        ));

        $credit_balance = 0;
        foreach ($credit as $value) {
            $currency = get_post_meta($value->trn_id, '_order_currency', true);
            if ($currency == $basecurrency) {
                $credit_balance += $value->earnings;
            } else {
                $credit_balance += scd_function_convert_subtotal($value->earnings, $basecurrency, $currency, 2, true);
            }
        }

        $earnings = $debit_balance - $credit_balance;
        $result = new \stdClass;
        $result->earnings = $earnings;
    } else {
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT
                        order_id, net_amount as earnings
                    FROM
                        {$wpdb->prefix}dokan_orders as do LEFT JOIN {$wpdb->prefix}posts as p ON do.order_id = p.ID
                    WHERE
                        seller_id = %d AND DATE(p.post_date) <= %s AND order_status IN ($status)",
            $seller_id,
            $on_date
        ));
        $total_earninigs = 0;
        foreach ($results as $value) {
            $currency = get_post_meta($value->order_id, '_order_currency', true);
            if ($currency == $basecurrency) {
                $total_earninigs += $value->earnings;
            } else {
                $total_earninigs += scd_function_convert_subtotal($value->earnings, $basecurrency, $currency, 2, true);
            }
        }
        $result = new \stdClass;
        $result->earnings = $total_earninigs;
    }

    $earning = (float) $result->earnings;

    wp_cache_set($cache_key, $earning, $cache_group);
    //dokan_cache_update_group($cache_key, $cache_group);


    return $earning;
}

add_filter('dokan_get_formatted_seller_earnings', 'scd_dokan_get_formatted_seller_earnings', 99, 2);

function scd_dokan_get_formatted_seller_earnings($earning, $seller_id)
{
    $earnings = scd_dokan_get_seller_earnings($earning, $seller_id);

    return wc_price($earnings);
}

add_action('dokan_order_detail_after_order_items', 'scd_dokan_order_detail_after_order_items', 99, 1);

function scd_dokan_order_detail_after_order_items($order)
{

?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery.post(
                ajaxurl, {
                    action: 'scd_dokan_get_order_details',
                    order_id: '<?php echo $order->get_id(); ?>'
                },
                function(response) {

                    if (response.success) {
                        if (response.data !== undefined) {
                            if (response.data.table !== undefined) {
                                //line_cost     
                                var leng = response.data.table.length;

                                var cost = jQuery('td.item_cost .view');
                                var total = jQuery('td.line_cost .view');
                                for (var i = 0; i < leng; i++) {
                                    jQuery(cost[i]).html(response.data.table[i].cost_subtotal + response.data.table[i].cost_total);
                                    jQuery(total[i]).html(response.data.table[i].subtotal + response.data.table[i].total + response.data.table[i].refund);
                                }
                            }
                            //earning 
                            if (response.data.earning !== undefined)
                                jQuery('li.earning-from-order .amount').html(response.data.earning);
                            //order total
                            if (response.data.total_order !== undefined)
                                jQuery('.wc-order-totals-items .wc-order-totals td.total .view').html(response.data.total_order);
                            //total refunded refunded-total
                            if (response.data.total_refund !== undefined)
                                jQuery('.wc-order-totals-items .wc-order-totals td.refunded-total:eq(1)').html('-' + response.data.total_refund);
                            var totals = jQuery('.wc-order-totals-items .wc-order-totals td.total');
                            //discount
                            if (response.data.total_discount !== undefined)
                                jQuery(totals[0]).html(response.data.total_discount);

                            //shipping
                            if (response.data.total_shipping !== undefined)
                                jQuery(totals[1]).html(response.data.total_shipping);

                        }
                    }
                });
        });
    </script>
<?php
}

//add_action('wp_ajax_scd_dokan_get_order_details', 'scd_dokan_get_order_details');

function scd_dokan_get_order_details()
{
    if (isset($_POST['order_id'])) {
        $order = wc_get_order($_POST['order_id']);

        $order_items = $order->get_items('line_item');
        $resp = array();
        $args['decimals'] = scd_options_get_decimal_precision();
        $args['price_format'] = get_woocommerce_price_format();
        $args['currency'] = $order->get_currency();
        $args['price_format'] = scd_change_currency_display_format($args['price_format'], $order->get_currency());
        $line = array();
        $types = '';
        foreach ($order_items as $item_id => $item) {
            $line = array(
                'cost_subtotal' => '',
                'cost_total' => '',
                'subtotal' => '',
                'total' => '',
                'refund' => ''
            );

            switch ($item['type']) {
                case 'line_item':
                    $_product = $order->get_product_from_item($item);
                    //item cost 
                    if (isset($item['line_total'])) {
                        if (isset($item['line_subtotal']) && $item['line_subtotal'] != $item['line_total']) {
                            $line['cost_subtotal'] = '<del>' . scd_format_converted_price_to_html($order->get_item_subtotal($item, false, true), $args) . '</del> ';
                        }
                        $line['cost_total'] = scd_format_converted_price_to_html($order->get_item_total($item, false, true), $args);
                    }
                    //total item
                    if (isset($item['line_total'])) {
                        if (isset($item['line_subtotal']) && $item['line_subtotal'] != $item['line_total']) {
                            $line['subtotal'] = '<del>' . scd_format_converted_price_to_html($item['line_subtotal'], $args) . '</del> ';
                        }
                        $line['total'] = scd_format_converted_price_to_html($item['line_total'], $args);
                    }

                    if ($refunded = $order->get_total_refunded_for_item($item_id)) {
                        $line['refund'] = ' <small class="refunded">-' . scd_format_converted_price_to_html($refunded, $args) . '</small>';
                    }
                    break;
            }
            $resp[] = $line;
        }

        //shipping
        $line_items_shipping = $order->get_items('shipping');
        foreach ($line_items_shipping as $item_id => $item) {
            $line = array(
                'cost_subtotal' => '',
                'cost_total' => '',
                'subtotal' => '',
                'total' => '',
                'refund' => ''
            );
            $line['total'] = (isset($item['cost'])) ? scd_format_converted_price_to_html(wc_round_tax_total($item['cost']), $args) : '';
            if ($refunded = $order->get_total_refunded_for_item($item_id, 'shipping')) {
                $line['refund'] = '<small class="refunded">-' . scd_format_converted_price_to_html($refunded, $args) . '</small>';
            }
            $resp[] = $line;
        }

        //fee
        $line_items_fee = $order->get_items('fee');
        foreach ($line_items_fee as $item_id => $item) {
            $line = array(
                'cost_subtotal' => '',
                'cost_total' => '',
                'subtotal' => '',
                'total' => '',
                'refund' => ''
            );
            $line['total'] = (isset($item['line_total'])) ? scd_format_converted_price_to_html(wc_round_tax_total($item['line_total']), $args) : '';
            if ($refunded = $order->get_total_refunded_for_item($item_id, 'fee')) {
                $line['refund'] = '<small class="refunded">-' . scd_format_converted_price_to_html($refunded, $args) . '</small>';
            }

            $resp[] = $line;
        }
        //refunds
        if ($refunds = $order->get_refunds()) {
            foreach ($refunds as $refund) {
                $line = array(
                    'cost_subtotal' => '',
                    'cost_total' => '',
                    'subtotal' => '',
                    'total' => '',
                    'refund' => ''
                );
                $line['total'] = scd_format_converted_price_to_html('-' .  dokan_replace_func('get_refund_amount', 'get_amount', $refund), $args);
                $resp[] = $line;
            }
        }
        $response = array();
        $response['types'] = $types;
        $response['table'] = $resp;
        $response['total_order'] = scd_format_converted_price_to_html($order->get_total(), $args);
        $response['total_shipping'] = scd_format_converted_price_to_html($order->get_total_shipping(), $args);
        $response['total_discount'] = scd_format_converted_price_to_html($order->get_total_discount(), $args);
        $response['total_refund'] = scd_format_converted_price_to_html($order->get_total_refunded(), $args);
        $response['earning'] = scd_format_converted_price_to_html(dokan()->commission->get_earning_by_order($order), $args);
        wp_send_json_success($response);
    }
    wp_send_json_error();
}
add_filter('woocommerce_get_price_html', 'scd_dokan_change_product_html', 999, 2);
function scd_dokan_change_product_html($price_html, $product)
{
    global $post;
    if (in_array('dokan-dashboard', get_body_class())) {
        $child = array();
        $price1 = "";
        $price2 = "";
        $simplePrice1 = "";
        $simplePrice2 = "";
        if ($product->get_children()) {
            // echo wp_kses_post( $product->get_price_html() );
            foreach ($product->get_children() as $product_child) {
                //Adding price products who don't in meta Data
                if (!get_post_meta($product_child, '_list_regular_price', true)) {
                    update_post_meta($product_child, '_list_regular_price', scd_function_convert_subtotal(wc_get_product($product_child)->get_regular_price(), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                    update_post_meta($product_child, '_list_sale_price', scd_function_convert_subtotal(wc_get_product($product_child)->get_sale_price(), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                } else {
                    if (!get_post_meta($product_child, '_regular_price', true)) {
                        update_post_meta($product_child, '_list_regular_price', scd_function_convert_subtotal(wc_get_product($product_child)->get_regular_price(), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                        update_post_meta($product_child, '_list_sale_price', scd_function_convert_subtotal(wc_get_product($product_child)->get_sale_price(), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                    } else {
                        update_post_meta($product_child, '_list_regular_price', scd_function_convert_subtotal(get_post_meta($product_child, '_regular_price', true), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                        update_post_meta($product_child, '_list_sale_price', scd_function_convert_subtotal(get_post_meta($product_child, '_sale_price', true), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                    }
                }

                if (get_post_meta($product_child, '_sale_price')[0] != "") {
                    array_push($child, get_post_meta($product_child, '_list_sale_price', true));
                } else {
                    array_push($child, get_post_meta($product_child, '_list_regular_price', true));
                }
            }
            asort($child);
            $price1 = $child[array_key_first($child)];
            $price2 = $child[array_key_last($child)];
            return get_woocommerce_currency_symbol(scd_get_user_currency()) . '' . $price1 . ' - ' . get_woocommerce_currency_symbol(scd_get_user_currency()) . '' . $price2;
        } else {
            //Adding price products who don't in meta Data
            if (!get_post_meta($post->ID, '_list_regular_price', true)) {
                update_post_meta($post->ID, '_list_regular_price', scd_function_convert_subtotal(wc_get_product($post->ID)->get_regular_price(), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                update_post_meta($post->ID, '_list_sale_price', scd_function_convert_subtotal(wc_get_product($post->ID)->get_sale_price(), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
            } else {
                if (!get_post_meta($post->ID, '_regular_price', true)) {
                    update_post_meta($post->ID, '_list_regular_price', scd_function_convert_subtotal(wc_get_product($post->ID)->get_regular_price(), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                    update_post_meta($post->ID, '_list_sale_price', scd_function_convert_subtotal(wc_get_product($post->ID)->get_sale_price(), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                } else {
                    update_post_meta($post->ID, '_list_regular_price', scd_function_convert_subtotal(get_post_meta($post->ID, '_regular_price', true), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                    update_post_meta($post->ID, '_list_sale_price', scd_function_convert_subtotal(get_post_meta($post->ID, '_sale_price', true), get_option('woocommerce_currency'), scd_get_user_currency(), 2));
                }
            }

            $simplePrice1 = get_post_meta($post->ID, '_list_regular_price', true);
            $simplePrice2 = get_post_meta($post->ID, '_list_sale_price', true);

            if ($simplePrice2) {
                return '<span style="text-decoration: line-through; color:red;">' . get_woocommerce_currency_symbol(scd_get_user_currency()) . '' . $simplePrice1 . '</span> <span>' . get_woocommerce_currency_symbol(scd_get_user_currency()) . '' . $simplePrice2 . '</span>';
            } else {
                return '<span>' . get_woocommerce_currency_symbol(scd_get_user_currency()) . '' . $simplePrice1 . '</span>';
            }
        }
    } else {
        return $price_html;
    }
}


/******************************************************************************************
correct withdraw on dokan Dashbord
 ************************************************************************************************/

add_filter('dokan_withdraw_content', 'scd_dokan_add_woocommerce_currency_symbol_filter_save', 21);
function scd_dokan_add_woocommerce_currency_symbol_filter_save()
{
?>
    <script>
        //alert(localStorage.user_currency);
        jQuery(document).ready(function() {
            jQuery('.dokan-form-horizontal').append('<input name="withdraw_amount" required="" class="wc_input_price dokan-form-control" id="scd-withdraw-amount" type="hidden" placeholder="0.00" value="">');
            jQuery('#withdraw-amount').keyup(function() {
                jQuery('#scd-withdraw-amount').val(jQuery('#withdraw-amount').val() * scd_get_convert_rate('<?php echo scd_get_user_currency(); ?>', settings.baseCurrency));
            });
        });
    </script>

<?php

    // add_filter( 'woocommerce_currency_symbol','scd_dokan_woocommerce_currency_symbol',10,2);
}

add_filter('dokan_order_net_amount', 'scd_dokan_add_order_amount_convert', 10, 2);
function scd_dokan_add_order_amount_convert($net_amountscd, $orderscd)
{
    $rate = scd_get_conversion_rate(get_woocommerce_currency(), scd_get_target_currency());
    $net_amountscd = $net_amountscd / $rate; ?>

<?php return $net_amountscd;
}

/******************************************************************************************
end correct withdraw on dokan Dashbord
 ************************************************************************************************/



//Order list rewritting

add_action('woocommerce_admin_order_actions_end', 'scd_dokan_order', 10, 1);
function scd_dokan_order($order)
{
?>
    <script>
        jQuery('document').ready(function() {
            if (jQuery('article.dokan-orders-area')[0] === undefined) {
                jQuery('td.scd-dokan-order-total').remove();
                jQuery('td.scd-dokan-order-earning').remove();
                jQuery('td.scd-dokan-order-status').remove();
                jQuery('td.scd-dokan-order-customer').remove();
                jQuery('td.scd-dokan-order-date').remove();
                jQuery('td.scd-dokan-order-action').remove();
            }
            jQuery('td.dokan-order-total').css('display', 'none');
            jQuery('td.dokan-order-earning').css('display', 'none');
            jQuery('td.dokan-order-status').css('display', 'none');
            jQuery('td.dokan-order-customer').css('display', 'none');
            jQuery('td.dokan-order-date').css('display', 'none');
            jQuery('td.dokan-order-action').css('display', 'none');
        });
    </script>
    <td class="scd-dokan-order-total" data-title="<?php esc_attr_e('Order Total', 'dokan-lite'); ?>">
        <?php
        echo get_woocommerce_currency_symbol(scd_get_user_currency()) . '' . scd_function_convert_subtotal($order->get_total(), $order->get_currency(), scd_get_user_currency(), 2);
        ?>
    </td>
    <td class="scd-dokan-order-earning" data-title="<?php esc_attr_e('Earning', 'dokan-lite'); ?>">
        <?php echo wp_kses_post(get_woocommerce_currency_symbol(scd_get_user_currency()) . '' . scd_function_convert_subtotal(dokan()->commission->get_earning_by_order($order), get_option('woocommerce_currency'), scd_get_user_currency(), 2)); ?>
    </td>
    <td class="scd-dokan-order-status" data-title="<?php esc_attr_e('Status', 'dokan-lite'); ?>">
        <?php echo '<span class="dokan-label dokan-label-' . dokan_get_order_status_class(dokan_get_prop($order, 'status')) . '">' . dokan_get_order_status_translated(dokan_get_prop($order, 'status')) . '</span>'; ?>
    </td>
    <td class="scd-dokan-order-customer" data-title="<?php esc_attr_e('Customer', 'dokan-lite'); ?>">
        <?php
        $user_info = '';

        if ($order->get_user_id()) {
            $user_info = get_userdata($order->get_user_id());
        }

        if (!empty($user_info)) {

            $user = '';

            if ($user_info->first_name || $user_info->last_name) {
                $user .= esc_html($user_info->first_name . ' ' . $user_info->last_name);
            } else {
                $user .= esc_html($user_info->display_name);
            }
        } else {
            $user = $order->get_formatted_billing_full_name();
        }

        echo esc_html($user);
        ?>
    </td>
    <td class="scd-dokan-order-date" data-title="<?php esc_attr_e('Date', 'dokan-lite'); ?>">
        <?php
        if ('0000-00-00 00:00:00' == dokan_get_date_created($order)) {
            $t_time = $h_time = __('Unpublished', 'dokan-lite');
        } else {
            $t_time    = get_the_time('Y/m/d g:i:s A', dokan_get_prop($order, 'id'));
            $gmt_time  = strtotime(dokan_get_date_created($order) . ' UTC');
            $time_diff = current_time('timestamp', 1) - $gmt_time;

            if ($time_diff > 0 && $time_diff < 24 * 60 * 60) {
                $h_time = sprintf(__('%s ago', 'dokan-lite'), human_time_diff($gmt_time, current_time('timestamp', 1)));
            } else {
                $h_time = get_the_time('Y/m/d', dokan_get_prop($order, 'id'));
            }
        }

        echo '<abbr title="' . esc_attr(dokan_date_time_format($t_time)) . '">' . esc_html(apply_filters('post_date_column_time', dokan_date_time_format($h_time, true), dokan_get_prop($order, 'id'))) . '</abbr>';
        ?>
    </td>
    <td class="scd-dokan-order-action" width="17%" data-title="<?php esc_attr_e('Action', 'dokan-lite'); ?>">
        <?php
        $actions = array();

        if (dokan_get_option('order_status_change', 'dokan_selling', 'on') == 'on') {
            if (in_array(dokan_get_prop($order, 'status'), array('pending', 'on-hold'))) {
                $actions['processing'] = array(
                    'url' => wp_nonce_url(admin_url('admin-ajax.php?action=dokan-mark-order-processing&order_id=' . dokan_get_prop($order, 'id')), 'dokan-mark-order-processing'),
                    'name' => __('Processing', 'dokan-lite'),
                    'action' => "processing",
                    'icon' => '<i class="fa fa-clock-o">&nbsp;</i>'
                );
            }

            if (in_array(dokan_get_prop($order, 'status'), array('pending', 'on-hold', 'processing'))) {
                $actions['complete'] = array(
                    'url' => wp_nonce_url(admin_url('admin-ajax.php?action=dokan-mark-order-complete&order_id=' . dokan_get_prop($order, 'id')), 'dokan-mark-order-complete'),
                    'name' => __('Complete', 'dokan-lite'),
                    'action' => "complete",
                    'icon' => '<i class="fa fa-check">&nbsp;</i>'
                );
            }
        }

        $actions['view'] = array(
            'url' => wp_nonce_url(add_query_arg(array('order_id' => dokan_get_prop($order, 'id')), dokan_get_navigation_url('orders')), 'dokan_view_order'),
            'name' => __('View', 'dokan-lite'),
            'action' => "view",
            'icon' => '<i class="fa fa-eye">&nbsp;</i>'
        );

        $actions = apply_filters('woocommerce_admin_order_actions', $actions, $order);

        foreach ($actions as $action) {
            $icon = (isset($action['icon'])) ? $action['icon'] : '';
            printf('<a class="dokan-btn dokan-btn-default dokan-btn-sm tips" href="%s" data-toggle="tooltip" data-placement="top" title="%s">%s</a> ', esc_url($action['url']), esc_attr($action['name']), $icon);
        }

        ?>
    </td>
<?php
}
//End of order list rewritting


//Single order page rewritting
add_action('dokan_order_detail_after_order_items', 'scd_single_order_page', 10, 1);
function scd_single_order_page($order)
{
?>
    <script>
        jQuery(window).on('load', function() {
            var elements = jQuery('.dokan-dashboard .amount');
            var len = elements.length;
            var rate = '<?php echo scd_get_conversion_rate($order->get_currency(), scd_get_user_currency()) ?>';
            var currency_symbol = '<?php echo get_woocommerce_currency_symbol(scd_get_user_currency()); ?>';
            console.log(rate);
            for (var i = 0; i < len; i++) {
                jQuery(elements[i]).parent().html(`${currency_symbol}${((elements[i].textContent.replace(/[^0-9\.-]+/g,"")) * rate).toFixed(2) }`);
            }
            jQuery('.earning-from-order').html(
                `<li class="earning-from-order">
            <span><?php esc_html_e('Earning From Order:', 'dokan-lite'); ?></span>
            <?php echo  wp_kses_post((get_woocommerce_currency_symbol(scd_get_user_currency()) . '' . scd_function_convert_subtotal(dokan()->commission->get_earning_by_order($order), get_option('woocommerce_currency'), scd_get_user_currency(), 2))); ?>
        </li>`);
            jQuery('del').remove();
        });
    </script>
<?php
}
