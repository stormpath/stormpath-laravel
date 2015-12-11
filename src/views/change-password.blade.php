@extends('stormpath::base')

@section('title', 'Change Your Password')
@section('description', 'Change your password here.')
@section('bodytag', 'login')

@section('content')
    <div class="container custom-container">
        <div class="va-wrapper">
            <div class="view registration-view container">
                <div class="box row">
                    <div class="email-password-area col-xs-12 large col-sm-12">

                        <div class="header">
                            <span>Change Your Password</span>
                            <p>
                                Enter your new account password below.  Once confirmed,
                                you'll be logged into your account and your new password will be
                                active.
                            </p>
                        </div>

                        @if (isset($errors) && count($errors) > 0)
                            <div class="alert alert-danger bad-login">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form class="login-form form-horizontal" method="post" role="form">
                            {{ csrf_field() }}

                            <div class="form-group group-password">
                                <label for="" class="col-sm-4">Password</label>

                                <div class="col-sm-8">
                                    <input type="password"
                                           class="form-control"
                                           placeholder="Password"
                                           required="true"
                                           name="password"
                                    >
                                </div>
                            </div>

                            <div class="form-group group-password">
                                <label for="" class="col-sm-4">Password (again)</label>

                                <div class="col-sm-8">
                                    <input type="password"
                                           class="form-control"
                                           placeholder="Password (again)"
                                           required="true"
                                           name="password_confirmation"
                                    >
                                </div>
                            </div>

                            <div>
                                <button class="login btn btn-login btn-sp-green" type="submit">Submit</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection