@extends('stormpath::base')

@section('title', 'Create an Account')
@section('description', 'Create a new account.')
@section('bodytag', 'register')

@section('content')
    <div class="container custom-container">
        <div class="va-wrapper">
            <div class="view registration-view container">
                <div class="box row">
                    <div class="header">
                        <span>Create Account</span>
                    </div>

                    @if (isset($errors) && count($errors) > 0)
                        <div class="alert alert-danger bad-login">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form class="registration-form form-horizontal sp-form" method="post"
                          role="form">
                        {{ csrf_field() }}

                        @foreach(config('stormpath.web.register.form.fields') as $name => $field)
                            @if($field['enabled'])
                            <div class="form-group group-{{$name}}">
                                <label class="col-sm-4">{{$field['label']}}</label>
                                <div class="col-sm-8">
                                    <input type="{{$field['type']}}"
                                           class="form-control"
                                           name="{{$name}}"
                                           placeholder="{{$field['placeholder']}}"
                                           value="{{ old($name) }}"
                                    >
                                </div>
                            </div>
                            @endif
                        @endforeach
                        <button class="btn btn-register btn-sp-green" type="submit">Create Account</button>

                    </form>

                    </div>
                @if(config('stormpath.web.login.enabled'))
                    <a href="{{config('stormpath.web.login.uri')}}" class="to-login"> Back to Login</a>
                @endif</div>
            </div>
        </div>
    </div>
@endsection