<form id="ws-payment-form" method="POST" action="https://formtest.wspay.biz/authorization.aspx">


    @foreach ($address as $address_field_name => $address_field)
    <input class="{{$address_field[2]}}" type="hidden" name="{{$address_field_name}}" />
    @endforeach

    <input type="hidden" name="ShopID" value="{{$ws_pay[0]}}">
    <input type="hidden" name="ShoppingCartID" value="{{$ws_pay[1]}}">
    <input type="hidden" name="Version" value="2.0">
    <input type="hidden" name="TotalAmount" value="{{$ws_pay[2]}}">
    <input type="hidden" name="Signature" value="{{$ws_pay[3]}}">

    <input type="hidden" name="ReturnURL" value="https://indian-matrimony.co.uk/checkout">
    <input type="hidden" name="CancelURL" value="https://indian-matrimony.co.uk/checkout">
    <input type="hidden" name="ReturnErrorURL" value="https://indian-matrimony.co.uk/checkout">

    <input value="Contiue to payment" type="submit" />
</form>