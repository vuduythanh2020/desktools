<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $locale = $locale ?? app()->getLocale();
        $siteName = __('app.site_name');
        $siteDescription = __('app.site_description');
        $pageTitle = trim(View::yieldContent('title', $siteName));
        if ($pageTitle && stripos($pageTitle, $siteName) === false) {
            $pageTitle .= ' | ' . $siteName;
        }
        $pageDescription = trim(View::yieldContent('description', $siteDescription));
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
        if (str_starts_with($baseUrl, 'http://')) {
            $baseUrl = 'https://' . substr($baseUrl, 7);
        }
        $path = trim(request()->path(), '/');
        $canonical = $baseUrl . ($path === '' ? '' : '/' . $path);
        $locales = ['vi', 'en'];
        $localeToOg = ['vi' => 'vi_VN', 'en' => 'en_US'];
        $segments = request()->segments();
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
    <link rel="canonical" href="{{ $canonical }}">
    @foreach ($locales as $altLocale)
        @php
            $altSegments = $segments;
            if (count($altSegments) > 0 && in_array($altSegments[0], $locales, true)) {
                $altSegments[0] = $altLocale;
            } else {
                array_unshift($altSegments, $altLocale);
            }
            $altPath = implode('/', $altSegments);
            $altUrl = $baseUrl . ($altPath === '' ? '' : '/' . $altPath);
        @endphp
        <link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altUrl }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $baseUrl }}/vi">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $localeToOg[$locale] ?? 'vi_VN' }}">
    <meta property="og:image" content="{{ $baseUrl }}/og-image.svg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $siteName }}">
    @foreach ($locales as $altLocale)
        @if ($altLocale !== $locale)
            <meta property="og:locale:alternate" content="{{ $localeToOg[$altLocale] ?? 'en_US' }}">
        @endif
    @endforeach
    <meta name="twitter:card" content="summary">
    <meta name="twitter:image" content="{{ $baseUrl }}/og-image.svg">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta name="theme-color" content="#d15a35">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $baseUrl . '/',
            'inLanguage' => $locale,
        ], JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $pageTitle,
            'description' => $pageDescription,
            'url' => $canonical,
            'inLanguage' => $locale,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $baseUrl . '/',
            ],
        ], JSON_UNESCAPED_SLASHES) !!}
    </script>
    @yield('structured_data')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6efe6;
            --bg-accent: #f2dfc8;
            --ink: #1f1a17;
            --muted: #6a5d54;
            --primary: #d15a35;
            --primary-dark: #a84528;
            --card: #fff7ef;
            --border: #e8d8c8;
            --code-bg: #201a16;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "DM Sans", system-ui, -apple-system, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1200px 600px at 10% -10%, var(--bg-accent), transparent 60%),
                radial-gradient(900px 500px at 90% 10%, #f1eadf, transparent 55%),
                var(--bg);
        }
        .skip-link {
            position: absolute;
            left: -999px;
            top: 8px;
            z-index: 999;
            background: #fff;
            color: var(--ink);
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            text-decoration: none;
            font-weight: 600;
        }
        .skip-link:focus {
            left: 12px;
        }
        .shell {
            max-width: 1560px;
            margin: 0 auto;
            padding: 20px 32px 72px;
        }
        .shell.wide {
            max-width: 1900px;
            width: min(96vw, 1900px);
        }
        .shell.focus {
            padding-top: 12px;
        }
        .shell.focus header {
            display: none;
        }
        .shell.focus .card {
            border: none;
            box-shadow: none;
            background: transparent;
            padding: 0;
        }
        .shell.focus .tool-card {
            padding: 0;
        }
        .shell.focus .row {
            grid-template-columns: repeat(auto-fit, minmax(520px, 1fr));
            gap: 32px;
        }
        .shell.focus textarea {
            min-height: 60vh;
            height: 60vh;
        }
        .workspace {
            min-height: calc(100vh - 140px);
        }
        .focus-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            border: 1px solid rgba(232, 216, 200, 0.6);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            margin-bottom: 16px;
            position: sticky;
            top: 12px;
            z-index: 5;
        }
        .focus-nav a {
            text-decoration: none;
            color: var(--ink);
            font-weight: 600;
        }
        .focus-nav .badge {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 6px 10px;
            border-radius: 999px;
            background: #fff3e7;
            border: 1px solid var(--border);
            color: var(--primary-dark);
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .focus-toolbar {
            position: sticky;
            top: 12px;
            z-index: 6;
            padding: 8px 12px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(232, 216, 200, 0.7);
            backdrop-filter: blur(10px);
        }
        .drawer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(31, 26, 23, 0.25);
            display: none;
            z-index: 30;
        }
        .drawer {
            position: fixed;
            right: 16px;
            top: 16px;
            bottom: 16px;
            width: min(380px, 90vw);
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(232, 216, 200, 0.8);
            box-shadow: 0 30px 80px rgba(31, 26, 23, 0.25);
            padding: 18px;
            overflow: auto;
            transform: translateX(110%);
            transition: transform 180ms ease;
            z-index: 31;
        }
        .drawer .row {
            grid-template-columns: 1fr !important;
            gap: 12px;
        }
        .drawer .row > div {
            min-width: 0;
        }
        .drawer select {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            display: block;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
        .drawer label {
            display: block;
        }
        .drawer.open {
            transform: translateX(0);
        }
        .drawer-backdrop.show {
            display: block;
        }
        .toolbar .group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid rgba(232, 216, 200, 0.7);
            background: rgba(255, 255, 255, 0.85);
        }
        .method-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .method-tabs .btn {
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--border);
        }
        .method-tabs .btn.active {
            background: var(--primary-dark);
            color: #fff;
            border-color: var(--primary-dark);
        }
        .method-status {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            font-size: 13px;
            color: var(--muted);
        }
        .file-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--border);
            background: #fff3e7;
            color: var(--muted);
        }
        .file-pill.active {
            color: var(--primary-dark);
        }
        .toolbar .group label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-right: 6px;
        }
        .panel {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(232, 216, 200, 0.8);
            border-radius: 14px;
            padding: 14px;
        }
        .panel h4 {
            margin: 0 0 10px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }
        details.panel {
            border: 1px solid rgba(232, 216, 200, 0.7);
            border-radius: 14px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.85);
        }
        details.panel summary {
            cursor: pointer;
            font-weight: 600;
            color: var(--muted);
            list-style: none;
        }
        details.panel summary::-webkit-details-marker { display: none; }

        .preview-fullscreen {
            position: fixed;
            inset: 16px;
            background: #fff;
            border: 1px solid rgba(232, 216, 200, 0.8);
            border-radius: 20px;
            padding: 18px;
            z-index: 20;
            box-shadow: 0 30px 80px rgba(31, 26, 23, 0.25);
            display: none;
            flex-direction: column;
            gap: 12px;
        }
        .preview-fullscreen.lucky-preview {
            inset: 5vh 5vw;
            text-align: center;
            pointer-events: auto;
        }
        .preview-fullscreen.lucky-preview .toolbar {
            justify-content: center;
            gap: 16px;
        }
        .preview-fullscreen.lucky-preview .toolbar strong {
            font-size: 42px;
        }
        .preview-fullscreen.lucky-preview .muted {
            font-size: 28px;
        }
        .preview-fullscreen.lucky-preview #ld-preview-results {
            display: flex;
            flex-direction: column;
            gap: 16px;
            align-items: center;
        }
        .preview-fullscreen.lucky-preview #ld-preview-results .panel {
            width: 90%;
        }
        .preview-fullscreen.lucky-preview #ld-preview-results .panel > div {
            justify-content: center;
        }
        .preview-fullscreen.lucky-preview .panel {
            background: #fff7ef;
            border: 1px solid rgba(232, 216, 200, 0.9);
        }
        .preview-fullscreen.lucky-preview strong {
            font-size: 34px;
        }
        .preview-fullscreen.lucky-preview .file-pill {
            font-size: 34px;
            padding: 18px 26px;
            cursor: pointer;
        }
        .preview-fullscreen.lucky-preview .file-pill.active {
            cursor: default;
        }
        .preview-fullscreen.active { display: flex; }
        .preview-fullscreen .table-wrap {
            flex: 1;
            overflow: auto;
        }
        .tree {
            border: 1px solid rgba(232, 216, 200, 0.7);
            border-radius: 14px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.9);
            max-height: 60vh;
            overflow: auto;
            font-family: "DM Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 13px;
        }
        .tree ul {
            list-style: none;
            padding-left: 18px;
            margin: 6px 0;
            border-left: 1px dashed rgba(232, 216, 200, 0.8);
        }
        .tree li { margin: 6px 0; }
        .tree button {
            border: none;
            background: transparent;
            padding: 0;
            font: inherit;
            cursor: pointer;
            color: var(--ink);
        }
        .tree .node-value {
            color: var(--ink);
            margin-left: 6px;
        }
        .tree .node-value.string {
            color: #256a48;
        }
        .tree .node-value.number {
            color: #1b5f8d;
        }
        .tree .node-value.boolean,
        .tree .node-value.null {
            color: #8b4a2e;
        }
        .tree .caret {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            margin-right: 6px;
            border-radius: 6px;
            border: 1px solid rgba(232, 216, 200, 0.9);
            background: #fff;
            font-size: 11px;
            color: var(--muted);
        }
        .tree .collapsed > ul { display: none; }
        .json-node.collapsed .json-children,
        .json-node.collapsed .json-close { display: none; }
        .json-summary { display: none; color: var(--muted); }
        .json-node.collapsed .json-summary { display: inline; }
        .json-key {
            color: var(--primary-dark);
            font-weight: 600;
        }
        .json-value.string {
            color: #256a48;
        }
        .json-value.number {
            color: #1b5f8d;
        }
        .json-value.boolean,
        .json-value.null {
            color: #8b4a2e;
        }
        .tree .node-key {
            color: var(--primary-dark);
            font-weight: 600;
        }
        .tree .node-type {
            color: var(--muted);
            margin-left: 6px;
        }
        .tree .node-button.active {
            background: #fff1e6;
            border-radius: 6px;
            padding: 2px 4px;
            box-shadow: inset 0 0 0 1px rgba(209, 90, 53, 0.18);
        }
        .path-bar {
            margin: 10px 0;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid rgba(232, 216, 200, 0.7);
            background: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            font-family: "DM Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }
        .path-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }
        .path-label {
            font-weight: 600;
            color: var(--muted);
        }
        .path-crumbs {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px;
        }
        .path-crumb {
            border: 1px solid rgba(232, 216, 200, 0.9);
            background: #fff;
            border-radius: 8px;
            padding: 2px 6px;
            cursor: pointer;
            font: inherit;
            color: var(--primary-dark);
        }
        .path-crumb.active {
            background: #fff1e6;
            border-color: rgba(209, 90, 53, 0.25);
            color: var(--primary);
        }
        .path-sep {
            color: var(--muted);
            font-size: 12px;
        }
        .path-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .path-action {
            border: 1px solid rgba(232, 216, 200, 0.9);
            background: #fff;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            color: var(--ink);
        }
        .path-action.pulse {
            background: rgba(209, 90, 53, 0.14);
            border-color: rgba(209, 90, 53, 0.35);
            color: var(--primary-dark);
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: url("/favicon.svg") center/cover no-repeat;
            box-shadow: 0 10px 20px rgba(209, 90, 53, 0.2);
        }
        .brand h1 {
            font-size: 20px;
            margin: 0;
        }
        .brand p {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 13px;
        }
        .nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .nav a {
            text-decoration: none;
            color: var(--ink);
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid var(--border);
        }
        .lang {
            display: flex;
            gap: 8px;
        }
        .lang a {
            text-decoration: none;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--muted);
            background: #fff;
        }
        .lang a.active {
            color: #fff;
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .card {
            background: var(--card);
            border: 1px solid rgba(232, 216, 200, 0.6);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(31, 26, 23, 0.06);
        }
        .site-footer {
            margin-top: 24px;
            padding: 12px 0 4px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }
        .tool-card {
            padding: 36px;
        }
        h2 {
            margin: 0 0 12px;
            font-size: 28px;
        }
        .muted { color: var(--muted); }
        .grid {
            display: grid;
            gap: 24px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary-dark);
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            border: 0;
            cursor: pointer;
            transition: transform 120ms ease, box-shadow 120ms ease, background 120ms ease;
        }
        .btn:active {
            transform: translateY(1px);
        }
        .btn.pulse {
            box-shadow: 0 0 0 4px rgba(209, 90, 53, 0.2);
        }
        .btn.secondary {
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--border);
        }
        .btn.secondary.pulse {
            box-shadow: 0 0 0 4px rgba(31, 26, 23, 0.1);
        }
        textarea, input, select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            font-size: 16px;
            font-family: "DM Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            background: #fff;
        }
        textarea {
            min-height: 520px;
        }
        label { font-weight: 600; }
        .row {
            display: grid;
            gap: 28px;
            grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
        }
        .toolbar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin: 16px 0 0;
        }
        .table-wrap {
            overflow: auto;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
        }
        .table-wrap.scroll {
            height: 55vh;
            max-height: 55vh;
        }
        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
            background: #ffe7da;
            color: #8b3a1e;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 13px;
        }
        th, td {
            border-bottom: 1px solid var(--border);
            padding: 8px 10px;
            text-align: left;
        }
        code {
            background: var(--code-bg);
            color: #fce9d8;
            padding: 2px 6px;
            border-radius: 6px;
        }
        @media (max-width: 900px) {
            header { flex-direction: column; align-items: flex-start; }
            .shell { padding: 24px 18px 56px; }
            textarea { min-height: 300px; }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to content</a>
    <div class="shell @yield('shell_class')">
        <header role="banner">
            <div class="brand">
                <div class="logo" aria-hidden="true"></div>
                <div>
                    <h1>{{ __('app.site_name') }}</h1>
                    <p>{{ __('app.site_tagline') }}</p>
                </div>
            </div>
            <nav class="nav" aria-label="Primary">
                <a href="/{{ $locale }}">{{ __('app.nav.home') }}</a>
                <a href="/{{ $locale }}/text-unescape">{{ __('app.nav.text_unescape') }}</a>
                <a href="/{{ $locale }}/csv-cleaner">{{ __('app.nav.csv_cleaner') }}</a>
                <a href="/{{ $locale }}/lucky-draw">{{ __('app.nav.lucky_draw') }}</a>
            </nav>
            <div class="lang">
                @php
                    $path = trim(request()->path(), '/');
                    $parts = $path === '' ? [$locale] : explode('/', $path);
                    $parts[0] = 'vi';
                    $viPath = implode('/', $parts);
                    $parts[0] = 'en';
                    $enPath = implode('/', $parts);
                @endphp
                <a href="/{{ $viPath }}" class="{{ $locale === 'vi' ? 'active' : '' }}">VI</a>
                <a href="/{{ $enPath }}" class="{{ $locale === 'en' ? 'active' : '' }}">EN</a>
            </div>
        </header>
        <main id="main-content" role="main">
            @yield('content')
        </main>
        <footer class="site-footer" role="contentinfo">
            <span>Desktools</span>
        </footer>
    </div>
    @yield('scripts')
</body>
</html>
