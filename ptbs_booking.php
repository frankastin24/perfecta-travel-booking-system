<?php

/**
 * Plugin Name: Perfecta Traval Booking System
 * Plugin URI: https://www.frankastin.co.uk/perfecta-traval-booking-system
 * Description: A custom booking system for Perfecta Traval Booking System with WS Pay Payment Gateway
 * Version: 0.1
 * Author: Frank Astin
 * Author URI: https://www.frankastin.co.uk/
 * Text Domain: ptbs-booking
 * License: GPL2
 *
 * == Copyright ==
 * Copyright 2023 Frank Astin
 *
 */

include plugin_dir_path(__FILE__) . '/bladeone.php';
include plugin_dir_path(__FILE__) . '/traits/admin_menu.php';
include plugin_dir_path(__FILE__) . '/traits/generate_data.php';
include plugin_dir_path(__FILE__) . '/traits/ajax.php';
include plugin_dir_path(__FILE__) . '/traits/shortcodes.php';
include plugin_dir_path(__FILE__) . '/class_ws_payment_gateway.php';

use eftec\bladeone\BladeOne;

$views = plugin_dir_path(__FILE__) . '/views';
$cache = plugin_dir_path(__FILE__) . '/cache';
global $blade;
$blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);

function wpse27856_set_content_type()
{
    return "text/html";
}
add_filter('wp_mail_content_type', 'wpse27856_set_content_type');

class POA_Booking
{

    use admin_menu;
    use generate_data;
    use ajax;
    use shortcodes;

