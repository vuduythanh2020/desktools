<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('app.site_name'))</title>
    <meta name="description" content="@yield('description', __('app.site_description'))">
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
        .path-bar {
            margin: 10px 0;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid rgba(232, 216, 200, 0.7);
            background: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            font-family: "DM Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
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
            background: linear-gradient(140deg, #e49a64, #d15a35);
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
            background: var(--primary);
            border-color: var(--primary);
        }
        .card {
            background: var(--card);
            border: 1px solid rgba(232, 216, 200, 0.6);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(31, 26, 23, 0.06);
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
            background: var(--primary);
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
    <div class="shell @yield('shell_class')">
        <header>
            <div class="brand">
                <div class="logo" aria-hidden="true"></div>
                <div>
                    <h1>{{ __('app.site_name') }}</h1>
                    <p>{{ __('app.site_tagline') }}</p>
                </div>
            </div>
            <nav class="nav">
                <a href="/{{ $locale }}">{{ __('app.nav.home') }}</a>
                <a href="/{{ $locale }}/text-unescape">{{ __('app.nav.text_unescape') }}</a>
                <a href="/{{ $locale }}/csv-cleaner">{{ __('app.nav.csv_cleaner') }}</a>
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
        @yield('content')
    </div>
    @yield('scripts')
</body>
</html>
