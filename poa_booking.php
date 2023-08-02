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




        $this->calender_data = $this->generate_months_data();
        add_shortcode('poa_booking', array($this, 'shortcode'));
    }

    public function generate_months_data()
    {
        $months = array();
        foreach ($this->months_containing_startdates as $month => $dates) {
            $months[]  = $this->generate_month_data($month, $dates);
        }
        return $months;
    }
    public function shortcode()
    {


        $data = json_encode($this->calender_data);

        $this->generate_styles();
        return <<<END
            <div class='calendar'>
            <header><select id="month-select"></select></header>
            <div class="day-names"></div>
            <div class="days"></div>
            </div>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
            <script>
           
            const calendar_data = $data;

            let selectOptionsHTML =  '';
            const populateSelect = (month,index) => {
                
                let optionsHTML = '';

                calendar_data.forEach((month) => {
                    optionsHTML += '<option value="'+month.name+'">'+month.name+'</option>';
                });

                $('#month-select').html(optionsHTML);
              
         
            
            }

            
            

            const populateCalendar = (month,index) => {
                
                let daysHTML = '';

                month.days.forEach((day) => {
                    daysHTML += '<div class="day '+ day.class + '">'+day.number+'</div>';
                });

                $('.days').html(daysHTML);
              
             

            
            }




            $(() => {
                populateSelect();
                populateCalendar(calendar_data[0],0);

                $('#month-select').on('change', (e) => {
                    const index = $(e.currentTarget).find(":selected").index();
                    console.log(index);
                    populateCalendar(calendar_data[index],index);
                });
                $('body').on('mouseover','.active', (e) => {
                    const index = $(e.currentTarget).index();
                    $(e.currentTarget).addClass('hover')
                    const length = index + 6;
                    for (let x = index; x < length; x ++) {
                        $('.day').eq(x).addClass('hover');
                    }
                })
                $('body').on('mouseout','.active', (e) => {
                    $('.hover').removeClass('hover')
                })
                $('body').on('click','.active', (e) => {
                    const index = $(e.currentTarget).index();
                    const length = index + 6;
                    if( $(e.currentTarget).hasClass('selected') ) {
                        $(e.currentTarget).removeClass('selected')
                        for (let x = index; x < length; x ++) {
                            $('.day').eq(x).removeClass('selected');
                        }
                    } else {
                    $('.selected').removeClass('selected')

                    $(e.currentTarget).addClass('selected')
                  
                    for (let x = index; x < length; x ++) {
                        $('.day').eq(x).addClass('selected');
                    }
                }
                })
                $('body').on('click','.selected', (e) => {
                    $('.selected').removeClass('selected');
                })
            });

            </script>";
            END;
    }
    public function generate_month_data($month, $dates)
    {

        $data = [];


        $monthDateObj  = DateTime::createFromFormat('Y-m-d', $dates[0]);

        $data['name'] = $monthDateObj->format('F');


        $prev_month_num_days = cal_days_in_month(CAL_GREGORIAN, intval($month) - 1, 2023);

        $num_days = cal_days_in_month(CAL_GREGORIAN, $month, 2023);

        $lastDayPrevMonthObj = DateTime::createFromFormat('Y-m-d', '2023-' . strval(intval($month) - 1) . '-' . $prev_month_num_days);

        $firstDayDateObj  = DateTime::createFromFormat('Y-m-d', '2023-' . $month . '-01');

        $lastDayDateObj  = DateTime::createFromFormat('Y-m-d', '2023-' . $month . '-' . $num_days);

        $number_of_additional_days =  (intval($firstDayDateObj->format('w')) == 0) ? 6 : intval($firstDayDateObj->format('w')) - 1;

        $lastDayPrevMonth = intval($lastDayPrevMonthObj->format('d'));
        $lastDayDate =  intval($lastDayDateObj->format('w'));

        $data['days'] = [];

        for ($i = 0; $i < $number_of_additional_days; $i++) {
            $data['days'][] = array('class' => 'grey', 'number' =>  $lastDayPrevMonth - ($number_of_additional_days - $i));
        }


        for ($i = 0; $i < $num_days; $i++) {
            $dayDateObj  = DateTime::createFromFormat('Y-m-j', '2023-' . $month . '-' . $i);

            $daydate = $dayDateObj->format('Y-m-d');

            $active = false;

            if (in_array($daydate, $dates)) {
                $active = true;
            }

            $data['days'][] = array('class' => ($active ? 'active' : 'false'), 'number' => strval($i + 1));
        }

        for ($i = 0; $i < (7 - $lastDayDate); $i++) {
            $data['days'][] = array('class' =>  'grey', 'number' => strval($i + 1));
        }


        return $data;
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
                cursor: pointer;
            }

            .hover {
                background: blue;
            }

            .selected {
                background: red;
                cursor: pointer;
            }
        </style>
<?php
    }
    public function generate_script()
    {
    }
}

new POA_Booking();
