<div class="table-responsive">
    <table style="width:100%;">
        <tbody>
            <tr>
                <td>Departure dates</td>
                @foreach($prices as $price)
                <td>
                    @foreach($price['months'] as $month)

                    @foreach($month as $start_date)
                    @if(isset( $start_date->month))
                    {{ $start_date->month}}
                    @endif
                    {{ $start_date->day}},
                    @endforeach

                    @endforeach
                </td>
                @endforeach
            </tr>
            <tr>
                <td>UPPER / MAIN DECK</td>
                @foreach($prices as $price)
                <td>
                    {{$price['price_upper']}}
                </td>
                @endforeach
            </tr>
            <tr>
                <td>LOWER DECK</td>
                @foreach($prices as $price)
                <td>
                    {{$price['price_lower']}}
                </td>
                @endforeach
            </tr>
            <tr>
                <td>Single cabin use supplement</td>
                @foreach($prices as $price)
                <td>50%</td>
                @endforeach
            </tr>

        </tbody>
    </table>
</div>