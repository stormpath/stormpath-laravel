<button class="btn btn-social btn-facebook" onClick="facebookLogin()"> {{ config('stormpath.web.social.facebook.name') }}</button>

<script>
    function facebookLogin() {
        var FB = window.FB;
        var scopes = '{{ config('stormpath.web.social.facebook.scope') }}';



        FB.login(function (response) {
            if (response.status === 'connected') {
                var queryStr = window.location.search.replace('?', '');
                // TODO make dynamic
                if (queryStr) {
                    // Don't include any access_token parameters in
                    // the query string as it will be added by us.
                    queryStr = queryStr.replace(/(&?)access_token=([^&]*)/, '');

                    window.location.replace('/callbacks/facebook?' + queryStr + '&access_token=' + response.authResponse.accessToken);
                } else {
                    window.location.replace('/callbacks/facebook?access_token=' + response.authResponse.accessToken);
                }
            }
        }, {scope: scopes});
    }

    window.fbAsyncInit = function () {
        FB.init({
            appId      : '{{config('stormpath.web.social.facebook.clientId') }}',
            cookie     : true,
            xfbml      : true,
            version    : 'v2.3'
        });
    };

    (function (d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>