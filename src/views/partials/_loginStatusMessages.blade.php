@if(isset($status) && View::exists("stormpath::partials.login.{$status}"))
    <div class="header">
        @include("stormpath::partials.login.{$status}")
    </div>
@endif