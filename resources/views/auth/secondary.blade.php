@extends('layouts.box')

@section('slickNav')
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" href="{{route('logout')}}">Logout</a>
        </li>
    </ul>
@endsection

@section('content')
    <div class="container">
        <h3>Secondary Authentication</h3>
        <p>
            Please enter your secondary password.
        </p>
        <form method="POST" action="{{ route('login.secondary') }}">
            @csrf

            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror
                </div>
            </div>
            <div class="form-group row mb-0 mt-4">
                <div class="col">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Login') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
