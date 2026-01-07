@extends('layouts.app')

@section('title', __('app.lucky_draw.page_title'))
@section('description', __('app.lucky_draw.description'))
@section('shell_class', 'wide focus')
@section('structured_data')
    @php
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
        if (str_starts_with($baseUrl, 'http://')) {
            $baseUrl = 'https://' . substr($baseUrl, 7);
        }
        $pageUrl = $baseUrl . '/' . $locale . '/lucky-draw';
    @endphp
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => __('app.lucky_draw.title'),
            'description' => __('app.lucky_draw.description'),
            'applicationCategory' => 'EntertainmentApplication',
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
        <span class="badge">{{ __('app.lucky_draw.title') }}</span>
    </div>
    <h1 class="sr-only">{{ __('app.lucky_draw.title') }}</h1>
    <div class="card tool-card workspace">
        <div class="toolbar" style="margin-bottom: 16px;">
            <div class="group">
                <label>{{ __('app.lucky_draw.input_method') }}</label>
                <div class="method-tabs">
                    <button id="ld-tab-list" class="btn active">{{ __('app.lucky_draw.method_manual') }}</button>
                    <button id="ld-tab-file" class="btn">{{ __('app.lucky_draw.method_file') }}</button>
                    <button id="ld-tab-range" class="btn">{{ __('app.lucky_draw.method_range') }}</button>
                </div>
            </div>
            <div class="group">
                <label>{{ __('app.lucky_draw.panel_controls') }}</label>
                <button id="ld-prepare" class="btn">{{ __('app.lucky_draw.prepare_draw') }}</button>
                <button id="ld-reset" class="btn secondary">{{ __('app.lucky_draw.reset_draw') }}</button>
                <button id="ld-clear" class="btn secondary">{{ __('app.lucky_draw.clear_history') }}</button>
                <button id="ld-preview-open" class="btn secondary">{{ __('app.actions.full_preview') }}</button>
            </div>
            <div class="group">
                <label>{{ __('app.lucky_draw.exclude_winners') }}</label>
                <input id="ld-exclude" type="checkbox">
            </div>
        </div>
        <div class="method-status" style="margin-bottom: 12px;">
            <span id="ld-method">{{ __('app.lucky_draw.method_manual') }}</span>
            <span id="ld-count" class="file-pill">{{ __('app.lucky_draw.participant_count', ['count' => 0]) }}</span>
            <span id="ld-file-name" class="file-pill">{{ __('app.lucky_draw.file_hint') }}</span>
        </div>
        <div class="row">
            <div>
                <div id="ld-panel-list" class="panel" style="margin-bottom: 16px;">
                    <h4>{{ __('app.lucky_draw.panel_input') }}</h4>
                    <label for="ld-list">{{ __('app.lucky_draw.input_list') }}</label>
                    <textarea id="ld-list" rows="8" placeholder="{{ __('app.lucky_draw.input_placeholder') }}"></textarea>
                </div>
                <div id="ld-panel-file" class="panel" style="display: none; margin-bottom: 16px;">
                    <h4>{{ __('app.lucky_draw.panel_file') }}</h4>
                    <label>{{ __('app.lucky_draw.file_label') }}</label>
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <label for="ld-file" class="btn secondary">{{ __('app.lucky_draw.file_button') }}</label>
                        <input id="ld-file" type="file" accept=".csv,.xlsx,.xls" style="display:none;">
                        <span class="file-pill" id="ld-file-pill">{{ __('app.lucky_draw.file_hint') }}</span>
                    </div>
                    <p class="muted" style="margin: 10px 0 0;">{{ __('app.lucky_draw.file_note') }}</p>
                </div>
                <div id="ld-panel-range" class="panel" style="display: none;">
                    <h4>{{ __('app.lucky_draw.panel_range') }}</h4>
                    <div class="toolbar" style="gap: 10px;">
                        <div>
                            <label for="ld-from">{{ __('app.lucky_draw.range_from') }}</label>
                            <input id="ld-from" type="number" value="1">
                        </div>
                        <div>
                            <label for="ld-to">{{ __('app.lucky_draw.range_to') }}</label>
                            <input id="ld-to" type="number" value="100">
                        </div>
                    </div>
                    <p class="muted" style="margin: 10px 0 0;">{{ __('app.lucky_draw.range_note') }}</p>
                </div>
                <div class="panel" style="margin-top: 16px;">
                    <h4>{{ __('app.lucky_draw.panel_prizes') }}</h4>
                    <div class="toolbar" style="gap: 12px;">
                        <div>
                            <label for="ld-consolation">{{ __('app.lucky_draw.prize_consolation') }}</label>
                            <input id="ld-consolation" type="number" min="0" value="5">
                        </div>
                        <div>
                            <label for="ld-third">{{ __('app.lucky_draw.prize_third') }}</label>
                            <input id="ld-third" type="number" min="0" value="3">
                        </div>
                        <div>
                            <label for="ld-second">{{ __('app.lucky_draw.prize_second') }}</label>
                            <input id="ld-second" type="number" min="0" value="2">
                        </div>
                        <div>
                            <label for="ld-first">{{ __('app.lucky_draw.prize_first') }}</label>
                            <input id="ld-first" type="number" min="0" value="1">
                        </div>
                        <div>
                            <label for="ld-special">{{ __('app.lucky_draw.prize_special') }}</label>
                            <input id="ld-special" type="number" min="0" value="1">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="panel" style="margin-bottom: 16px;">
                    <h4>{{ __('app.lucky_draw.panel_results') }}</h4>
                    <div id="ld-status" class="muted">{{ __('app.lucky_draw.no_results') }}</div>
                    <div id="ld-results" style="margin-top: 12px;"></div>
                </div>
                <div class="panel">
                    <h4>{{ __('app.lucky_draw.panel_history') }}</h4>
                    <div id="ld-history" class="muted">{{ __('app.lucky_draw.no_history') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="preview-fullscreen lucky-preview" id="ld-preview-fullscreen">
        <div class="toolbar" style="justify-content: space-between;">
            <strong>{{ __('app.lucky_draw.panel_results') }}</strong>
            <button id="ld-preview-close" class="btn secondary">{{ __('app.actions.close') }}</button>
        </div>
        <div class="muted" id="ld-preview-status"></div>
        <div id="ld-preview-results"></div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        const listInput = document.getElementById('ld-list');
        const countBadge = document.getElementById('ld-count');
        const methodLabel = document.getElementById('ld-method');
        const methodFileName = document.getElementById('ld-file-name');
        const fromInput = document.getElementById('ld-from');
        const toInput = document.getElementById('ld-to');
        const excludeToggle = document.getElementById('ld-exclude');
        const prepareBtn = document.getElementById('ld-prepare');
        const resetBtn = document.getElementById('ld-reset');
        const clearBtn = document.getElementById('ld-clear');
        const status = document.getElementById('ld-status');
        const results = document.getElementById('ld-results');
        const historyEl = document.getElementById('ld-history');
        const previewOpen = document.getElementById('ld-preview-open');
        const previewClose = document.getElementById('ld-preview-close');
        const previewWrap = document.getElementById('ld-preview-fullscreen');
        const previewStatus = document.getElementById('ld-preview-status');
        const previewResults = document.getElementById('ld-preview-results');
        const tabList = document.getElementById('ld-tab-list');
        const tabFile = document.getElementById('ld-tab-file');
        const tabRange = document.getElementById('ld-tab-range');
        const panelList = document.getElementById('ld-panel-list');
        const panelFile = document.getElementById('ld-panel-file');
        const panelRange = document.getElementById('ld-panel-range');
        const fileInput = document.getElementById('ld-file');
        const filePill = document.getElementById('ld-file-pill');

        const prizeInputs = {
            consolation: document.getElementById('ld-consolation'),
            third: document.getElementById('ld-third'),
            second: document.getElementById('ld-second'),
            first: document.getElementById('ld-first'),
            special: document.getElementById('ld-special'),
        };

        const prizeMeta = [
            { key: 'consolation', label: `{{ __('app.lucky_draw.prize_consolation') }}` },
            { key: 'third', label: `{{ __('app.lucky_draw.prize_third') }}` },
            { key: 'second', label: `{{ __('app.lucky_draw.prize_second') }}` },
            { key: 'first', label: `{{ __('app.lucky_draw.prize_first') }}` },
            { key: 'special', label: `{{ __('app.lucky_draw.prize_special') }}` },
        ];

        const textNoResults = `{{ __('app.lucky_draw.no_results') }}`;
        const textNoHistory = `{{ __('app.lucky_draw.no_history') }}`;
        const textDrawing = `{{ __('app.lucky_draw.drawing') }}`;
        const textDrawDone = `{{ __('app.lucky_draw.draw_done') }}`;
        const textErrorNoParticipants = `{{ __('app.lucky_draw.error_no_participants') }}`;
        const textErrorNotEnough = `{{ __('app.lucky_draw.error_not_enough') }}`;
        const textWinner = `{{ __('app.lucky_draw.winner') }}`;
        const textWinners = `{{ __('app.lucky_draw.winners') }}`;
        const textManual = `{{ __('app.lucky_draw.method_manual') }}`;
        const textFile = `{{ __('app.lucky_draw.method_file') }}`;
        const textRange = `{{ __('app.lucky_draw.method_range') }}`;
        const textFileHint = `{{ __('app.lucky_draw.file_hint') }}`;
        const textFileError = `{{ __('app.lucky_draw.file_error') }}`;
        const textFileMissing = `{{ __('app.lucky_draw.error_no_file') }}`;
        const textReady = `{{ __('app.lucky_draw.ready_draw') }}`;
        const textClickToDraw = `{{ __('app.lucky_draw.click_to_draw') }}`;
        const historyKey = 'desktools.luckyDraw.history';

        let participants = [];
        let available = [];
        let sessionHistory = [];
        let drawInProgress = false;
        let inputMode = 'list';
        let fileItems = [];
        let fileName = '';
        let drawSlots = [];

        function updateCount() {
            countBadge.textContent = `{{ __('app.lucky_draw.participant_count', ['count' => '{count}']) }}`.replace('{count}', participants.length);
            countBadge.classList.toggle('active', participants.length > 0);
        }

        function setParticipants(list) {
            participants = list;
            available = [...participants];
            updateCount();
        }

        function updateMethodLabel() {
            methodLabel.textContent = inputMode === 'file' ? textFile : inputMode === 'range' ? textRange : textManual;
            methodFileName.textContent = inputMode === 'file' && fileName ? fileName : textFileHint;
            methodFileName.classList.toggle('active', inputMode === 'file' && !!fileName);
        }

        function setMode(nextMode) {
            inputMode = nextMode;
            tabList.classList.toggle('active', nextMode === 'list');
            tabFile.classList.toggle('active', nextMode === 'file');
            tabRange.classList.toggle('active', nextMode === 'range');
            panelList.style.display = nextMode === 'list' ? 'block' : 'none';
            panelFile.style.display = nextMode === 'file' ? 'block' : 'none';
            panelRange.style.display = nextMode === 'range' ? 'block' : 'none';
            updateMethodLabel();
            if (nextMode === 'list') {
                loadList();
            } else if (nextMode === 'range') {
                loadRange();
            } else if (nextMode === 'file') {
                if (fileItems.length) {
                    setParticipants([...fileItems]);
                } else if (fileInput.files.length) {
                    loadFile();
                } else {
                    setParticipants([]);
                }
            }
        }

        function parseListInput() {
            const lines = listInput.value.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
            return Array.from(new Set(lines));
        }

        function loadList() {
            setParticipants(parseListInput());
        }

        function loadRange() {
            const start = Number(fromInput.value);
            const end = Number(toInput.value);
            if (!Number.isFinite(start) || !Number.isFinite(end)) {
                setParticipants([]);
                return;
            }
            const min = Math.min(start, end);
            const max = Math.max(start, end);
            const range = [];
            for (let i = min; i <= max; i++) {
                range.push(String(i));
            }
            setParticipants(range);
        }

        function parseCsvRows(raw) {
            const rows = [];
            let row = [];
            let cell = '';
            let inQuotes = false;
            for (let i = 0; i < raw.length; i++) {
                const char = raw[i];
                if (inQuotes) {
                    if (char === '"' && raw[i + 1] === '"') {
                        cell += '"';
                        i++;
                    } else if (char === '"') {
                        inQuotes = false;
                    } else {
                        cell += char;
                    }
                    continue;
                }
                if (char === '"') {
                    inQuotes = true;
                } else if (char === ',') {
                    row.push(cell);
                    cell = '';
                } else if (char === '\n') {
                    row.push(cell);
                    rows.push(row);
                    row = [];
                    cell = '';
                } else if (char === '\r') {
                    if (raw[i + 1] === '\n') {
                        continue;
                    }
                    row.push(cell);
                    rows.push(row);
                    row = [];
                    cell = '';
                } else {
                    cell += char;
                }
            }
            if (cell.length || row.length) {
                row.push(cell);
                rows.push(row);
            }
            return rows;
        }

        function normalizeList(items) {
            return Array.from(new Set(items.map((item) => String(item).trim()).filter(Boolean)));
        }

        async function readFileList(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext === 'csv') {
                const text = await file.text();
                const rows = parseCsvRows(text);
                return normalizeList(rows.map((row) => row[0] ?? ''));
            }
            if (ext === 'xlsx' || ext === 'xls') {
                if (typeof XLSX === 'undefined') {
                    throw new Error(textFileError);
                }
                const data = await file.arrayBuffer();
                const workbook = XLSX.read(data, { type: 'array' });
                const sheetName = workbook.SheetNames[0];
                const sheet = workbook.Sheets[sheetName];
                const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });
                return normalizeList(rows.map((row) => (row && row.length ? row[0] : '')));
            }
            return [];
        }

        async function loadFile() {
            if (!fileInput.files.length) {
                status.textContent = textFileMissing;
                return;
            }
            const file = fileInput.files[0];
            fileName = file.name;
            filePill.textContent = file.name;
            filePill.classList.add('active');
            try {
                fileItems = await readFileList(file);
                setParticipants([...fileItems]);
                updateMethodLabel();
                fileInput.value = '';
            } catch (error) {
                status.textContent = error.message || textFileError;
            }
        }

        function prizeCounts() {
            const counts = {};
            for (const [key, input] of Object.entries(prizeInputs)) {
                counts[key] = Math.max(0, Number(input.value) || 0);
            }
            return counts;
        }

        function totalWinnersNeeded() {
            const counts = prizeCounts();
            return Object.values(counts).reduce((sum, value) => sum + value, 0);
        }

        function renderHistory() {
            historyEl.innerHTML = '';
            if (sessionHistory.length === 0) {
                historyEl.textContent = textNoHistory;
                historyEl.className = 'muted';
                return;
            }
            historyEl.className = '';
            sessionHistory.slice().reverse().forEach((entry) => {
                const row = document.createElement('div');
                row.className = 'panel';
                row.style.marginBottom = '10px';

                const title = document.createElement('strong');
                title.textContent = `${entry.title} · ${entry.time}`;
                row.appendChild(title);

                const list = document.createElement('div');
                list.style.marginTop = '8px';
                list.style.display = 'flex';
                list.style.flexWrap = 'wrap';
                list.style.gap = '8px';

                entry.winners.forEach((winner) => {
                    const pill = document.createElement('span');
                    pill.className = 'file-pill active';
                    pill.textContent = winner;
                    list.appendChild(pill);
                });

                row.appendChild(list);
                historyEl.appendChild(row);
            });
        }

        function saveHistory() {
            try {
                localStorage.setItem(historyKey, JSON.stringify(sessionHistory));
            } catch {
                // ignore storage failures
            }
        }

        function loadHistory() {
            try {
                const raw = localStorage.getItem(historyKey);
                if (!raw) return;
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    sessionHistory = parsed;
                }
            } catch {
                sessionHistory = [];
            }
        }

        function randomPick(source) {
            const index = Math.floor(Math.random() * source.length);
            return { value: source[index], index };
        }

        function animatePick(source, targetEl) {
            return new Promise((resolve) => {
                let current = '';
                const ticker = setInterval(() => {
                    if (source.length === 0) return;
                    const pick = randomPick(source);
                    current = pick.value;
                    targetEl.textContent = current;
                }, 50);
                setTimeout(() => {
                    clearInterval(ticker);
                    const pick = randomPick(source);
                    targetEl.textContent = pick.value;
                    resolve(pick);
                }, 1800);
            });
        }

        function renderSlots(container) {
            container.innerHTML = '';
            if (!drawSlots.length) return;
            drawSlots.forEach((block) => {
                const card = document.createElement('div');
                card.className = 'panel';
                card.style.marginBottom = '12px';

                const title = document.createElement('strong');
                title.textContent = `${block.label} (${block.slots.length})`;
                card.appendChild(title);

                const list = document.createElement('div');
                list.style.marginTop = '8px';
                list.style.display = 'flex';
                list.style.flexWrap = 'wrap';
                list.style.gap = '8px';

                block.slots.forEach((slot) => {
                    const pill = document.createElement('button');
                    pill.type = 'button';
                    pill.className = 'file-pill';
                    pill.textContent = slot.winner || textClickToDraw;
                    pill.dataset.slotId = slot.id;
                    pill.style.cursor = slot.winner ? 'default' : 'pointer';
                    if (slot.winner) {
                        pill.classList.add('active');
                    }
                    list.appendChild(pill);
                });

                card.appendChild(list);
                container.appendChild(card);
            });
        }

        function buildDrawSlots() {
            const counts = prizeCounts();
            drawSlots = prizeMeta
                .filter((prize) => counts[prize.key] > 0)
                .map((prize) => {
                    const count = counts[prize.key];
                    const slots = Array.from({ length: count }, (_, index) => ({
                        id: `${prize.key}-${index + 1}-${Date.now()}`,
                        winner: null,
                    }));
                    return { ...prize, slots };
                });
        }

        function resetPools() {
            available = excludeToggle.checked ? [...participants] : [...participants];
        }

        function prepareDraw() {
            if (participants.length === 0) {
                status.textContent = textErrorNoParticipants;
                renderAll();
                return;
            }
            const totalNeeded = totalWinnersNeeded();
            if (excludeToggle.checked && participants.length < totalNeeded) {
                status.textContent = textErrorNotEnough;
                renderAll();
                return;
            }
            buildDrawSlots();
            resetPools();
            status.textContent = textReady;
            renderAll();
        }

        async function drawSlot(slotId, targetEl) {
            if (drawInProgress) return;
            const pool = excludeToggle.checked ? available : participants;
            if (!pool.length) {
                status.textContent = textErrorNotEnough;
                renderAll();
                return;
            }
            const slot = drawSlots.flatMap((block) => block.slots).find((item) => item.id === slotId);
            if (!slot || slot.winner) return;
            drawInProgress = true;
            status.textContent = textDrawing;

            const pick = await animatePick(pool, targetEl);
            slot.winner = pick.value;
            targetEl.classList.add('active');
            targetEl.style.cursor = 'default';

            if (excludeToggle.checked) {
                pool.splice(pick.index, 1);
                available = pool;
                updateCount();
            }

            sessionHistory.push({
                title: drawSlots.find((block) => block.slots.some((item) => item.id === slotId))?.label || '',
                winners: [slot.winner],
                time: new Date().toLocaleTimeString(),
            });
            saveHistory();
            renderHistory();

            const allDone = drawSlots.every((block) => block.slots.every((item) => item.winner));
            status.textContent = allDone ? textDrawDone : textReady;
            renderAll();
            drawInProgress = false;
        }

        function renderAll() {
            results.innerHTML = '';
            if (!drawSlots.length) {
                status.textContent = status.textContent || textNoResults;
            } else if (!status.textContent) {
                status.textContent = textReady;
            }
            renderSlots(results);
            if (previewWrap.classList.contains('active')) {
                previewStatus.textContent = status.textContent;
                renderSlots(previewResults);
            }
        }

        previewOpen.addEventListener('click', () => {
            previewWrap.classList.add('active');
            previewStatus.textContent = status.textContent;
            renderSlots(previewResults);
        });

        previewClose.addEventListener('click', () => {
            previewWrap.classList.remove('active');
        });

        listInput.addEventListener('input', () => {
            if (inputMode === 'list') {
                loadList();
            }
        });
        fromInput.addEventListener('input', () => {
            if (inputMode === 'range') {
                loadRange();
            }
        });
        toInput.addEventListener('input', () => {
            if (inputMode === 'range') {
                loadRange();
            }
        });
        function handleSlotClick(event) {
            const target = event.target.closest('[data-slot-id]');
            if (!target) return;
            drawSlot(target.dataset.slotId, target);
        }

        prepareBtn.addEventListener('click', prepareDraw);
        results.addEventListener('click', handleSlotClick);
        previewResults.addEventListener('click', handleSlotClick);
        previewWrap.addEventListener('click', handleSlotClick);
        tabList.addEventListener('click', () => setMode('list'));
        tabFile.addEventListener('click', () => setMode('file'));
        tabRange.addEventListener('click', () => setMode('range'));
        fileInput.addEventListener('change', () => {
            if (!fileInput.files.length) {
                filePill.textContent = textFileHint;
                filePill.classList.remove('active');
                fileName = '';
                updateMethodLabel();
                return;
            }
            fileName = fileInput.files[0].name;
            filePill.textContent = fileName;
            filePill.classList.add('active');
            updateMethodLabel();
            if (inputMode === 'file') {
                loadFile();
            }
        });

        resetBtn.addEventListener('click', () => {
            drawSlots = [];
            results.innerHTML = '';
            status.textContent = textNoResults;
            renderAll();
        });

        clearBtn.addEventListener('click', () => {
            sessionHistory = [];
            saveHistory();
            renderHistory();
        });

        setMode('list');
        loadHistory();
        renderHistory();
    </script>
@endsection
