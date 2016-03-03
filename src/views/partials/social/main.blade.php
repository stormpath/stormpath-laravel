@if(config('stormpath.web.social.facebook') && config('stormpath.web.social.facebook.enabled'))
    @include('stormpath::partials.social.facebook-login-form')
@endif

@if(config('stormpath.web.social.google') && config('stormpath.web.social.google.enabled'))
    @include('stormpath::partials.social.google-login-form')
@endif

@if(config('stormpath.web.social.github') && config('stormpath.web.social.github.enabled'))
    @include('stormpath::partials.social.github-login-form')
@endif

@if(config('stormpath.web.social.linkedin') && config('stormpath.web.social.linkedin.enabled'))
    @include('stormpath::partials.social.linkedin-login-form')
@endif