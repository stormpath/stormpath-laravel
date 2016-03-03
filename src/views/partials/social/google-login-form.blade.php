<button class="btn btn-social btn-google" onClick="googleLogin()"> {{ config('stormpath.web.social.google.name') }}</button>

<script type="text/javascript">
    /**
     * Get the value of a querystring
     * @param  {String} field The field to get the value of
     * @param  {String} url   The URL to get the value from (optional)
     * @return {String}       The field value
     */

    function googleLogin() {
        var clientId = '{{ config('stormpath.web.social.google.clientId') }}';
        var googleScopes = '{{ config('stormpath.web.social.google.scope') }}';
        var hd = '';
        var scopes = '';

        if (googleScopes.length) {
            scopes = googleScopes.split(',').join('+');
        }

        var finalUrl = 'https://accounts.google.com/o/oauth2/auth?response_type=code&client_id=' +
                clientId +
                '&scope=' + scopes +
                '&include_granted_scopes=true&redirect_uri=' +
                '{{ config('stormpath.web.social.google.callbackUri') }}';

        if (hd) {
            finalUrl = finalUrl + '&hd=' + hd;
        }

        window.location = finalUrl;
    }
</script>