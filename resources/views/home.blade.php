@extends('layouts.app')

@section('title', __('app.home.title'))
@section('description', __('app.home.description'))

@section('content')
    <div class="card">
        <h2>{{ __('app.home.heading') }}</h2>
        <p class="muted">{{ __('app.home.subheading') }}</p>
        <div class="grid" style="margin-top: 20px;">
            <div class="card">
                <h3>{{ __('app.text_unescape.title') }}</h3>
                <p class="muted">{{ __('app.text_unescape.summary') }}</p>
                <a class="btn" href="/{{ $locale }}/text-unescape">{{ __('app.actions.open_tool') }}</a>
            </div>
            <div class="card">
                <h3>{{ __('app.csv_cleaner.title') }}</h3>
                <p class="muted">{{ __('app.csv_cleaner.summary') }}</p>
                <a class="btn" href="/{{ $locale }}/csv-cleaner">{{ __('app.actions.open_tool') }}</a>
            </div>
        </div>
    </div>
@endsection