    public function __construct()
    {
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->plugin_dir_url = plugin_dir_url(__FILE__);

        //Get Start dates

        $this->calendar_data = array();

        $this->calendar_data['ajax_url'] = admin_url('admin-ajax.php');

        $this->selects = $this->select_menus();
        $slug = $_SERVER;
        $slug = explode('/', $slug['REQUEST_URI'])[2];

        $args = array(
            'name'        => $slug,
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => 1,
            'post_type' => 'package'
        );
        $package = get_posts($args);

        if (
            !$package
        ) {
            $this->start_dates = false;
        } else {
            $this->start_dates = get_post_meta($package[0]->ID, 'start_dates', true);
        }

        add_shortcode('poa_booking', array($this, 'calendar_form_shortcode'));

        add_shortcode('poa_pricing', array($this, 'pricing_table_shortcode'));

        add_action('woocommerce_after_checkout_form', array($this, 'address_shortcode'));

        add_shortcode('poa_order_complete', array($this, 'order_complete_shortcode'));

        add_action('admin_menu', array($this, 'add_menu_page'));

        add_action('wp_ajax_generate_wspay_signature', array($this, 'generate_wspay_signature'));

        add_action('wp_ajax_nopriv_generate_wspay_signature', array($this, 'generate_wspay_signature'));

        add_action('init', array($this, 'register_post_types'));
        add_action('save_post', array($this, 'save_post'));
        add_filter('woocommerce_default_address_fields', array($this, 'filter_default_address_fields'), 20, 1);
        add_filter('woocommerce_coupons_enabled', array($this, 'hide_coupon_field_on_cart'));
        add_filter('woocommerce_cart_ready_to_calc_shipping', array($this, 'disable_shipping_calc_on_cart'), 99);
        add_filter('woocommerce_locate_template', array($this, 'woo_adon_plugin_template'), 1, 3);
        add_action('woocommerce_email', array($this, 'unhook_those_pesky_emails'));
        add_filter('woocommerce_payment_gateways', array($this, 'WS_Pay_add_gateway_class'));
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'bs_order_lead_time_data'));
    }

    public function bs_order_lead_time_data($order)
    {



        // Using WC_Data method get_meta() since WooCommerce 3
?>
        <p class="form-field form-field-wide wc-customer-user">
            <!--email_off--> <!-- Disable CloudFlare email obfuscation -->
            <label for="customer_user">
                Cruise Name: </label>
            <b><?= get_post_meta($order->ID, 'Cruise', true) ?></b>
            <!--/email_off-->
        </p>
        <p class="form-field form-field-wide wc-customer-user">
            <!--email_off--> <!-- Disable CloudFlare email obfuscation -->
            <label for="customer_user">
                Dates Booked: </label>
            <b><?= get_post_meta($order->ID, 'Dates')[0] ?> to <?= get_post_meta($order->ID, 'Dates')[1] ?></b>
            <!--/email_off-->
        </p>
    <?php


        // Or the older way (using always the item Id)
        // echo wc_get_order_item_meta( $item_id , 'Lead Time', true );

    }
    public function WS_Pay_add_gateway_class($gateways)
    {
        $gateways[] = 'WC_WS_Pay_Gateway'; // your class name is here
        return $gateways;
    }
    public function unhook_those_pesky_emails($email_class)
    {

        // New order emails
        remove_action('woocommerce_order_status_pending_to_processing_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
        remove_action('woocommerce_order_status_pending_to_completed_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
        remove_action('woocommerce_order_status_pending_to_on-hold_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
        remove_action('woocommerce_order_status_failed_to_processing_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
        remove_action('woocommerce_order_status_failed_to_completed_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));
        remove_action('woocommerce_order_status_failed_to_on-hold_notification', array($email_class->emails['WC_Email_New_Order'], 'trigger'));

        // Processing order emails
        remove_action('woocommerce_order_status_pending_to_processing_notification', array($email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
        remove_action('woocommerce_order_status_pending_to_on-hold_notification', array($email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
    }
    function woo_adon_plugin_template($template, $template_name, $template_path)
    {
        global $woocommerce;
        $_template = $template;
        if (!$template_path)
            $template_path = $woocommerce->template_url;

        $plugin_path  = untrailingslashit(plugin_dir_path(__FILE__))  . '/emails/';

        // Look within passed path within the theme - this is priority


        if (file_exists($plugin_path . $template_name)) {
            $template = $plugin_path . $template_name;
        } else {

            $template = $_template;
        }




        return $template;
    }
    public function disable_shipping_calc_on_cart($show_shipping)
    {

        return false;

        return $show_shipping;
    }
    public function hide_coupon_field_on_cart($address_fields)
    {
        if (is_checkout()) {
            $enabled = false;
        }
        return $enabled;
    }
    public function filter_default_address_fields($address_fields)
    {
        // Only on checkout page
        if (!is_checkout()) return $address_fields;

        // All field keys in this array
        $key_fields = array('country', 'phone',  'company', 'address_1', 'address_2', 'city', 'state', 'postcode');

        // Loop through each address fields (billing and shipping)
        foreach ($key_fields as $key_field)
            unset($address_fields[$key_field]);

        return $address_fields;
    }


    public function save_post($post_id)
    {
        $post = get_post($post_id);


        if ($post->post_type == 'package' && isset($_POST['json_data'])) {

            update_post_meta($post_id, 'package_type', $_POST['package_type']);
            update_post_meta($post_id, 'start_dates', json_decode(stripslashes_deep($_POST['json_data'])));
        }
    }

    public function register_post_types()
    {

        $supports = array(
            'title', // post title

        );
        $labels = array(
            'name' => _x('Packages', 'plural'),
            'singular_name' => _x('Package', 'singular'),
            'menu_name' => _x('Packages', 'admin menu'),
            'name_admin_bar' => _x('Package', 'admin bar'),
            'add_new' => _x('Add New Package', 'add new'),
            'add_new_item' => __('Add New Package'),
            'new_item' => __('New package'),
            'edit_item' => __('Edit package'),
            'view_item' => __('View packages'),
            'all_items' => __('All packages'),
            'search_items' => __('Search package'),
            'not_found' => __('No packages found.'),
        );
        register_post_type(
            'package',
            array(
                'labels'      => $labels,
                'public'      => true,
                'has_archive' => true,
                'supports' => $supports,
                'rewrite'     => array('slug' => 'package'),
                'register_meta_box_cb' => array($this, 'add_package_metabox')
            )
        );
    }

    public function add_package_metabox()
    {
        add_meta_box(
            'package_type',
            __('Package Type', 'sitepoint'),
            array($this, 'package_type_metabox')
        );
        add_meta_box(
            'start_date',
            __('Start Dates', 'sitepoint'),
            array($this, 'startdates_metabox')
        );
    }

    public function package_type_metabox()
    {
        global $post;
        $package_type = !empty(get_post_meta($post->ID, 'package_type', true)) ? get_post_meta($post->ID, 'package_type', true) : 'cruise';
    ?>
        <label>Package Type</label>
        <select name="package_type">
            <option <?= $package_type == 'cruise' ? 'selected' : '' ?> value="cruise">Cruise</option>
            <option <?= $package_type == 'ordinary' ? 'selected' : '' ?> value="ordinary">Ordinary Accomodation</option>
        </select>
<?php
    }

    public function startdates_metabox()
    {
        include $this->plugin_dir . '/menu_page.php';
    }

    public function generate_product_and_add_to_cart($room, $date)
    {

        $product = new WC_Product_Simple();


        $product->set_name(' Room for ' . $room['number_of_people'] . ' ' . (intval($room['number_of_people']) > 1 ? 'People' : 'Person')); // product title

        $product->set_slug('package-name');

        $product->set_regular_price($room['price']); // in current shop currency

        $product->set_short_description('');

        $product->save();

        global $woocommerce;

        $woocommerce->cart->add_to_cart($product->get_id());
    }

    public function generate_order()
    {
        $order = wc_create_order();


        $address = array(
            'first_name' => '111Joe',
            'last_name'  => 'Conlin',
            'company'    => 'Speed Society',
            'email'      => 'joe@testing.com',
            'phone'      => '760-555-1212',
            'address_1'  => '123 Main st.',
            'address_2'  => '104',
            'city'       => 'San Diego',
            'state'      => 'Ca',
            'postcode'   => '92121',
            'country'    => 'US'
        );

        $order->set_address($address, 'billing');
        $order->set_total($_POST['total']);
    }


    public function order_complete_shortcode()
    {
    }
    public function generate_wspay_signature()
    {
        session_start();
        $_SESSION['ShopID'] = "PERFTRAV";

        $SecretKey = "b71a3ec4e5e94S";
        $ShoppingCartID = time();
        $Amount = $_POST['amount'];

        $_SESSION['Amount']  = $Amount;
        $_SESSION['ShoppingCartID']  =  $ShoppingCartID;
        $_SESSION['Signature'] = hash("sha512", $_SESSION['ShopID'] . $SecretKey . $ShoppingCartID . $SecretKey . $Amount . $SecretKey);



        $_SESSION['dates'] = $_POST['dates'];
        $_SESSION['cruise'] = $_POST['cruise'];
        $_SESSION['rooms'] = $_POST['rooms'];


        WC()->cart->empty_cart();



        foreach ($_POST['rooms'] as $room) {
            $this->generate_product_and_add_to_cart($room, $_POST['dates']);
        }


        echo json_encode(array('Signature' => hash("sha512", $ShopID . $SecretKey . $ShoppingCartID . $SecretKey . $Amount . $SecretKey), 'ShoppingCardID' => $ShoppingCartID));

        die();
    }
}

new POA_Booking();
