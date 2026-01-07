@extends('layouts.app')

@section('title', __('app.csv_cleaner.page_title'))
@section('description', __('app.csv_cleaner.description'))
@section('shell_class', 'wide focus')
@section('structured_data')
    @php
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
        if (str_starts_with($baseUrl, 'http://')) {
            $baseUrl = 'https://' . substr($baseUrl, 7);
        }
        $pageUrl = $baseUrl . '/' . $locale . '/csv-cleaner';
    @endphp
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => __('app.csv_cleaner.title'),
            'description' => __('app.csv_cleaner.description'),
            'applicationCategory' => 'DeveloperApplication',
            'operatingSystem' => 'All',
            'url' => $pageUrl,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ], JSON_UNESCAPED_SLASHES) !!}
    </script>
@endsection

@section('content')
    <div class="focus-nav">
        <a href="/{{ $locale }}">← {{ __('app.nav.home') }}</a>
        <span class="badge">{{ __('app.csv_cleaner.title') }}</span>
    </div>
    <h1 class="sr-only">{{ __('app.csv_cleaner.title') }}</h1>
    <div class="card tool-card workspace">
        <div class="toolbar focus-toolbar" style="margin-bottom: 16px;">
            <div class="group">
                <input type="file" id="csv-file" accept=".csv,.xlsx" />
                <button id="csv-load" class="btn">{{ __('app.actions.load') }}</button>
            </div>
            <div class="group">
                <button id="csv-run" class="btn">{{ __('app.actions.clean') }}</button>
                <button id="csv-download" class="btn secondary">{{ __('app.actions.download') }}</button>
                <button id="csv-preview-full" class="btn secondary">{{ __('app.actions.full_preview') }}</button>
                <button id="csv-options-open" class="btn secondary">{{ __('app.actions.options') }}</button>
            </div>
        </div>
        <div class="row">
            <div>
                <label for="csv-input">{{ __('app.csv_cleaner.input') }}</label>
                <textarea id="csv-input" rows="10" placeholder="{{ __('app.csv_cleaner.placeholder') }}"></textarea>
            </div>
            <div>
                <label>Preview</label>
                <p class="muted" id="csv-stats"></p>
                <div class="table-wrap scroll">
                    <table id="csv-preview"></table>
                </div>
            </div>
        </div>
    </div>
    <div class="preview-fullscreen" id="csv-preview-fullscreen">
        <div class="toolbar">
            <strong>Preview</strong>
            <button id="csv-preview-close" class="btn secondary">{{ __('app.actions.close') }}</button>
        </div>
        <p class="muted" id="csv-stats-full"></p>
        <div class="table-wrap scroll">
            <table id="csv-preview-full-table"></table>
        </div>
    </div>
    <div class="drawer-backdrop" id="csv-options-backdrop"></div>
    <div class="drawer" id="csv-options-drawer">
        <div class="toolbar" style="justify-content: space-between;">
            <strong>{{ __('app.csv_cleaner.panel_clean') }}</strong>
            <button id="csv-options-close" class="btn secondary">{{ __('app.actions.close') }}</button>
        </div>
        <div style="margin-top: 16px;">
            <div class="panel">
                <h4>{{ __('app.csv_cleaner.panel_clean') }}</h4>
                <div class="row">
                    <label><input type="checkbox" id="opt-trim" checked> {{ __('app.csv_cleaner.trim') }}</label>
                    <label><input type="checkbox" id="opt-empty" checked> {{ __('app.csv_cleaner.remove_empty') }}</label>
                    <label><input type="checkbox" id="opt-dup"> {{ __('app.csv_cleaner.remove_duplicates') }}</label>
                    <label><input type="checkbox" id="opt-phone"> {{ __('app.csv_cleaner.normalize_phone') }}</label>
                    <label><input type="checkbox" id="opt-email"> {{ __('app.csv_cleaner.normalize_email') }}</label>
                </div>
            </div>
            <div class="panel" style="margin-top: 16px;">
                <h4>{{ __('app.csv_cleaner.panel_format') }}</h4>
                <div class="row">
                    <div>
                        <label>{{ __('app.csv_cleaner.duplicate_row') }}</label>
                        <div class="muted" style="margin-top: 6px;">{{ __('app.csv_cleaner.duplicate_row_help') }}</div>
                    </div>
                </div>
                <div class="row" style="margin-top: 12px;">
                    <div>
                        <label for="phone-column">{{ __('app.csv_cleaner.phone_column') }}</label>
                        <select id="phone-column"></select>
                    </div>
                    <div>
                        <label for="phone-format">{{ __('app.csv_cleaner.phone_format') }}</label>
                        <select id="phone-format">
                            <option value="084">0xxxxxxxxx</option>
                            <option value="+84">+84xxxxxxxxx</option>
                        </select>
                    </div>
                    <div>
                        <label for="email-column">{{ __('app.csv_cleaner.email_column') }}</label>
                        <select id="email-column"></select>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/vendor/xlsx.full.min.js"></script>
    <script>
        const input = document.getElementById('csv-input');
        const file = document.getElementById('csv-file');
        const loadBtn = document.getElementById('csv-load');
        const runBtn = document.getElementById('csv-run');
        const downloadBtn = document.getElementById('csv-download');
        const preview = document.getElementById('csv-preview');
        const stats = document.getElementById('csv-stats');
        const fullBtn = document.getElementById('csv-preview-full');
        const fullWrap = document.getElementById('csv-preview-fullscreen');
        const fullClose = document.getElementById('csv-preview-close');
        const fullStats = document.getElementById('csv-stats-full');
        const fullTable = document.getElementById('csv-preview-full-table');
        const optionsOpen = document.getElementById('csv-options-open');
        const optionsClose = document.getElementById('csv-options-close');
        const optionsDrawer = document.getElementById('csv-options-drawer');
        const optionsBackdrop = document.getElementById('csv-options-backdrop');

        const optTrim = document.getElementById('opt-trim');
        const optEmpty = document.getElementById('opt-empty');
        const optDup = document.getElementById('opt-dup');
        const optPhone = document.getElementById('opt-phone');
        const optEmail = document.getElementById('opt-email');

        const phoneColumn = document.getElementById('phone-column');
        const phoneFormat = document.getElementById('phone-format');
        const emailColumn = document.getElementById('email-column');

        let parsed = { headers: [], rows: [], delimiter: ',' };
        let cleaned = { headers: [], rows: [], delimiter: ',' };

        function countSeparator(line, separator) {
            let count = 0;
            let inQuotes = false;
            for (let i = 0; i < line.length; i++) {
                const char = line[i];
                if (char === '"' && line[i + 1] === '"') {
                    i++;
                } else if (char === '"') {
                    inQuotes = !inQuotes;
                } else if (char === separator && !inQuotes) {
                    count++;
                }
            }
            return count;
        }

        function detectDelimiter(text) {
            const candidates = [',', ';', '\t', '|'];
            const lines = text.split(/\r?\n/).slice(0, 5);
            let best = { delimiter: ',', score: -1 };
            for (const delimiter of candidates) {
                let score = 0;
                for (const line of lines) {
                    score += countSeparator(line, delimiter);
                }
                if (score > best.score) {
                    best = { delimiter, score };
                }
            }
            return best.score > 0 ? best.delimiter : ',';
        }

        function parseCsv(text, delimiter) {
            const rows = [];
            let current = '';
            let row = [];
            let inQuotes = false;
            for (let i = 0; i < text.length; i++) {
                const char = text[i];
                const next = text[i + 1];
                if (char === '"' && inQuotes && next === '"') {
                    current += '"';
                    i++;
                } else if (char === '"') {
                    inQuotes = !inQuotes;
                } else if (char === delimiter && !inQuotes) {
                    row.push(current);
                    current = '';
                } else if ((char === '\n' || char === '\r') && !inQuotes) {
                    if (char === '\r' && next === '\n') i++;
                    row.push(current);
                    rows.push(row);
                    row = [];
                    current = '';
                } else {
                    current += char;
                }
            }
            row.push(current);
            rows.push(row);
            return rows.filter((r) => r.length > 1 || r[0] !== '');
        }

        function toCsv(headers, rows, delimiter) {
            const escapeCell = (cell) => {
                const value = String(cell ?? '');
                if (value.includes('"') || value.includes(delimiter) || value.includes('\n')) {
                    return '"' + value.replace(/"/g, '""') + '"';
                }
                return value;
            };
            const lines = [];
            lines.push(headers.map(escapeCell).join(delimiter));
            for (const row of rows) {
                lines.push(row.map(escapeCell).join(delimiter));
            }
            return lines.join('\n');
        }

        function normalizePhone(value, format) {
            const digits = String(value ?? '').replace(/\\D/g, '');
            if (!digits) return '';
            let core = digits;
            if (digits.startsWith('84')) {
                core = digits.slice(2);
            } else if (digits.startsWith('0')) {
                core = digits.slice(1);
            }
            if (format === '+84') {
                return '+84' + core;
            }
            return '0' + core;
        }

        function normalizeEmail(value) {
            return String(value ?? '').trim().toLowerCase();
        }

        function populateColumns(headers) {
            const options = headers.map((h, idx) => `<option value="${idx}">${h || 'Column ' + (idx + 1)}</option>`);
            phoneColumn.innerHTML = options.join('');
            emailColumn.innerHTML = options.join('');
        }

        function updatePreview(headers, rows) {
            const head = '<tr>' + headers.map((h) => `<th>${h}</th>`).join('') + '</tr>';
            const body = rows.map((r) => {
                return '<tr>' + r.map((c) => `<td>${c}</td>`).join('') + '</tr>';
            }).join('');
            preview.innerHTML = head + body;
            fullTable.innerHTML = head + body;
        }

        function cleanCsv() {
            const rows = parsed.rows.map((row) => row.slice());
            if (optTrim.checked) {
                for (const row of rows) {
                    for (let i = 0; i < row.length; i++) {
                        row[i] = String(row[i] ?? '').trim();
                    }
                }
            }
            let filtered = rows;
            if (optEmpty.checked) {
                filtered = filtered.filter((row) => row.some((cell) => String(cell ?? '').trim() !== ''));
            }
            if (optPhone.checked) {
                const idx = Number(phoneColumn.value || 0);
                filtered.forEach((row) => {
                    row[idx] = normalizePhone(row[idx], phoneFormat.value);
                });
            }
            if (optEmail.checked) {
                const idx = Number(emailColumn.value || 0);
                filtered.forEach((row) => {
                    row[idx] = normalizeEmail(row[idx]);
                });
            }
            if (optDup.checked) {
                const seen = new Set();
                filtered = filtered.filter((row) => {
                    const key = row.join('|');
                    if (seen.has(key)) return false;
                    seen.add(key);
                    return true;
                });
            }
            cleaned = { headers: parsed.headers, rows: filtered, delimiter: parsed.delimiter };
            updatePreview(cleaned.headers, cleaned.rows);
            const msg = `{{ __('app.csv_cleaner.stats') }}`.replace('{before}', parsed.rows.length).replace('{after}', cleaned.rows.length);
            if (parsed.rows.length !== cleaned.rows.length) {
                stats.innerHTML = `${msg}<span class="stat-change">Δ ${cleaned.rows.length - parsed.rows.length}</span>`;
                fullStats.innerHTML = `${msg}<span class="stat-change">Δ ${cleaned.rows.length - parsed.rows.length}</span>`;
            } else {
                stats.textContent = msg;
                fullStats.textContent = msg;
            }
        }

        function readInputText() {
            const raw = input.value.trim();
            if (!raw) return;
            const delimiter = detectDelimiter(raw);
            const rows = parseCsv(raw, delimiter);
            const headers = rows.shift() || [];
            headers[0] = headers[0]?.replace(/^\\uFEFF/, '');
            parsed = { headers, rows, delimiter };
            populateColumns(headers);
            cleaned = { headers, rows, delimiter };
            updatePreview(headers, rows);
            const msg = `{{ __('app.csv_cleaner.stats') }}`.replace('{before}', rows.length).replace('{after}', rows.length);
            stats.textContent = msg;
            fullStats.textContent = msg;
        }

        loadBtn.addEventListener('click', () => readInputText());
        let inputTimer = null;
        input.addEventListener('input', () => {
            if (inputTimer) clearTimeout(inputTimer);
            inputTimer = setTimeout(() => readInputText(), 300);
        });
        runBtn.addEventListener('click', () => cleanCsv());

        file.addEventListener('change', () => {
            const chosen = file.files[0];
            if (!chosen) return;
            const lower = chosen.name.toLowerCase();
            if (lower.endsWith('.xlsx')) {
                const reader = new FileReader();
                reader.onload = () => {
                    const data = new Uint8Array(reader.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheet];
                    const csv = XLSX.utils.sheet_to_csv(worksheet);
                    input.value = csv;
                    readInputText();
                };
                reader.readAsArrayBuffer(chosen);
            } else {
                const reader = new FileReader();
                reader.onload = () => {
                    input.value = reader.result;
                    readInputText();
                };
                reader.readAsText(chosen);
            }
        });

        downloadBtn.addEventListener('click', () => {
            if (!cleaned.rows.length) return;
            const csv = toCsv(cleaned.headers, cleaned.rows, cleaned.delimiter);
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'cleaned.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });

        fullBtn.addEventListener('click', () => {
            fullWrap.classList.add('active');
        });
        fullClose.addEventListener('click', () => {
            fullWrap.classList.remove('active');
        });

        optionsOpen.addEventListener('click', () => {
            if (parsed.headers.length) {
                populateColumns(parsed.headers);
            }
            optionsDrawer.classList.add('open');
            optionsBackdrop.classList.add('show');
        });
        optionsClose.addEventListener('click', () => {
            optionsDrawer.classList.remove('open');
            optionsBackdrop.classList.remove('show');
        });
        optionsBackdrop.addEventListener('click', () => {
            optionsDrawer.classList.remove('open');
            optionsBackdrop.classList.remove('show');
        });
    </script>
@endsection
