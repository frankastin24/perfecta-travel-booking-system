<div class="poa-booking">
    @include('components.calendar')
    <div class="form">
        <div class="fields">
            <div class="input">
                <label>Dates From To</label>
                <p class="dates-from-to"></p>
            </div>

            @foreach($selects as $index => $select)
            @component('components.select', array('index' => $index, 'select' => $select))
            @endcomponent
            @endforeach
        </div>
        <div class="total">
            <h1>Total €<span class="total-amount">0.00</span></h1>
        </div>

        <form name="pay" action="/placanje/" method="POST">
            <input type="submit" value="Continue to checkout">

        </form>

    </div>

</div>