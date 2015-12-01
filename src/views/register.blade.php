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

                    <form class="registration-form form-horizontal sp-form" method="post"
                          role="form">
                        {{ csrf_field() }}

                        @foreach(config('stormpath.web.register.fields') as $field)
                            <div class="form-group group-{{$field['name']}}">
                                <label class="col-sm-4">{{$field['placeholder']}}</label>
                                <div class="col-sm-8">
                                    <input type="{{$field['type']}}" class="form-control" required="{{$field['required']}}" name="{{$field['name']}}" placeholder="{{$field['placeholder']}}">
                                </div>
                            </div>
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