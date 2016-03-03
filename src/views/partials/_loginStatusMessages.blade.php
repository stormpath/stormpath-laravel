@if(isset($status) && View::exists("stormpath::partials.login.{$status}"))
    <div class="box row">
        <div class="email-password-area col-xs-12 large col-sm-12">
            <div class="header">
                @include("stormpath::partials.login.{$status}")
            </div>
        </div>
    </div>

    <br />

@endif