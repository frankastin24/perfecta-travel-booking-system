<?php
trait shortcodes
{
    public function calendar_form_shortcode()
    {
        if ($this->start_dates) {

            $this->calendar_data['months'] = $this->generate_months_data();

            wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Mulish:wght@400;500&display=swap');
            wp_enqueue_style('poa-booking-style', $this->plugin_dir_url . 'style.css', array(), time());
            wp_enqueue_script('jquery');
            wp_enqueue_script('poa-booking-script', $this->plugin_dir_url . '/script.js', array('jquery'), strval(time()));
            wp_localize_script('poa-booking-script', 'PoaData',  $this->calendar_data);

            global $blade;

            return  $blade->run('calendar_form_shortcode', array('selects' => $this->selects));
        } else {
            return 'Please create a package with the same name as this cruise.';
        }
    }

    public function pricing_table_shortcode()
    {
        if ($this->start_dates) {
            global $blade;
            return  $blade->run('pricing_table', array('prices' => $this->generate_pricing_data()));
        } else {
            return 'Please create a package with the same name as this cruise.';
        }
    }



    public function address_shortcode()
    {
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Mulish:wght@400;500&display=swap');
        wp_enqueue_style('poa-booking-style', $this->plugin_dir_url . 'style.css', array(), time());

        $address = array(
            'CustomerFirstName' => array('First Name', 'text', 'Please enter your first name'),
            'CustomerLastName' => array('Last Name', 'text'),
            'CustomerEmail' => array('Email', 'email',),
        );

        session_start();
        wp_enqueue_script('jquery');

        wp_enqueue_script('poa-booking-script', $this->plugin_dir_url . '/script.js', array('jquery'), strval(time()));


        $ws_pay = array($_SESSION['ShopID'], $_SESSION['ShoppingCartID'], $_SESSION['Amount'], $_SESSION['Signature']);
        global $blade;

        echo $blade->run('details_form', array('site_url' => site_url(), 'address' => $address, 'ws_pay' => $ws_pay));
    }
    public function order_complete()
    {
        global $blade;
        return $blade->run('details_form', array('address' => $address));
    }
}
