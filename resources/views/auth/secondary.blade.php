@extends('layouts.box_loggedin')

@section('content')
    <div class="container">
        <h3>Secondary Authentication</h3>
        <p>
            Hi {{$user->name_first}}, please enter your secondary password.
        </p>
        <form method="POST" action="{{ isset($postroute) ? $postroute : route('login.secondary') }}">
            @csrf

            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" required autocomplete="current-password">
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
