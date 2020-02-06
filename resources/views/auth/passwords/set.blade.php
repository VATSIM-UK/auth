@extends('layouts.box_loggedin')

@section('content')
    <div class="container">
        <h3>Set A Secondary Password</h3>
        <p>
            @if(\Illuminate\Support\Facades\Auth::user()->hasPassword())
                Your secondary password has expired. Please create a new one.
            @else
                For security purposes, due to your allocated roles, you are required to set a secondary password below.
            @endif
        </p>
        <form method="POST" action="{{ route('login.password.set') }}">
            @csrf

            @if(\Illuminate\Support\Facades\Auth::user()->hasPassword())
                <div class="form-group row">
                    <label for="old_password"
                           class="col-md-4 col-form-label text-md-right">{{ __('Current Password') }}</label>

                    <div class="col-md-6">
                        <input id="old_password"
                               type="password"
                               class="form-control @error('old_password') is-invalid @enderror"
                               name="old_password"
                               required>

                        @error('old_password')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password"
                           type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password"
                           required
                           autocomplete="new-password">

                    @error('password')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror
                </div>
            </div>
            <small class="form-text text-muted">Passwords must be at least 8 characters long, containing a uppercase and
                a lowercase letter, as well as a number.</small>

            <div class="form-group row">
                <label for="password-confirm"
                       class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                <div class="col-md-6">
                    <input type="password"
                           class="form-control"
                           name="password_confirmation"
                           required>
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Set Password') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
