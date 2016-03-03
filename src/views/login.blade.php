@extends('stormpath::base')

@section('title', 'Log In')
@section('description', 'Log into your account!')
@section('bodytag', 'login')

@if(config('stormpath.web.social.enabled'))
    {{--*/ $socialProviders = true /*--}}
    {{--*/ $areaWrap = 'small col-sm-8' /*--}}
    {{--*/ $classLabel = 'col-sm-12' /*--}}
    {{--*/ $classInput = 'col-sm-12' /*--}}
@else
    {{--*/ $socialProviders = false /*--}}
    {{--*/ $areaWrap = 'small col-sm-12' /*--}}
    {{--*/ $classLabel = 'col-sm-4' /*--}}
    {{--*/ $classInput = 'col-sm-8' /*--}}
@endif


@section('content')
    <div class="container custom-container">
        <div class="va-wrapper">
            <div class="view login-view container">

                @include('stormpath::partials._loginStatusMessages')

                <div class="box row">
                    <div class="email-password-area col-xs-12 {{ $areaWrap }}">

                        <div class="header">
                            @if(config('stormpath.web.register.enabled'))
                                <span>Log In or <a href="{{ config('stormpath.web.register.uri') }}">Create Account</a></span>
                            @else
                                <span>Log In </span>
                            @endif
                        </div>

                        @if (isset($errors) && count($errors) > 0)
                            <div class="alert alert-danger bad-login">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ config('stormpath.web.login.uri') }}" class="login-form form-horizontal" method="post"
                              role="form">
                            {{ csrf_field() }}

                            <div class="form-group group-email">
                                <label class="{{ $classLabel }}">Email</label>
                                <div class="{{ $classInput }}">
                                    <input type="text"
                                           class="form-control"
                                           autofocus="true"
                                           placeholder="Email"
                                           name="login"
                                           value="{{ old('login') }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group group-password">
                                <label class="{{ $classLabel }}">Password</label>
                                <div class="{{ $classInput }}">
                                    <input type="password"
                                           class="form-control"
                                           placeholder="Password"
                                           name="password"
                                    >
                                </div>
                            </div>

                            <div>
                                <button class="login btn btn-login btn-sp-green" type="submit">Log In</button>
                            </div>
                        </form>
                    </div>
                    @if($socialProviders)
                        <div class="social-area col-xs-12 col-sm-4">
                            <div class="header">
                                <label>Easy 1-click login:</label>
                            </div>

                            @include('stormpath::partials.social.main')

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection