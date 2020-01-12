@extends('layouts.box')

@section('content')
    <div class="container">
        <h3>Secondary Authentication</h3>
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Uh oh!</h4>
            <p>It seems like the central VATSIM SSO login system is down. If you have a secondary password set on your
                account, you may log in below.</p>
        </div>
        <form method="POST" action="{{ isset($postroute) ? $postroute : route('login.secondary') }}">
            @csrf

            <div class="form-group row">
                <label for="email"
                       class="col-md-4 col-form-label text-md-right">{{ __('Primary Email Address') }}</label>

                <div class="col-md-6">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="id" class="col-md-4 col-form-label text-md-right">{{ __('VATSIM CID') }}</label>

                <div class="col-md-6">
                    <input id="id" type="number" class="form-control @error('id') is-invalid @enderror"
                           name="id" required>
                </div>
            </div>

            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" required>
                </div>
                @error('password')
                <div class="col-12">
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                </div>
                @enderror
            </div>
            <div class="form-group row mb-0 mt-4">
                <div class="col">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Login') }}
                    </button>
                </div>
            </div>
            <a href="{{route('password.request')}}">Forgot your password?</a>
        </form>
    </div>
@endsection

@section('boxFooter')
    @error('password')
    <div class="card-footer bg-danger text-white">
        Having trouble? Send an email to <a class="badge badge-light" href="mailto:web-support@vatsim.uk">web-support@vatsim.uk</a>
    </div>
    @enderror
@endsection
