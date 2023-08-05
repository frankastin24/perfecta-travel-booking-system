<div class="select">
    <label>{{$select['title']}}</label>
    <input name="{{$select['name']}}" type="hidden" />
    <button><span></span></button>
    <ul class="options">
        @foreach($select['options'] as $value => $option)
        <li data-value="{{$value}}">{{$option}}</li>
        @endforeach
    </ul>
    @if(isset($select['validation-message']))
    <p class="validation-error-message">{{$select['validation-message']}}</p>
    @endif
</div>