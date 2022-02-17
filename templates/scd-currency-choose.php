<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">

<?php
    $current_page = get_query_var( 'scd_currency' );
    /**
     *  dokan_dashboard_content_before hook
     *  dokan_dashboard_support_content_before
     *
     *  @hooked get_dashboard_side_navigation
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_content_before' );
    do_action( 'dokan_dashboard_support_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-booking-wrapper dokan-product-edit">
	
	<?php
//add_action('wp_ajax_scd_show_user_currency', 'scd_show_user_currency');

//function scd_show_user_currency() {
	/*
	    $scd_url = dokan_get_navigation_url( 'scd_currency' );

        $template_args = array(
            'is_scd_currency'  => true,
            'scd_url' => $scd_url
        );
	    
		do_action( 'dokan_scd_load_menu_template', $current_page, $template_args );
	
	*/
	
    $options = array(
        'base-currency' => 'Base currency only',
        'only-default-currency' => 'Your default currency only'/*,
        'base-and-default-currency' => 'Base and default currency',
        'selected-currencies' => 'Selected currencies'*/
    );
    ?>
    <div class="scd-container">
        <p id="scd-action-status" style="margin-left:15%;"></p>
         <div class="scd-form-grp">
             <p class="scd-label">Select your default currency</p>
             <div class="scd-form-input">
                <select id="scd-currency-list" class="scd-user-curr">
                    <?php
                    $user_curr = scd_get_user_currency();
                    //if($user_curr!==FALSE) $user_curr=$user_curr[0];
                    foreach (scd_get_list_currencies() as $key => $val) {
                        if ($user_curr == $key) {
                            echo '<option selected value="' . $key . '" >' . $key . '(' . get_woocommerce_currency_symbol($key) . ')</option>';
                        } else {
                            echo '<option value="' . $key . '" >' . $key . '(' . get_woocommerce_currency_symbol($key) . ')</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="scd-form-btn">
                <?php
                //echo '<a  style="color:black; " class="scd-btn-control button" href="#" id="scd-save-curr">Save change<a>';
                echo '<br>';
                
                ?>
            </div>
        </div>
        <div class="scd-form-grp">
            <p class="scd-label">Set products price in</p>
            <div class="scd-form-input">
                <select id="scd-currency-option" class="scd-user-curr">
                    <?php
                    $currency_opt = scd_get_user_currency_option();
                    foreach ($options as $key => $val) {
                        if ($currency_opt == $key) {
                            echo '<option selected value="' . $key . '" >' . $val . '</option>';
                        } else {
                            echo '<option value="' . $key . '" >' . $val . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="scd-form-btn">
                <?php
                echo '<br>';
                
                ?>
            </div>           
            <div class="scd-form-btn">
                <?php
				echo '<a  style="color:black; " class="scd-btn-control button" href="#" id="scd-save-currency-option">Save change<a>';
                echo '</p>';
                ?>
            </div>
        </div>
    </div>
 
    </div><!-- .dokan-dashboard-content -->

    <?php

    /**
     *  dokan_dashboard_content_after hook
     *  dokan_dashboard_support_content_after hook
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_content_after' );
    do_action( 'dokan_dashboard_support_content_after' );
    ?>

</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>