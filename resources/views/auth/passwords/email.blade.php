@extends('layouts.box_loggedin')

@section('content')
    <div class="container">
        <h3>Password Reset</h3>
        <p>
            Forgot your password? Click the link below, and we will send a reset link to your primary email.
        </p>
        @error('email')
        <div class="alert alert-danger" role="alert">
            You requested a password reset too recently! If you are having issues, contact the VATSIM Web Services Team.
        </div>
        @enderror

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @else

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group row mb-0">
                    <div class="col">
                        <a href="{{route('login')}}" type="submit" class="btn btn-dark">
                            {{ __('Back') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection
