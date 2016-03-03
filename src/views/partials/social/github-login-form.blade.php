<button class="btn btn-social btn-github" onClick="githubLogin()">  {{ config('stormpath.web.socialProviders.github.name') }}</button>

<script type="text/javascript">
    /**
     * Get the value of a querystring
     * @param  {String} field The field to get the value of
     * @param  {String} url   The URL to get the value from (optional)
     * @return {String}       The field value
     */

    function githubLogin() {
        var clientId = '{{ config('stormpath.web.socialProviders.github.clientId') }}';
        var scopes = '';


        var finalUrl = 'https://github.com/login/oauth/authorize?client_id=' +
                clientId +
                '&scope=user' + scopes +
                '&state={{ uniqid() }}';


        window.location = finalUrl;
    }
</script>