@if(config('stormpath.web.socialProviders.facebook') && config('stormpath.web.socialProviders.facebook.enabled'))
    @include('stormpath::partials.social.facebook-login-form')
@endif

@if(config('stormpath.web.socialProviders.google') && config('stormpath.web.socialProviders.google.enabled'))
    @include('stormpath::partials.social.google-login-form')
@endif

@if(config('stormpath.web.socialProviders.github') && config('stormpath.web.socialProviders.github.enabled'))
    @include('stormpath::partials.social.github-login-form')
@endif

@if(config('stormpath.web.socialProviders.linkedin') && config('stormpath.web.socialProviders.linkedin.enabled'))
    @include('stormpath::partials.social.linkedin-login-form')
@endif