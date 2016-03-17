<button class="btn btn-social btn-github" onClick="githubLogin()">  {{ config('stormpath.web.social.github.name') }}</button>

<script type="text/javascript">
    function githubLogin() {
        var clientId = '{{ config('stormpath.web.social.github.clientId') }}';
        var gitHubScope = '{{ config('stormpath.web.social.github.scopes') }}';
        var oauthStateToken = '{{ uniqid() }}';

        var url = 'https://github.com/login/oauth/authorize' +
                '?client_id=' + encodeURIComponent(clientId) +
                '&scope=' + encodeURIComponent(gitHubScope) +
                '&state=' + encodeURIComponent(oauthStateToken);

        window.location = url;
    }
</script>