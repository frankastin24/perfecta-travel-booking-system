<?php

/**
 * Plugin Name: POA Booking
 * Plugin URI: https://www.frankastin.co.uk/poa-booking
 * Description: A custom booking system for Pearls Of Adriatic
 * Version: 0.1
 * Author: Frank Astin
 * Author URI: https://www.frankastin.co.uk/
 * Text Domain: POA-booking
 * License: GPL2
 *
 * == Copyright ==
 * Copyright 2023 frankastin
 *
 */

include plugin_dir_path(__FILE__) . '/bladeone.php';
include plugin_dir_path(__FILE__) . '/traits/admin_menu.php';
include plugin_dir_path(__FILE__) . '/traits/generate_data.php';
include plugin_dir_path(__FILE__) . '/traits/ajax.php';
include plugin_dir_path(__FILE__) . '/traits/shortcodes.php';

use eftec\bladeone\BladeOne;

class POA_Booking extends BladeOne
{
    use admin_menu;
    use generate_data;
    use ajax;
    use shortcodes;
    public function __construct()
    {
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->plugin_dir_url = plugin_dir_url(__FILE__);

        //Setup Blade Templating 

        $this->templatePath = [plugin_dir_path(__FILE__) . '/views'];
        $this->compiledPath = plugin_dir_path(__FILE__) . '/cache';

        $this->setMode(0);

        //Get Start dates

        $this->calendar_data = array();

        $this->calendar_data['ajax_url'] = admin_url('admin-ajax.php');

        $this->selects = $this->select_menus();

        add_shortcode('poa_booking', array($this, 'shortcode'));

        add_action('woocommerce_after_checkout_form', array($this, 'address_shortcode'));

        add_shortcode('poa_order_complete', array($this, 'order_complete_shortcode'));

        add_action('admin_menu', array($this, 'add_menu_page'));

        add_action('wp_ajax_generate_wspay_signature', array($this, 'generate_wspay_signature'));

        add_action('wp_ajax_nopriv_generate_wspay_signature', array($this, 'generate_wspay_signature'));

        add_action('init', array($this, 'register_post_types'));
        add_action('save_post', array($this, 'save_post'));
    }


    public function save_post($post_id)
    {
        $post = get_post($post_id);


        if ($post->post_type == 'package' && isset($_POST['json_data'])) {
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
            'start_date',
            __('Start Dates', 'sitepoint'),
            array($this, 'startdates_metabox')
        );
    }

    public function startdates_metabox()
    {
        include $this->plugin_dir . '/menu_page.php';
    }

    public function generate_product_and_add_to_cart($room)
    {

        $product = new WC_Product_Simple();


        $product->set_name('Room for ' . $room['number_of_people'] . ' ' . (intval($room['number_of_people']) > 1 ? 'People' : 'Person')); // product title

        $product->set_slug('package-name');

        $product->set_regular_price($room['price']); // in current shop currency

        $product->set_short_description('');

        $product->save();

        WC()->cart->add_to_cart($product->get_id());
        $_SESSION['product_added'] = 'monkey';
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

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_item['data']->delete();
        }

        setcookie("order_details", $_POST['dates'], time() + 3600);

        WC()->cart->empty_cart();

        foreach ($_POST['rooms'] as $room) {
            $this->generate_product_and_add_to_cart($room);
        }


        echo json_encode(array('Signature' => hash("sha512", $ShopID . $SecretKey . $ShoppingCartID . $SecretKey . $Amount . $SecretKey), 'ShoppingCardID' => $ShoppingCartID));

        die();
    }
}

new POA_Booking();
