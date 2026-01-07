@extends('layouts.app')

@section('title', __('app.home.title'))
@section('description', __('app.home.description'))
@section('structured_data')
    @php
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
        if (str_starts_with($baseUrl, 'http://')) {
            $baseUrl = 'https://' . substr($baseUrl, 7);
        }
    @endphp
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('app.site_name') . ' tools',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => __('app.text_unescape.title'),
                    'url' => $baseUrl . '/' . $locale . '/text-unescape',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => __('app.date_converter.title'),
                    'url' => $baseUrl . '/' . $locale . '/date-converter',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => __('app.csv_cleaner.title'),
                    'url' => $baseUrl . '/' . $locale . '/csv-cleaner',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 4,
                    'name' => __('app.lucky_draw.title'),
                    'url' => $baseUrl . '/' . $locale . '/lucky-draw',
                ],
            ],
        ], JSON_UNESCAPED_SLASHES) !!}
    </script>
@endsection

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
                <h3>{{ __('app.date_converter.title') }}</h3>
                <p class="muted">{{ __('app.date_converter.summary') }}</p>
                <a class="btn" href="/{{ $locale }}/date-converter">{{ __('app.actions.open_tool') }}</a>
            </div>
            <div class="card">
                <h3>{{ __('app.csv_cleaner.title') }}</h3>
                <p class="muted">{{ __('app.csv_cleaner.summary') }}</p>
                <a class="btn" href="/{{ $locale }}/csv-cleaner">{{ __('app.actions.open_tool') }}</a>
            </div>
            <div class="card">
                <h3>{{ __('app.lucky_draw.title') }}</h3>
                <p class="muted">{{ __('app.lucky_draw.summary') }}</p>
                <a class="btn" href="/{{ $locale }}/lucky-draw">{{ __('app.actions.open_tool') }}</a>
            </div>
        </div>
    </div>
@endsection
