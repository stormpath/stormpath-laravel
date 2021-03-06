<button class="btn btn-social btn-linkedin" onClick="linkedinLogin()">  {{ config('stormpath.web.social.linkedin.name') }}</button>

<script>
    function buildUrl(baseUrl, queryString) {
        var result = baseUrl;

        if (queryString) {
            var serializedQueryString = '';

            for (var key in queryString) {
                var value = queryString[key];

                if (serializedQueryString.length) {
                    serializedQueryString += '&';
                }

                // Don't include any access_token parameters in
                // the query string as it will be added by LinkedIn.
                if (key === 'access_token') {
                    continue;
                }

                serializedQueryString += key + '=' + encodeURIComponent(value);
            }

            result += '?' + serializedQueryString;
        }

        return result;
    }

    function linkedinLogin() {
        var oauthStateToken = '#{oauthStateToken}';
        var authorizationUrl = 'https://www.linkedin.com/uas/oauth2/authorization';

        var clientId = '{{ config('stormpath.web.social.linkedin.clientId') }}';
        var redirectUri = '{{ url('/').config('stormpath.web.social.linkedin.uri') }}';

        var linkedinScope = '{{ config('stormpath.web.social.linkedin.scope') }}';

        window.location = buildUrl(authorizationUrl, {
            response_type: 'code',
            client_id: clientId,
            scope: linkedinScope,
            redirect_uri: redirectUri,
            state: oauthStateToken
        });
    }
</script>