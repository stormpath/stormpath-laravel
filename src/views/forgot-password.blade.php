@extends('stormpath::base')

@section('title', 'Forgot Your Password?')
@section('description', 'Forgot your password? No worries!')
@section('bodytag', 'login')

@section('content')
    <div class="container custom-container">
        <div class="va-wrapper">
            <div class="view registration-view container">

                @if(isset($status) && $status == 'invalid_sptoken')
                    <div class="row">
                        <div class="alert alert-warning invalid-sp-token-warning">
                            <p>
                                The password reset link you tried to use is no longer valid.
                                Please request a new link from the form below.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="box row">
                    <div class="email-password-area col-xs-12 large col-sm-12">

                        <div class="header">
                            <span>Forgot your password?</span>
                            <p>
                                Enter your email address below to reset your password. You will
                                be sent an email which you will need to open to continue. You may
                                need to check your spam folder.
                            </p>
                        </div>

                        @if (isset($errors) && count($errors) > 0)
                            <div class="alert alert-danger bad-login">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        
                        <form action="{{config('stormpath.web.forgotPassword.uri')}}" class="login-form form-horizontal" method="post" role="form">
                            {{ csrf_field() }}

                            <div class="form-group group-email">
                                <label for="" class="col-sm-4">Email</label>
                                <div class="col-sm-8">
                                    <input type="text"
                                           class="form-control"
                                           placeholder="Email"
                                           required="true"
                                           name="email"
                                           {{ old('email') }}
                                    >
                                </div>
                            </div>

                            <div>
                                <button class="login btn btn-login btn-sp-green" type="submit">Send Email</button>
                            </div>
                        </form>

                    </div>
                    @if(config('stormpath.web.login.enabled'))
                        <a href="{{config('stormpath.web.login.uri')}}" class="to-login"> Back to Login</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection