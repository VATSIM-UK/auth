@extends('layouts.app')

@section('content')
    <div class="container py-4" id="app">
        <app></app>
    </div>
@endsection

@push('afterBody')
    <script>
        var apiUri = "{{ url('/api') }}";
        var csrf = "{{ csrf_token() }}";
        window.dataIsLoading = false;
    </script>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/libraries.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
@endpush
