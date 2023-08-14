<?php

trait generate_data
{
    public function select_menus()
    {
        $number_of_people = array(1 => '1 person', 2 => '2 persons', 3 => '3 persons', 4 => '4 persons', 5 => '5 persons');

        return array(
            array(
                'name' => 'position',
                'title' => 'Position',
                'options' => array(
                    'price_upper' => 'UPPER/ MAIN DECK',
                    'price_lower' => 'LOWER DECK',
                ),
                'validation-message' => 'You must select a position.'
            ),
            array(
                'name' => 'num_rooms',
                'title' => 'Number Of Cabins',
                'options' => array(),
                'validation-message' => 'You must select a number of rooms.'
            ),

        );
    }

    public function generate_pricing_data()
    {
        $start_date_array = (array)$start_date_array;

        $start_dates_by_price = array();

        foreach ($this->start_dates as $start_date_array) {



            $monthDateObj  = DateTime::createFromFormat('Y-m-d', $start_date_array->start_date);

            $month_name = $monthDateObj->format('F');

            $month  = $monthDateObj->format('m');
            $day  = $monthDateObj->format('d');




            if (!$start_dates_by_price[$start_date_array->price_upper]) {

                $start_dates_by_price[$start_date_array->price_upper] = array();
                $start_dates_by_price[$start_date_array->price_upper]['price_upper'] = $start_date_array->price_upper;
                $start_dates_by_price[$start_date_array->price_upper]['price_lower'] = $start_date_array->price_lower;
                $start_dates_by_price[$start_date_array->price_upper]['months'] = array();
            }

            if (!$start_dates_by_price[$start_date_array->price_upper]['months'][$month]) {


                $start_date_array->month = $month_name;

                $start_dates_by_price[$start_date_array->price_upper]['months'][$month] = array();
            }

            $start_date_array->day =  $day;

            $start_dates_by_price[$start_date_array->price_upper]['months'][$month][] = $start_date_array;

            ksort($start_dates_by_price[$start_date_array->price_upper]['months']);
        }
        ksort($start_dates_by_price);
        return $start_dates_by_price;
    }

    public function generate_months_data()
    {

        $years = array();


        foreach ($this->start_dates as $start_date_array) {



            $start_date_array = (array) $start_date_array;

            $month_1 = explode('-', $start_date_array['start_date'])[1];
            $month_2 = explode('-', $start_date_array['end_date'])[1];

            $year = explode('-', $start_date_array['start_date'])[0];

            if (!array_key_exists($year, $years)) {
                $years[$year] = array();
            }

            if (!array_key_exists($month_1, $years[$year])) {


                $years[$year][$month_1] = array();
            }
            if (!array_key_exists($month_2, $years[$year])) {


                $years[$year][$month_2] = array();
                $years[$year][$month_2][$start_date_array['end_date']] =   $start_date_array;
            }

            $years[$year][$month_1][$start_date_array['start_date']] =   $start_date_array;
        }


        ksort($years);




        $months = array();

        foreach ($years as $year) {
            foreach ($year as $months_array) {




                $month = $this->generate_month_data($months_array);



                array_push($months, $month);
            }
        }

        return $months;
    }

    public function generate_month_data($new_dates)
    {


        $data = [];

        $first_startdate  = array_keys($new_dates)[0];


        $monthDateObj  = DateTime::createFromFormat('Y-m-d', $first_startdate);

        $data['year'] = $monthDateObj->format('Y');

        $data['name'] = $monthDateObj->format('F');

        $data['month']  = $monthDateObj->format('m');

        $num_days =  $num_days = date('t', mktime(0, 0, 0,  $data['month'], 1, $data['year']));


        $data['prev_month_object']  = $monthDateObj->modify('first day of -1 month');

        $data['prev_month'] = $data['prev_month_object']->format('m');
        $data['prev_year'] = $data['prev_month_object']->format('Y');


        $data['next_month_object']  = $monthDateObj->modify('first day of +1 month');



        $prev_month_num_days =  $num_days = date('t', mktime(0, 0, 0,  $data['prev_month'], 1, $data['prev_year']));


        $lastDayPrevMonthObj = DateTime::createFromFormat('Y-m-d',  $data['prev_year'] . '-' .  $data['prev_month']  . '-' . $prev_month_num_days);

        $firstDayDateObj  = DateTime::createFromFormat('Y-m-d', $monthDateObj->format('Y') . '-' .  $monthDateObj->format('d') . '-01');

        $lastDayDateObj  = DateTime::createFromFormat('Y-m-d', $monthDateObj->format('Y') . '-' .  $monthDateObj->format('d') . '-' . $num_days);

        $number_of_additional_days =  (intval($firstDayDateObj->format('w')) == 0) ? 6 : intval($firstDayDateObj->format('w')) - 1;

        $lastDayPrevMonth = intval($lastDayPrevMonthObj->format('d'));




        $lastDayDate =  intval($lastDayDateObj->format('w'));

        $data['days'] = [];

        for ($i = 0; $i < $number_of_additional_days; $i++) {
            $data['days'][] = array('class' => 'grey', 'number' =>  $lastDayPrevMonth - ($number_of_additional_days - $i), 'date' => '');
        }



        for ($i = 0; $i < $num_days; $i++) {

            $dayDateObj  = DateTime::createFromFormat('Y-m-j', $monthDateObj->format('Y') . '-' . $monthDateObj->format('m') . '-' . $i + 1);

            $daydate = $dayDateObj->format('Y-m-d');


            $duration = 0;
            $is_active = false;
            foreach ($new_dates as $date) {
                if ($date['start_date'] == $daydate) {
                    $is_active = true;
                }
            }
            if ($is_active) {

                $start_date_object = DateTime::createFromFormat('Y-m-d',  $new_dates[$daydate]['start_date']);
                $end_date_object = DateTime::createFromFormat('Y-m-d',  $new_dates[$daydate]['end_date']);



                $duration = $start_date_object->diff($end_date_object)->days;


                $data['days'][] = array('enddate' => $end_date_object->format('Y-m-d'), 'class' => 'active month-day', 'number' => strval($i + 1), 'date' => $daydate, 'duration' =>  $duration, 'price_upper' => $new_dates[$daydate]['price_upper'], 'price_lower' => $new_dates[$daydate]['price_lower'], 'number_rooms' => $new_dates[$daydate]['number_rooms'], 'people_per_room' => $new_dates[$daydate]['people_per_room']);
            } else {


                $data['days'][] = array('class' =>  'false month-day', 'number' => strval($i + 1), 'date' => $daydate);
            }
        }

        for ($i = 1; $i < (7 - ($lastDayDate - 1)); $i++) {


            $nextMonthDayObj = DateTime::createFromFormat('Y-m-d',  $data['next_month_object']->format('Y') . '-' . $data['next_month_object']->format('m') . '-' . $i);

            $data['days'][] = array('class' =>  'grey', 'number' => strval($i), 'date' =>  $nextMonthDayObj->format('Y-m-d'));
        }


        return $data;
    }
}
