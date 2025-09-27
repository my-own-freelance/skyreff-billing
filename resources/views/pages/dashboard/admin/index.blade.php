@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <style>

    </style>
@endpush
@section('content')
    <h1>Dashboard admin</h1>

@endsection

@push('scripts')
    <script src="{{ asset('/dashboard/js/plugin/chart.js/chart.min.js') }}"></script>
@endpush
