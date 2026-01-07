@extends('layouts.app')

@section('title', __('app.date_converter.page_title'))
@section('description', __('app.date_converter.description'))
@section('shell_class', 'wide focus')
@section('structured_data')
    @php
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
        if (str_starts_with($baseUrl, 'http://')) {
            $baseUrl = 'https://' . substr($baseUrl, 7);
        }
        $pageUrl = $baseUrl . '/' . $locale . '/date-converter';
    @endphp
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => __('app.date_converter.title'),
            'description' => __('app.date_converter.description'),
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
        <span class="badge">{{ __('app.date_converter.title') }}</span>
    </div>
    <h1 class="sr-only">{{ __('app.date_converter.title') }}</h1>
    <div class="card tool-card workspace">
        <div class="toolbar focus-toolbar" style="margin-bottom: 16px;">
            <div class="group">
                <label for="dc-input-mode">{{ __('app.date_converter.input_mode') }}</label>
                <select id="dc-input-mode">
                    <option value="auto">{{ __('app.date_converter.input_modes.auto') }}</option>
                    <option value="date">{{ __('app.date_converter.input_modes.date') }}</option>
                    <option value="timestamp_s">{{ __('app.date_converter.input_modes.timestamp_s') }}</option>
                    <option value="timestamp_ms">{{ __('app.date_converter.input_modes.timestamp_ms') }}</option>
                </select>
            </div>
            <div class="group">
                <label for="dc-date-format">{{ __('app.date_converter.date_format') }}</label>
                <select id="dc-date-format">
                    <option value="auto">{{ __('app.date_converter.date_formats.auto') }}</option>
                    <option value="ymd">{{ __('app.date_converter.date_formats.ymd') }}</option>
                    <option value="dmy_slash">{{ __('app.date_converter.date_formats.dmy_slash') }}</option>
                    <option value="dmy_dash">{{ __('app.date_converter.date_formats.dmy_dash') }}</option>
                </select>
            </div>
            <div class="group">
                <label for="dc-timezone">{{ __('app.date_converter.timezone') }}</label>
                <div style="display: flex; gap: 8px; min-width: 260px;">
                    <select id="dc-timezone">
                        <option value="local">{{ __('app.date_converter.timezones.local') }}</option>
                        <option value="utc">{{ __('app.date_converter.timezones.utc') }}</option>
                        <option value="offset">{{ __('app.date_converter.timezones.offset') }}</option>
                    </select>
                    <input id="dc-offset" type="text" placeholder="{{ __('app.date_converter.offset_placeholder') }}" style="max-width: 140px;">
                </div>
            </div>
            <div class="group">
                <label>{{ __('app.date_converter.actions') }}</label>
                <button id="dc-run" class="btn">{{ __('app.actions.convert') }}</button>
                <button id="dc-swap" class="btn secondary">{{ __('app.actions.swap') }}</button>
                <button id="dc-now" class="btn secondary">{{ __('app.date_converter.action_now') }}</button>
            </div>
            <details class="panel">
                <summary>{{ __('app.date_converter.panel_tips') }}</summary>
                <p class="muted" style="margin-top: 8px;">{{ __('app.date_converter.tips') }}</p>
            </details>
        </div>
        <div class="row">
            <div>
                <label for="dc-input">{{ __('app.date_converter.input_label') }}</label>
                <input id="dc-input" type="text" placeholder="{{ __('app.date_converter.input_placeholder') }}">
                <div style="display: grid; gap: 12px; margin-top: 12px;">
                    <div>
                        <label for="dc-picker">{{ __('app.date_converter.picker_label') }}</label>
                        <input id="dc-picker" type="datetime-local">
                    </div>
                </div>
                <p id="dc-warning" class="muted" style="margin-top: 12px;"></p>
                <p id="dc-error" class="muted" style="margin-top: 6px; color: #a84528;"></p>
            </div>
            <div>
                <label>{{ __('app.date_converter.output_label') }}</label>
                <div style="display: grid; gap: 16px; margin-top: 8px;">
                    <div style="display: grid; gap: 8px;">
                        <div class="toolbar" style="margin: 0; justify-content: space-between;">
                            <strong>{{ __('app.date_converter.timestamp_seconds') }}</strong>
                            <button class="btn secondary dc-copy" data-copy-target="dc-ts-seconds">{{ __('app.actions.copy') }}</button>
                        </div>
                        <input id="dc-ts-seconds" type="text" readonly>
                    </div>
                    <div style="display: grid; gap: 8px;">
                        <div class="toolbar" style="margin: 0; justify-content: space-between;">
                            <strong>{{ __('app.date_converter.timestamp_milliseconds') }}</strong>
                            <button class="btn secondary dc-copy" data-copy-target="dc-ts-ms">{{ __('app.actions.copy') }}</button>
                        </div>
                        <input id="dc-ts-ms" type="text" readonly>
                    </div>
                    <div>
                        <div class="toolbar" style="margin: 0 0 8px; justify-content: space-between;">
                            <strong>{{ __('app.date_converter.formats_heading') }}</strong>
                        </div>
                        <div id="dc-formats" style="display: grid; gap: 12px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const input = document.getElementById('dc-input');
        const picker = document.getElementById('dc-picker');
        const inputMode = document.getElementById('dc-input-mode');
        const dateFormat = document.getElementById('dc-date-format');
        const timezone = document.getElementById('dc-timezone');
        const offsetInput = document.getElementById('dc-offset');
        const runBtn = document.getElementById('dc-run');
        const swapBtn = document.getElementById('dc-swap');
        const nowBtn = document.getElementById('dc-now');
        const warningEl = document.getElementById('dc-warning');
        const errorEl = document.getElementById('dc-error');
        const tsSeconds = document.getElementById('dc-ts-seconds');
        const tsMs = document.getElementById('dc-ts-ms');
        const formatsWrap = document.getElementById('dc-formats');
        const copyLabel = `{{ __('app.actions.copy') }}`;
        const copiedLabel = `{{ __('app.actions.copied') }}`;
        const invalidInput = `{{ __('app.date_converter.error_invalid') }}`;
        const invalidOffset = `{{ __('app.date_converter.error_offset') }}`;
        const warningAmbiguous = `{{ __('app.date_converter.warning_ambiguous') }}`;
        const monthLabels = @json(__('app.date_converter.months_short'));
        const locale = `{{ $locale }}`;

        let lastResult = null;
        let copyTimer = null;

        const formatItems = [
            { id: 'iso', label: `{{ __('app.date_converter.format_iso') }}` },
            { id: 'ymd', label: `{{ __('app.date_converter.format_ymd') }}` },
            { id: 'dmy-slash', label: `{{ __('app.date_converter.format_dmy_slash') }}` },
            { id: 'dmy-time', label: `{{ __('app.date_converter.format_dmy_dash_time') }}` },
            { id: 'ymd-time', label: `{{ __('app.date_converter.format_ymd_time_slash') }}` },
            { id: 'human', label: `{{ __('app.date_converter.format_human') }}` },
        ];

        function pad2(value) {
            return String(value).padStart(2, '0');
        }

        function pad3(value) {
            return String(value).padStart(3, '0');
        }

        function toLocalPickerValue(date) {
            return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}T${pad2(date.getHours())}:${pad2(date.getMinutes())}`;
        }

        function formatOffset(minutes) {
            const sign = minutes >= 0 ? '+' : '-';
            const abs = Math.abs(minutes);
            const hours = Math.floor(abs / 60);
            const mins = abs % 60;
            return `${sign}${pad2(hours)}:${pad2(mins)}`;
        }

        function parseOffset(value) {
            const raw = value.trim();
            if (!raw) return null;
            const match = raw.match(/^([+-])(\d{1,2})(?::?(\d{2}))?$/);
            if (!match) return null;
            const sign = match[1] === '-' ? -1 : 1;
            const hours = Number(match[2]);
            const minutes = match[3] ? Number(match[3]) : 0;
            if (Number.isNaN(hours) || Number.isNaN(minutes)) return null;
            if (hours > 14 || minutes > 59) return null;
            return sign * (hours * 60 + minutes);
        }

        function getTimezoneConfig() {
            const mode = timezone.value;
            if (mode === 'offset') {
                const minutes = parseOffset(offsetInput.value || '');
                if (minutes === null) {
                    return { mode, error: invalidOffset };
                }
                return { mode, minutes };
            }
            return { mode, minutes: 0 };
        }

        function getDateParts(date, tzMode, offsetMinutes) {
            if (tzMode === 'local') {
                return {
                    year: date.getFullYear(),
                    month: date.getMonth() + 1,
                    day: date.getDate(),
                    hour: date.getHours(),
                    minute: date.getMinutes(),
                    second: date.getSeconds(),
                    ms: date.getMilliseconds(),
                    offset: -date.getTimezoneOffset(),
                };
            }
            if (tzMode === 'offset') {
                const shifted = new Date(date.getTime() + offsetMinutes * 60000);
                return {
                    year: shifted.getUTCFullYear(),
                    month: shifted.getUTCMonth() + 1,
                    day: shifted.getUTCDate(),
                    hour: shifted.getUTCHours(),
                    minute: shifted.getUTCMinutes(),
                    second: shifted.getUTCSeconds(),
                    ms: shifted.getUTCMilliseconds(),
                    offset: offsetMinutes,
                };
            }
            return {
                year: date.getUTCFullYear(),
                month: date.getUTCMonth() + 1,
                day: date.getUTCDate(),
                hour: date.getUTCHours(),
                minute: date.getUTCMinutes(),
                second: date.getUTCSeconds(),
                ms: date.getUTCMilliseconds(),
                offset: 0,
            };
        }

        function formatIso(parts, tzMode) {
            const base = `${parts.year}-${pad2(parts.month)}-${pad2(parts.day)}T${pad2(parts.hour)}:${pad2(parts.minute)}:${pad2(parts.second)}.${pad3(parts.ms)}`;
            if (tzMode === 'utc') return `${base}Z`;
            return `${base}${formatOffset(parts.offset)}`;
        }

        function formatHuman(parts) {
            const monthLabel = monthLabels[parts.month - 1] || '';
            const time = `${pad2(parts.hour)}:${pad2(parts.minute)}:${pad2(parts.second)}`;
            if (locale === 'vi') {
                return `${pad2(parts.day)} ${monthLabel} ${parts.year} ${time}`;
            }
            return `${monthLabel} ${pad2(parts.day)}, ${parts.year} ${time}`;
        }

        function parseDateParts(value, format) {
            const cleaned = value.trim().replace('T', ' ');
            const timeMatch = cleaned.match(/^(.*?)(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?(?:\.(\d{1,3}))?)?$/);
            if (!timeMatch) return null;
            const datePart = timeMatch[1];
            const hour = timeMatch[2] ? Number(timeMatch[2]) : 0;
            const minute = timeMatch[3] ? Number(timeMatch[3]) : 0;
            const second = timeMatch[4] ? Number(timeMatch[4]) : 0;
            const ms = timeMatch[5] ? Number(timeMatch[5].padEnd(3, '0')) : 0;
            if ([hour, minute, second, ms].some(Number.isNaN)) return null;

            const ymd = datePart.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
            const dmySlash = datePart.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            const dmyDash = datePart.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/);

            let warning = '';
            if (format === 'ymd' && ymd) {
                return { year: Number(ymd[1]), month: Number(ymd[2]), day: Number(ymd[3]), hour, minute, second, ms };
            }
            if (format === 'dmy_slash' && dmySlash) {
                return { year: Number(dmySlash[3]), month: Number(dmySlash[2]), day: Number(dmySlash[1]), hour, minute, second, ms };
            }
            if (format === 'dmy_dash' && dmyDash) {
                return { year: Number(dmyDash[3]), month: Number(dmyDash[2]), day: Number(dmyDash[1]), hour, minute, second, ms };
            }
            if (format === 'auto') {
                if (ymd) {
                    return { year: Number(ymd[1]), month: Number(ymd[2]), day: Number(ymd[3]), hour, minute, second, ms };
                }
                if (dmySlash) {
                    if (Number(dmySlash[1]) <= 12) warning = warningAmbiguous;
                    return { year: Number(dmySlash[3]), month: Number(dmySlash[2]), day: Number(dmySlash[1]), hour, minute, second, ms, warning };
                }
                if (dmyDash) {
                    if (Number(dmyDash[1]) <= 12) warning = warningAmbiguous;
                    return { year: Number(dmyDash[3]), month: Number(dmyDash[2]), day: Number(dmyDash[1]), hour, minute, second, ms, warning };
                }
            }
            return null;
        }

        function parseDate(value, tzMode, offsetMinutes, formatMode) {
            const isoWithZone = value.trim().match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2}(?:\.\d{1,3})?)?(Z|[+-]\d{2}:?\d{2})$/);
            if (isoWithZone) {
                const date = new Date(value);
                if (!Number.isNaN(date.getTime())) {
                    return { date, warning: '' };
                }
            }
            const parts = parseDateParts(value, formatMode);
            if (!parts) return null;
            const { year, month, day, hour, minute, second, ms } = parts;
            if ([year, month, day].some(Number.isNaN)) return null;
            if (tzMode === 'local') {
                const date = new Date(year, month - 1, day, hour, minute, second, ms);
                return { date, warning: parts.warning || '' };
            }
            const utcMs = Date.UTC(year, month - 1, day, hour, minute, second, ms);
            if (Number.isNaN(utcMs)) return null;
            const timestamp = tzMode === 'offset' ? utcMs - offsetMinutes * 60000 : utcMs;
            return { date: new Date(timestamp), warning: parts.warning || '' };
        }

        function parseTimestamp(value, unit) {
            const number = Number(value);
            if (!Number.isFinite(number)) return null;
            const ms = unit === 's' ? number * 1000 : number;
            return new Date(ms);
        }

        function detectTimestampUnit(value) {
            if (value.includes('.')) return 's';
            const digits = value.replace(/[^0-9]/g, '');
            if (digits.length <= 10) return 's';
            return 'ms';
        }

        function updateOutputs(date, tzMode, offsetMinutes) {
            const timestampMs = date.getTime();
            const seconds = timestampMs / 1000;
            tsSeconds.value = Number.isFinite(seconds) ? String(seconds) : '';
            tsMs.value = Number.isFinite(timestampMs) ? String(Math.round(timestampMs)) : '';

            const parts = getDateParts(date, tzMode, offsetMinutes);
            const output = {
                iso: formatIso(parts, tzMode),
                ymd: `${parts.year}-${pad2(parts.month)}-${pad2(parts.day)}`,
                'dmy-slash': `${pad2(parts.day)}/${pad2(parts.month)}/${parts.year}`,
                'dmy-time': `${pad2(parts.day)}-${pad2(parts.month)}-${parts.year} ${pad2(parts.hour)}:${pad2(parts.minute)}:${pad2(parts.second)}`,
                'ymd-time': `${parts.year}/${pad2(parts.month)}/${pad2(parts.day)} ${pad2(parts.hour)}:${pad2(parts.minute)}:${pad2(parts.second)}`,
                human: formatHuman(parts),
            };

            for (const item of formatItems) {
                const value = output[item.id] || '';
                const field = document.getElementById(`dc-format-${item.id}`);
                if (field) field.value = value;
            }

            lastResult = { date, tzMode, offsetMinutes };
        }

        function setError(message) {
            errorEl.textContent = message || '';
        }

        function setWarning(message) {
            warningEl.textContent = message || '';
        }

        function convert() {
            setError('');
            setWarning('');
            const raw = input.value.trim();
            if (!raw) {
                tsSeconds.value = '';
                tsMs.value = '';
                for (const item of formatItems) {
                    const field = document.getElementById(`dc-format-${item.id}`);
                    if (field) field.value = '';
                }
                lastResult = null;
                return;
            }

            const tz = getTimezoneConfig();
            if (tz.error) {
                setError(tz.error);
                return;
            }

            const mode = inputMode.value;
            let date = null;
            let warning = '';

            if (mode === 'timestamp_s') {
                date = parseTimestamp(raw, 's');
            } else if (mode === 'timestamp_ms') {
                date = parseTimestamp(raw, 'ms');
            } else if (mode === 'date') {
                const parsed = parseDate(raw, tz.mode, tz.minutes, dateFormat.value);
                if (parsed) {
                    date = parsed.date;
                    warning = parsed.warning || '';
                }
            } else {
                const numeric = /^-?\d+(?:\.\d+)?$/.test(raw);
                if (numeric) {
                    const unit = detectTimestampUnit(raw);
                    date = parseTimestamp(raw, unit);
                } else {
                    const parsed = parseDate(raw, tz.mode, tz.minutes, dateFormat.value);
                    if (parsed) {
                        date = parsed.date;
                        warning = parsed.warning || '';
                    }
                }
            }

            if (!date || Number.isNaN(date.getTime())) {
                setError(invalidInput);
                return;
            }

            setWarning(warning);
            updateOutputs(date, tz.mode, tz.minutes);
        }

        function applyNow() {
            const tz = getTimezoneConfig();
            if (tz.error) {
                setError(tz.error);
                return;
            }
            const date = new Date();
            const parts = getDateParts(date, tz.mode, tz.minutes);
            const value = `${parts.year}-${pad2(parts.month)}-${pad2(parts.day)} ${pad2(parts.hour)}:${pad2(parts.minute)}:${pad2(parts.second)}`;
            input.value = value;
            if (tz.mode === 'local') {
                picker.value = toLocalPickerValue(date);
            }
            inputMode.value = 'date';
            convert();
        }

        function swapInput() {
            if (!lastResult) return;
            const timestamp = lastResult.date.getTime();
            const currentMode = inputMode.value;
            if (currentMode === 'timestamp_ms' || currentMode === 'timestamp_s' || currentMode === 'auto') {
                const parts = getDateParts(lastResult.date, lastResult.tzMode, lastResult.offsetMinutes);
                input.value = `${parts.year}-${pad2(parts.month)}-${pad2(parts.day)} ${pad2(parts.hour)}:${pad2(parts.minute)}:${pad2(parts.second)}`;
                inputMode.value = 'date';
            } else {
                input.value = String(Math.round(timestamp / 1000));
                inputMode.value = 'timestamp_s';
            }
            convert();
        }

        function setOffsetState() {
            const active = timezone.value === 'offset';
            offsetInput.disabled = !active;
            if (!active) {
                offsetInput.value = '';
            } else if (!offsetInput.value.trim()) {
                offsetInput.value = '+00:00';
            }
        }

        function buildFormatRows() {
            formatsWrap.innerHTML = '';
            for (const item of formatItems) {
                const row = document.createElement('div');
                row.style.display = 'grid';
                row.style.gap = '8px';

                const bar = document.createElement('div');
                bar.className = 'toolbar';
                bar.style.margin = '0';
                bar.style.justifyContent = 'space-between';

                const label = document.createElement('strong');
                label.textContent = item.label;

                const btn = document.createElement('button');
                btn.className = 'btn secondary dc-copy';
                btn.dataset.copyTarget = `dc-format-${item.id}`;
                btn.textContent = copyLabel;

                const field = document.createElement('input');
                field.id = `dc-format-${item.id}`;
                field.type = 'text';
                field.readOnly = true;

                bar.appendChild(label);
                bar.appendChild(btn);
                row.appendChild(bar);
                row.appendChild(field);
                formatsWrap.appendChild(row);
            }
        }

        function copyToClipboard(value, button) {
            if (!value) return;
            navigator.clipboard.writeText(value).then(() => {
                if (copyTimer) clearTimeout(copyTimer);
                button.textContent = copiedLabel;
                copyTimer = setTimeout(() => {
                    button.textContent = copyLabel;
                }, 1200);
            }).catch(() => {
                const textarea = document.createElement('textarea');
                textarea.value = value;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                button.textContent = copiedLabel;
                if (copyTimer) clearTimeout(copyTimer);
                copyTimer = setTimeout(() => {
                    button.textContent = copyLabel;
                }, 1200);
            });
        }

        function scheduleConvert() {
            window.clearTimeout(scheduleConvert.timer);
            scheduleConvert.timer = window.setTimeout(convert, 200);
        }

        buildFormatRows();
        setOffsetState();
        applyNow();

        runBtn.addEventListener('click', convert);
        swapBtn.addEventListener('click', swapInput);
        nowBtn.addEventListener('click', applyNow);
        input.addEventListener('input', scheduleConvert);
        inputMode.addEventListener('change', convert);
        dateFormat.addEventListener('change', convert);
        timezone.addEventListener('change', () => {
            setOffsetState();
            convert();
        });
        offsetInput.addEventListener('input', convert);
        picker.addEventListener('input', () => {
            if (picker.value) {
                input.value = picker.value.replace('T', ' ');
                inputMode.value = 'date';
                convert();
            }
        });

        document.addEventListener('click', (event) => {
            const button = event.target.closest('.dc-copy');
            if (!button) return;
            const targetId = button.dataset.copyTarget;
            const field = targetId ? document.getElementById(targetId) : null;
            copyToClipboard(field ? field.value : '', button);
        });
    </script>
@endsection
