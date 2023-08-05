<?php
trait ajax
{
    public function add_startdate()
    {

        $product_prices = array(
            'Upper/ Main Deck' => intval($_POST['upper_price']),
            'Upper/ Main Deck Single Occupancy' => (intval($_POST['upper_price']) / 2),
            'Lower Deck' => intval($_POST['lower_price']),
            'Lower Deck Single Occupancy' => (intval($_POST['lower_price']) / 2)
        );

        $products = [];

        foreach ($product_prices as $price_name => $product_price) {
            $product = new WC_Product_Simple();

            $product->set_name($_POST['start_date'] . ' ' . $price_name); // product title

            $product->set_slug(sanitize_title($_POST['start_date'] . ' ' . $price_name));

            $product->set_regular_price($product_price); // in current shop currency

            $product->set_short_description('');

            $product->save();

            $products[$price_name] = $product->ID;
        }
        $this->start_dates[$_POST['start_date']]  =  $products;

        update_option('POA_Booking_Start_Date', $this->start_dates);

        echo json_encode($this->start_dates);
    }
}
