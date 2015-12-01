@extends('stormpath::base')

@section('title', 'Log In')
@section('description', 'Log into your account!')
@section('bodytag', 'login')

@section('content')
    <div class="container custom-container">
        <div class="va-wrapper">
            <div class="view login-view container">
                <div class="box row">
                    <div class="email-password-area col-xs-12 large col-sm-12">

                        @include('stormpath::partials._loginStatusMessages')


                        <div class="header">
                            @if(config('stormpath.web.register.enabled'))
                                Log In or <a href="{{ config('stormpath.web.register.uri') }}">Create Account</a>
                            @else
                                Log In
                            @endif
                        </div>

                        @if(isset($formErrors))
                            <div class="alert alert-danger bad-login">
                            @foreach($formErrors as $error)
                                <p>{{$error}}</p>
                            @endforeach
                            </div>
                        @endif

                        <form action="{{ config('stormpath.web.login.uri') }}" class="login-form form-horizontal" method="post"
                              role="form">
                            {{ csrf_field() }}

                            <div class="form-group group-email">
                                <label class=col-sm-4>Email</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" autofocus="true" placeholder="Email"
                                           name="login">
                                </div>
                            </div>

                            <div class="form-group group-password">
                                <label class="col-sm-4">Password</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" placeholder="Password"
                                           name="password">
                                </div>
                            </div>

                            <div class="col-sm-8">
                                <button class="login btn btn-login btn-sp-green" type="submit">Log In</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection