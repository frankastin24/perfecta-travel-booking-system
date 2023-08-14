<?php
add_action('plugins_loaded', 'ws_pay_init_gateway_class');
function ws_pay_init_gateway_class()
{
    class WC_WS_Pay_Gateway extends WC_Payment_Gateway
    {


        public function __construct()
        {
            $this->id = 'WC_WS_Pay_Gateway';
            $this->method_title =  'WS Pay Payment Gateway';
            $this->method_description = 'Custom payment gateway for WS Pay';
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title');
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        }
        public function process_payment($order_id)
        {
            session_start();

            $order = wc_get_order($order_id);

            update_post_meta($order_id, 'Dates', $_SESSION['dates']);
            update_post_meta($order_id, 'Cruise', $_SESSION['cruise']);


            $rooms = [];

            foreach ($order->get_items() as $item) {
                $rooms[] = array(
                    'name' => $item->get_name(),
                    'price' => $item->get_total()
                );
            }

            global  $blade;
            $email_body =  $blade->run('order_confimation_email', array('total' => $order->get_total(), 'dates' => $_SESSION['dates'], 'rooms' => $rooms, 'name' => $order->billing_first_name, 'cruise' => $_SESSION['cruise'], 'site_url' => site_url()));
            $subject = 'Your ' . $_SESSION['cruise'] . ' order has been recieved! Thank you for your decission to cruise with us!';

            wp_mail($order->billing_email, $subject, $email_body);



            $order->payment_complete();

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $cart_item['data']->delete();
            }
            global $woocommerce;
            $woocommerce->cart->empty_cart();

            return array(
                'result'   => 'success',
                'redirect' => '/order-complete',
            );
        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable WS Pay Payment', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default' => __('WS Payment Gateway', 'woocommerce'),
                    'desc_tip'      => true,
                ),
                'shop_id' => array(
                    'title' => __('Shop ID', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This is your WS Pay shop id', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip'      => true,
                ),
                'secret_key' => array(
                    'title' => __('Secret Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This is your WS Pay secret key', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip'      => true,
                ),
            );
        }

        /**
         * You will need it if you want your custom credit card form, Step 4 is about it
         */
        public function payment_fields()
        {
        }

        /*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
        public function payment_scripts()
        {

            // let's suppose it is our payment processor JavaScript that allows to obtain a token


            // and this is our custom JS in your plugin directory that works with token.js

        }

        /*
 		 * Fields validation, more in Step 5
		 */
        public function validate_fields()
        {
        }

        /*
		 * We're processing the payments here, everything about it is in Step 5
		 */


        /*
		 * In case you need a webhook, like PayPal IPN etc
		 */
        public function webhook()
        {
        }
    }
}
