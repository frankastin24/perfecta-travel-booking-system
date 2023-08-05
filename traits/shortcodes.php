<?php
trait shortcodes
{
    public function shortcode($atts)
    {
        // var_dump($atts);
        if (
            !isset($atts['package-title'])
        ) {
            return 'Package title not set';
        }

        $post = get_page_by_title($atts['package-title'], OBJECT,  'package');


        if (
            !$post
        ) {
            return 'Package not found';
        }


        $this->start_dates = get_post_meta($post->ID, 'start_dates', true);

        $this->calendar_data['months'] = $this->generate_months_data();

        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Mulish:wght@400;500&display=swap');
        wp_enqueue_style('poa-booking-style', $this->plugin_dir_url . 'style.css', array(), time());
        wp_enqueue_script('jquery');
        wp_enqueue_script('poa-booking-script', $this->plugin_dir_url . '/script.js', 'jquery', time());
        wp_localize_script('poa-booking-script', 'PoaData',  $this->calendar_data);

        return  $this->run('shortcode', array('selects' => $this->selects));
    }

    public function address_shortcode()
    {
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Mulish:wght@400;500&display=swap');
        wp_enqueue_style('poa-booking-style', $this->plugin_dir_url . 'style.css', array(), time());

        $address = array(
            'CustomerFirstName' => array('First Name', 'text', 'required', 'Please enter your first name'),
            'CustomerLastName' => array('Last Name', 'text', 'required'),
            'CustomerEmail' => array('Email', 'email', 'text', 'isemail'),
            'CustomerPhone' => array('Phone', 'tel', 'required'),
            'CustomerAddress' => array('Address Line 1', 'text', 'required'),
            'CustomerCity' => array('City', 'text', 'required'),
            'state' => array('State', 'text', 'required'),
            'CustomerZIP' => array('Postcode', 'text', 'required'),
            'CustomerCountry
            ' => array('Country', 'text', 'required')
        );

        session_start();
        wp_enqueue_script('jquery');
        wp_enqueue_script('poa-booking-script', $this->plugin_dir_url . '/script.js', 'jquery', time());


        $ws_pay = array($_SESSION['ShopID'], $_SESSION['ShoppingCartID'], $_SESSION['Amount'], $_SESSION['Signature']);

        echo $this->run('details_form', array('address' => $address, 'ws_pay' => $ws_pay));
    }
    public function order_complete()
    {



        return $this->run('details_form', array('address' => $address));
    }
}
