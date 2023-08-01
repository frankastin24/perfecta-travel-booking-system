<?php

/**
 * Plugin Name: POA Booking
 * Plugin URI: https://www.frankastin.co.uk/poa-booking
 * Description: A custom booking system
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

class POA_Booking
{
    public function __construct()
    {
        $this->start_dates = get_option('WPAB_start_dates', []);

        $this->start_dates =  ["2023-04-15", "2023-04-22", "2023-04-29", "2023-05-06", "2023-10-07", "2023-10-14", "2023-10-21", "2023-04-01", "2023-04-08", "2023-05-13", "2023-05-20", "2023-09-30", "2023-05-27", "2023-09-23", "2023-07-01", "2023-07-08", "2023-07-15", "2023-07-22", "2023-09-16", "2023-07-01", "2023-07-08", "2023-07-15", "2023-07-22", "2023-07-29", "2023-08-05", "2023-08-12", "2023-08-19", "2023-08-26", "2023-09-02", "2023-09-09"];

        $this->months_containing_startdates = [];

        foreach ($this->start_dates as $start_date) {
            $month = explode('-', $start_date)[1];
            if (!array_key_exists($month, $this->months_containing_startdates)) {

                $this->months_containing_startdates[intval($month)] = array();
            }

            array_push($this->months_containing_startdates[intval($month)], $start_date);
        }
        ksort($this->months_containing_startdates);
        $this->generate_styles();
        echo  $this->generate_calendar_html();
        exit;
    }

    public function generate_calendar_html()
    {
        $calendar_html = '';
        foreach ($this->months_containing_startdates as $month => $dates) {
            $calendar_html .= $this->generate_month_html($month, $dates);
        }
        return $calendar_html;
    }

    public function generate_month_html($month, $dates)
    {

        $monthDateObj  = DateTime::createFromFormat('Y-m-d', $dates[0]);

        $monthName = $monthDateObj->format('F');


        $month_html = '<div class="month"><header>' . $monthName . '</header><div class="day-names"><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div><div>S</div></div><div class="days">';

        $num_days = cal_days_in_month(CAL_GREGORIAN, $month, 2023);
        $firstDayDateObj  = DateTime::createFromFormat('Y-m-d', '2023-' . $month . '-01');

        $number_of_additional_days = intval($firstDayDateObj->format('w')) - 1;



        for ($i = 0; $i < $number_of_additional_days; $i++) {
            $month_html .= '<div class="day grey"></div>';
        }

        $active_days = 0;
        for ($i = 0; $i < $num_days; $i++) {
            $dayDateObj  = DateTime::createFromFormat('Y-m-j', '2023-' . $month . '-' . $i);

            $daydate = $dayDateObj->format('Y-m-d');
            $active = false;
            if ($active_days > 0) {
                $active = true;
                $active_days--;
            }
            if (in_array($daydate, $dates)) {
                $active = true;
                $active_days = 6;
            }

            $month_html .= '<div class="day ' . ($active ? 'active' : 'false') . '">' . $i . '</div>';
        }

        $month_html .= '</div></div>';
        return $month_html;
    }

    public function generate_styles()
    {
?>
        <style>
            .month {
                width: 300px;
                float: left;
                margin-right: 20px;
                border: 1px solid #CCC;
            }

            header {
                text-align: center;
                padding: 8px;
                background: #f5f5f5;
                font-size: 13px;
            }

            .day-names>div {
                width: 14.28%;
                float: left;
                text-align: center;
            }

            .day {
                width: 14.28%;
                float: left;
                text-align: center;
                height: 30px;
            }

            .grey {
                background: #f7f7f7;
            }

            .false {
                background: #ffc0bd;
            }

            .active {
                background: #d3ffd0;
            }
        </style>
<?php
    }
}

new POA_Booking();
