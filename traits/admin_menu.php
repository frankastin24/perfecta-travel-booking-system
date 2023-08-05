<?php
trait admin_menu
{
    public function add_menu_page()
    {
        add_menu_page('POA Booking', 'POA Booking', 'manage_options', 'poa-booking', array($this, 'menu_page'));
    }
}
