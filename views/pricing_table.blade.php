<div class="table-responsive">
    <table style="width:100%;">
        <tbody>
            <tr>
                <td>Departure dates</td>
                @foreach($prices as $key => $price)
                <td>
                    @foreach($price['months'] as $month)

                    @foreach($month as $index => $start_date)

                    @if(isset( $start_date->month))
                    {{ $start_date->month}}
                    @endif
                    {{ $start_date->day}} @if($index < count($month) - 1),@endif @endforeach <br>
                        @endforeach
                </td>
                @endforeach
            </tr>
			
            @if(isset(reset($prices)['price_upper']))

            <tr>
                <td>UPPER / MAIN DECK</td>
                @foreach($prices as $price)
                <td>
                    {{$price['price_upper']}}€
                </td>
                @endforeach
            </tr>
            <tr>
                <td>LOWER DECK</td>
                @foreach($prices as $price)
                <td>
                    {{$price['price_lower']}}€
                </td>
                @endforeach
            </tr>
            <tr>
                <td>Single cabin use supplement</td>
                @foreach($prices as $price)
                <td>50%</td>
                @endforeach
            </tr>
            @endif
            @if(isset(reset($prices)['price_per_person']))

            <tr>
                <td>Price Per Person</td>
                @foreach($prices as $price)
                <td>
                    {{$price['price_per_person']}}€
                </td>
                @endforeach
            </tr>

            <tr>
                <td>Single Person Supplement</td>
                @foreach($prices as $price)
                <td>{{$price['single_person_supplement']}}€</td>
                @endforeach
            </tr>
            @endif


        </tbody>
    </table>
</div>