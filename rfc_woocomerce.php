<?php

/**
 * Añadir el campo de RFC en el checkout, mostrarlo en el panel de admin/pedidos
 * añadir el rfc al ticket de compra (requiere plugin WooCommerce PDF Invoices,
 * Packing Slips, Delivery Notes & Shipping Labels)
 * introducir codigo en /functions.php
 * /


/**
 * Add RFC to Checkout
 */

function rfc_checkout_field($checkout){
    $current_user = wp_get_current_user();
    $saved_rfc = get_user_meta($current_user, 'user_rfc',true);
    $saved_name_rfc = get_user_meta($current_user, 'user_name_rfc',true);

    echo '<p></p>';
    echo '<div id="rfc_checkout_field"><h3>' . __('¿Requieres Factura?') . '</h3>';

    woocommerce_form_field('user_rfc_check', array(
        'type' => 'checkbox',
        'class' => array('user_rfc_check', 'form-row input', 'form-rom'),
        'label' => __('Sí!'),
        'required' => false
    ));

    woocommerce_form_field( 'user_rfc', array(
        'type' => 'text',
        'class' => array('user_rfc', 'form-row-input', 'form-row', 'hidden'),
        'label'=> __('RFC'),
        'placeholder' => __('Ingresa tu RFC'),
    ),
        $saved_rfc);

    woocommerce_form_field( 'user_name_rfc', array(
        'type' => 'text',
        'class' => array('user_name_rfc', 'form-row-input','form-row', 'hidden'),
        'label'=> __('Razón Social'),
        'placeholder' => __('Ingresa tu Razon Social'),
    ),
        $saved_name_rfc);
    echo '</div>';
    echo '<p> *Comprobaremos los datos antes de generar tu factura* </p>';


}
add_action( 'woocommerce_after_checkout_billing_form', 'rfc_checkout_field');


/**
 * Add RFC to Order
 */
function rfc_order_field($order_id){
    if ($order_id && $_POST['user_rfc'] && $_POST['user_name_rfc']) {
        update_post_meta( $order_id, 'user_rfc', sanitize_text_field($_POST['user_rfc']));
        update_post_meta( $order_id, 'user_name_rfc', sanitize_text_field($_POST['user_name_rfc']));
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'rfc_order_field');




/**
 * Add RFC to Admin View Order
 */
function display_rfc($order){
    echo '<p><strong>'.__('RFC').':</strong>'.get_post_meta( $order -> id, 'user_rfc',true).'</p>';
    echo '<p><strong>'.__('Razon').':</strong>'.get_post_meta($order -> id, 'user_name_rfc',true).'</p>';
}

add_action('woocommerce_admin_order_data_after_billing_address', 'display_rfc',10,1);



/**
 * Check Box Hide RFC
 */
function hidden_check() {
    ?>
    <script type="text/javascript">
        jQuery('input#user_rfc_check').change(function(){
            if (!this.checked) {
                jQuery('#user_rfc_field').addClass('hidden');
                jQuery('#user_name_rfc_field').addClass('hidden');
            } else {
                jQuery('#user_rfc_field').removeClass('hidden');
                jQuery('#user_name_rfc_field').removeClass('hidden');
            }
        });
    </script>
    <?php

}
add_action( 'woocommerce_after_checkout_form', 'hidden_check', 6);


/**
 * Add RFC to Invoice
 */

function wt_pklist_alter_billing_addr($billing_address, $template_type, $order)
{
    /* To unset existing field */
    if(!empty($billing_address['user_rfc']))
    {
        unset($billing_address['user_rfc']);
    }

    /* add a new field shipping address */
    $billing_address['user_rfc']='RFC:'.get_post_meta( $order -> id, 'user_rfc',true);

    return $billing_address;
}
add_filter('wf_pklist_alter_billing_address', 'wt_pklist_alter_billing_addr', 10, 3);
