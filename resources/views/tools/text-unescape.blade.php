@extends('layouts.app')

@section('title', __('app.text_unescape.page_title'))
@section('description', __('app.text_unescape.description'))
@section('shell_class', 'wide focus')
@section('structured_data')
    @php
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
        if (str_starts_with($baseUrl, 'http://')) {
            $baseUrl = 'https://' . substr($baseUrl, 7);
        }
        $pageUrl = $baseUrl . '/' . $locale . '/text-unescape';
    @endphp
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => __('app.text_unescape.title'),
            'description' => __('app.text_unescape.description'),
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
        <span class="badge">{{ __('app.text_unescape.title') }}</span>
    </div>
    <h1 class="sr-only">{{ __('app.text_unescape.title') }}</h1>
    <div class="card tool-card workspace">
        <div class="toolbar" style="margin-bottom: 16px;">
            <div class="group">
                <label>{{ __('app.text_unescape.panel_mode') }}</label>
                <select id="tu-mode">
                    <option value="auto">{{ __('app.text_unescape.modes.auto') }}</option>
                    <option value="json">{{ __('app.text_unescape.modes.json') }}</option>
                    <option value="html">{{ __('app.text_unescape.modes.html') }}</option>
                    <option value="url">{{ __('app.text_unescape.modes.url') }}</option>
                    <option value="csv">{{ __('app.text_unescape.modes.csv') }}</option>
                    <option value="base64">{{ __('app.text_unescape.modes.base64') }}</option>
                </select>
            </div>
            <div class="group">
                <label>{{ __('app.text_unescape.panel_import') }}</label>
                <label for="tu-file" class="btn secondary">{{ __('app.text_unescape.file_button') }}</label>
                <span id="tu-file-name" class="file-pill">{{ __('app.text_unescape.file_hint') }}</span>
                <input id="tu-file" type="file" accept=".csv,.xlsx,.xls,.json,.txt" style="display:none;">
            </div>
            <div class="group">
                <label>{{ __('app.text_unescape.panel_actions') }}</label>
                <button id="tu-run" class="btn">{{ __('app.actions.convert') }}</button>
                <button id="tu-swap" class="btn secondary">{{ __('app.actions.swap') }}</button>
            </div>
            <details class="panel">
                <summary>{{ __('app.text_unescape.panel_tips') }}</summary>
                <p class="muted" style="margin-top: 8px;">{{ __('app.text_unescape.tips') }}</p>
            </details>
        </div>
        <div class="row">
            <div>
                <label for="tu-input">{{ __('app.text_unescape.input') }}</label>
                <textarea id="tu-input" rows="12" placeholder="{{ __('app.text_unescape.placeholder') }}"></textarea>
            </div>
            <div>
                <label for="tu-output">{{ __('app.text_unescape.output') }}</label>
                <div class="toolbar" style="margin-bottom: 8px;">
                    <div class="group">
                        <label>View</label>
                        <button id="tu-view-text" class="btn secondary">{{ __('app.text_unescape.view_text') }}</button>
                        <button id="tu-view-tree" class="btn secondary">{{ __('app.text_unescape.view_tree') }}</button>
                    </div>
                    <div class="group">
                        <label>Tree</label>
                        <button id="tu-expand" class="btn secondary">{{ __('app.text_unescape.expand_all') }}</button>
                        <button id="tu-collapse" class="btn secondary">{{ __('app.text_unescape.collapse_all') }}</button>
                    </div>
                    <div class="group">
                        <label>Output</label>
                        <button id="tu-copy" class="btn secondary">{{ __('app.actions.copy') }}</button>
                    </div>
                </div>
                <div id="tu-path" class="path-bar" style="display: none;"></div>
                <textarea id="tu-output" rows="12" readonly style="display:none;"></textarea>
                <pre id="tu-text" class="tree" style="display:block;"></pre>
                <div id="tu-tree" class="tree"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        const fileErrorRead = `{{ __('app.text_unescape.file_error_read') }}`;
        const fileErrorXlsx = `{{ __('app.text_unescape.file_error_xlsx') }}`;
        const input = document.getElementById('tu-input');
        const output = document.getElementById('tu-output');
        const textView = document.getElementById('tu-text');
        const tree = document.getElementById('tu-tree');
        const pathBar = document.getElementById('tu-path');
        const mode = document.getElementById('tu-mode');
        const fileInput = document.getElementById('tu-file');
        const fileName = document.getElementById('tu-file-name');
        const run = document.getElementById('tu-run');
        const swap = document.getElementById('tu-swap');
        const copy = document.getElementById('tu-copy');
        const viewText = document.getElementById('tu-view-text');
        const viewTree = document.getElementById('tu-view-tree');
        const expandBtn = document.getElementById('tu-expand');
        const collapseBtn = document.getElementById('tu-collapse');
        const copyLabel = `{{ __('app.actions.copy') }}`;
        const copiedLabel = `{{ __('app.actions.copied') }}`;
        let currentObject = null;
        let currentView = 'text';
        let currentJsonText = '';
        let currentPath = 'root';
        let currentPathValue = null;
        let activeNodeButton = null;
        let pathNodeMap = new Map();
        let pathCopyButton = null;
        let pathCopyTimer = null;

        function parsePathTokens(path) {
            if (!path || path === 'root') return [];
            const tokens = [];
            let buffer = '';
            for (let i = 0; i < path.length; i++) {
                const char = path[i];
                if (char === '.') {
                    if (buffer) {
                        tokens.push({ type: 'key', value: buffer });
                        buffer = '';
                    }
                    continue;
                }
                if (char === '[') {
                    if (buffer) {
                        tokens.push({ type: 'key', value: buffer });
                        buffer = '';
                    }
                    const end = path.indexOf(']', i);
                    if (end !== -1) {
                        tokens.push({ type: 'index', value: path.slice(i + 1, end) });
                        i = end;
                        continue;
                    }
                }
                buffer += char;
            }
            if (buffer) tokens.push({ type: 'key', value: buffer });
            return tokens;
        }

        function getNodeByPath(path) {
            if (!currentObject) return null;
            if (!path || path === 'root') return currentObject;
            const tokens = parsePathTokens(path);
            let node = currentObject;
            for (const token of tokens) {
                if (node === null || node === undefined) return null;
                if (token.type === 'index') {
                    node = node[Number(token.value)];
                } else {
                    node = node[token.value];
                }
            }
            return node;
        }

        function buildPathCrumbs(path) {
            const crumbs = [{ label: 'root', path: 'root' }];
            if (!path || path === 'root') return crumbs;
            const tokens = parsePathTokens(path);
            let running = '';
            for (const token of tokens) {
                if (token.type === 'index') {
                    running += `[${token.value}]`;
                    crumbs.push({ label: `[${token.value}]`, path: running });
                } else {
                    running = running ? `${running}.${token.value}` : token.value;
                    crumbs.push({ label: token.value, path: running });
                }
            }
            return crumbs;
        }

        function highlightTreeNode(path) {
            if (activeNodeButton) {
                activeNodeButton.classList.remove('active');
            }
            const next = pathNodeMap.get(path);
            if (next) {
                next.classList.add('active');
                activeNodeButton = next;
            } else {
                activeNodeButton = null;
            }
        }

        function expandAncestors(li) {
            let current = li;
            while (current) {
                current.classList.remove('collapsed');
                const caret = current.querySelector(':scope > div .caret');
                if (caret && caret.textContent !== '·') {
                    caret.textContent = '▾';
                }
                current = current.parentElement?.closest('li') ?? null;
            }
        }

        function focusPath(path) {
            const normalized = path || 'root';
            setPath(normalized);
            const target = pathNodeMap.get(normalized);
            if (target) {
                const li = target.closest('li');
                if (li) expandAncestors(li);
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.focus({ preventScroll: true });
            }
        }

        async function copyPathValue() {
            if (currentPathValue === undefined) return;
            const payload = typeof currentPathValue === 'object'
                ? JSON.stringify(currentPathValue, null, 2)
                : String(currentPathValue);
            try {
                await navigator.clipboard.writeText(payload);
                if (pathCopyButton) {
                    if (pathCopyTimer) clearTimeout(pathCopyTimer);
                    pathCopyButton.textContent = copiedLabel;
                    pathCopyButton.classList.add('pulse');
                    pathCopyTimer = setTimeout(() => {
                        pathCopyButton.textContent = copyLabel;
                        pathCopyButton.classList.remove('pulse');
                    }, 1200);
                }
            } catch {
                output.value = payload;
                output.select();
                document.execCommand('copy');
                if (pathCopyButton) {
                    if (pathCopyTimer) clearTimeout(pathCopyTimer);
                    pathCopyButton.textContent = copiedLabel;
                    pathCopyButton.classList.add('pulse');
                    pathCopyTimer = setTimeout(() => {
                        pathCopyButton.textContent = copyLabel;
                        pathCopyButton.classList.remove('pulse');
                    }, 1200);
                }
            }
        }

        function renderPathBar(path) {
            pathBar.innerHTML = '';
            const label = document.createElement('span');
            label.className = 'path-label';
            label.textContent = `{{ __('app.text_unescape.path_label') }}:`;

            const crumbsWrap = document.createElement('div');
            crumbsWrap.className = 'path-crumbs';
            const crumbs = buildPathCrumbs(path);
            crumbs.forEach((crumb, index) => {
                const btn = document.createElement('button');
                btn.className = 'path-crumb';
                if (index === crumbs.length - 1) {
                    btn.classList.add('active');
                    btn.setAttribute('aria-current', 'location');
                }
                btn.textContent = crumb.label;
                btn.addEventListener('click', () => focusPath(crumb.path));
                crumbsWrap.appendChild(btn);
                if (index < crumbs.length - 1) {
                    const sep = document.createElement('span');
                    sep.className = 'path-sep';
                    sep.textContent = '>';
                    crumbsWrap.appendChild(sep);
                }
            });

            const meta = document.createElement('div');
            meta.className = 'path-meta';
            meta.appendChild(label);
            meta.appendChild(crumbsWrap);

            const actions = document.createElement('div');
            actions.className = 'path-actions';
            const copyBtn = document.createElement('button');
            copyBtn.className = 'path-action';
            copyBtn.textContent = `{{ __('app.text_unescape.copy_node') }}`;
            copyBtn.addEventListener('click', copyPathValue);
            pathCopyButton = copyBtn;
            actions.appendChild(copyBtn);

            pathBar.appendChild(meta);
            pathBar.appendChild(actions);
        }

        function setPath(path, value) {
            const normalized = path || 'root';
            currentPath = normalized;
            if (value !== undefined) {
                currentPathValue = value;
            } else {
                currentPathValue = getNodeByPath(normalized);
            }
            renderPathBar(normalized);
            highlightTreeNode(normalized);
        }

        function tryJsonParse(raw) {
            try {
                return JSON.parse(raw);
            } catch {
                return null;
            }
        }

        function unescapeJsonString(raw) {
            const direct = tryJsonParse(raw);
            if (direct !== null) {
                return direct;
            }
            try {
                return JSON.parse('"' + raw.replace(/\\\\/g, '\\\\\\\\').replace(/"/g, '\\"') + '"');
            } catch {
                return raw;
            }
        }

        function tryRepairJson(raw) {
            const trimmed = raw.trim();
            if (!trimmed) return null;

            const joined = trimmed
                .replace(/}\s*{/g, '},{')
                .replace(/]\s*\[/g, '],[')
                .replace(/}\s*\[/g, '},[')
                .replace(/]\s*{/g, '],{');
            if (joined !== trimmed) {
                const wrapped = `[${joined}]`;
                const parsed = tryJsonParse(wrapped);
                if (parsed !== null) return parsed;
            }

            const lines = trimmed.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
            if (lines.length > 1) {
                const items = [];
                for (const line of lines) {
                    const parsedLine = tryJsonParse(line);
                    if (parsedLine === null) return null;
                    items.push(parsedLine);
                }
                return items;
            }
            return null;
        }

        function parseJsonInput(raw) {
            const direct = tryJsonParse(raw);
            if (direct !== null) return direct;
            const repaired = tryRepairJson(raw);
            if (repaired !== null) return repaired;

            const unescaped = unescapeJsonString(raw);
            if (typeof unescaped === 'string') {
                const parsed = tryJsonParse(unescaped);
                if (parsed !== null) return parsed;
                const repairedUnescaped = tryRepairJson(unescaped);
                if (repairedUnescaped !== null) return repairedUnescaped;
            }
            return unescaped;
        }

        function parseMaybeNestedJson(value) {
            if (typeof value !== 'string') return value;
            const trimmed = value.trim();
            if (!trimmed) return value;
            if (trimmed.startsWith('{') || trimmed.startsWith('[')) {
                const parsed = tryJsonParse(trimmed);
                return parsed ?? value;
            }
            return value;
        }

        function expandEscapedLogPayload(payload) {
            if (!payload || typeof payload !== 'object') return payload;
            if (!payload.data || !Array.isArray(payload.data.result)) return payload;

            payload.data.result = payload.data.result.map((entry) => {
                if (!entry || !Array.isArray(entry.values)) return entry;
                const values = entry.values.map((valueRow) => {
                    if (!Array.isArray(valueRow) || valueRow.length < 2) return valueRow;
                    const raw = valueRow[1];
                    const decoded = typeof raw === 'string' ? unescapeJsonString(raw) : raw;
                    if (typeof decoded !== 'object' || decoded === null) return valueRow;
                    const logRaw = decoded.log;
                    const logDecoded = typeof logRaw === 'string' ? unescapeJsonString(logRaw) : logRaw;
                    const logParsed = parseMaybeNestedJson(logDecoded);

                    const expanded = [valueRow[0], ''];
                    expanded.push({ channel: decoded.channel ?? null, log: '' });
                    expanded.push(logParsed);
                    return expanded;
                });
                return { ...entry, values };
            });

            return payload;
        }

        function deepExpandJsonStrings(value) {
            if (Array.isArray(value)) {
                return value.map((item) => deepExpandJsonStrings(item));
            }
            if (value && typeof value === 'object') {
                const next = {};
                for (const [key, val] of Object.entries(value)) {
                    next[key] = deepExpandJsonStrings(val);
                }
                return next;
            }
            if (typeof value === 'string') {
                const decoded = unescapeJsonString(value);
                const parsed = parseMaybeNestedJson(decoded);
                if (parsed !== value) {
                    return deepExpandJsonStrings(parsed);
                }
            }
            return value;
        }

        function decodeHtml(raw) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = raw;
            return textarea.value;
        }

        function decodeUrl(raw) {
            try {
                return decodeURIComponent(raw.replace(/\+/g, ' '));
            } catch {
                return raw;
            }
        }

        function decodeBase64(raw) {
            try {
                const binary = atob(raw);
                const bytes = Uint8Array.from(binary, (c) => c.charCodeAt(0));
                return new TextDecoder().decode(bytes);
            } catch {
                return raw;
            }
        }

        function unescapeCsv(raw) {
            let text = raw;
            if (text.startsWith('"') && text.endsWith('"')) {
                text = text.slice(1, -1);
            }
            return text.replace(/""/g, '"');
        }

        function parseCsv(raw) {
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
            return rows.filter((rowData) => rowData.some((value) => value !== ''));
        }

        function csvToJson(raw) {
            const rows = parseCsv(raw);
            if (!rows.length) return [];
            const headers = rows.shift().map((header, index) => header.trim() || `col_${index + 1}`);
            return rows.map((row) => {
                const obj = {};
                headers.forEach((header, index) => {
                    obj[header] = row[index] ?? '';
                });
                return obj;
            });
        }

        function autoDetect(raw) {
            const trimmed = raw.trim();
            if (trimmed.startsWith('{') || trimmed.startsWith('[')) {
                return 'json';
            }
            if (trimmed.includes('\\\\') || trimmed.includes('\\n') || trimmed.includes('\\t')) {
                return 'json';
            }
            if (trimmed.includes('%') || trimmed.includes('+')) {
                return 'url';
            }
            if (trimmed.includes('&lt;') || trimmed.includes('&gt;') || trimmed.includes('&amp;')) {
                return 'html';
            }
            if (/^[A-Za-z0-9+/=\\s]+$/.test(trimmed) && trimmed.length > 8) {
                return 'base64';
            }
            if (trimmed.includes('","') || trimmed.includes('""')) {
                return 'csv';
            }
            return 'json';
        }

        function setView(view) {
            currentView = view;
            pathBar.style.display = view === 'tree' ? 'block' : 'none';
            tree.style.display = view === 'tree' ? 'block' : 'none';
            textView.style.display = view === 'text' ? 'block' : 'none';
        }

        function setFileLabel(name) {
            if (!name) {
                fileName.textContent = `{{ __('app.text_unescape.file_hint') }}`;
                fileName.classList.remove('active');
                return;
            }
            fileName.textContent = name;
            fileName.classList.add('active');
        }

        function formatValue(value) {
            if (value === null) return { text: 'null', type: 'null' };
            if (Array.isArray(value)) return { text: `[${value.length}]`, type: 'array' };
            if (typeof value === 'object') return { text: '{...}', type: 'object' };
            if (typeof value === 'string') return { text: `"${value}"`, type: 'string' };
            return { text: String(value), type: typeof value };
        }

        function renderTree(value) {
            tree.innerHTML = '';
            pathNodeMap = new Map();
            activeNodeButton = null;
            const buildNode = (node, keyPath) => {
                const li = document.createElement('li');
                const isArray = Array.isArray(node);
                const isObject = node && typeof node === 'object' && !isArray;
                const type = isArray ? 'array' : isObject ? 'object' : typeof node;
                const keyLabel = keyPath === '' ? 'root' : keyPath.split('.').slice(-1)[0];

                const row = document.createElement('div');
                row.style.display = 'flex';
                row.style.alignItems = 'center';
                row.style.gap = '6px';

                if (isArray || isObject) {
                    const caret = document.createElement('button');
                    caret.className = 'caret';
                    caret.textContent = '▾';
                    caret.addEventListener('click', (event) => {
                        event.stopPropagation();
                        li.classList.toggle('collapsed');
                        caret.textContent = li.classList.contains('collapsed') ? '▸' : '▾';
                    });
                    row.appendChild(caret);
                } else {
                    const spacer = document.createElement('span');
                    spacer.className = 'caret';
                    spacer.textContent = '·';
                    spacer.style.cursor = 'default';
                    row.appendChild(spacer);
                }

                const button = document.createElement('button');
                button.className = 'node-button';
                const valueMeta = formatValue(node);
                button.innerHTML = `<span class="node-key">${keyLabel}</span><span class="node-type">${type}</span><span class="node-value ${valueMeta.type}">${valueMeta.text}</span>`;
                const nodePath = keyPath || 'root';
                button.dataset.path = nodePath;
                pathNodeMap.set(nodePath, button);
                button.addEventListener('click', () => {
                    if (currentView === 'tree') {
                        setPath(nodePath, node);
                    }
                });
                row.appendChild(button);
                li.appendChild(row);

                if (isArray || isObject) {
                    const ul = document.createElement('ul');
                    const entries = isArray ? node.entries() : Object.entries(node);
                    for (const [childKey, childValue] of entries) {
                        const childPath = keyPath === '' ? String(childKey) : (isArray ? `${keyPath}[${childKey}]` : `${keyPath}.${childKey}`);
                        ul.appendChild(buildNode(childValue, childPath));
                    }
                    li.appendChild(ul);
                }
                return li;
            };
            const rootUl = document.createElement('ul');
            rootUl.appendChild(buildNode(value, ''));
            tree.appendChild(rootUl);
            if (currentView === 'tree') {
                setPath('root', value);
            }
        }

        function renderText(value) {
            textView.innerHTML = '';
            if (value === null || typeof value !== 'object') {
                textView.textContent = String(value ?? '');
                currentJsonText = textView.textContent;
                return;
            }
            const indent = (level) => '  '.repeat(level);

            const build = (node, level, keyName, isLast) => {
                const isArray = Array.isArray(node);
                const isObject = node && typeof node === 'object' && !isArray;

                if (!isArray && !isObject) {
                    const line = document.createElement('div');
                    const comma = isLast ? '' : ',';
                    line.appendChild(document.createTextNode(indent(level)));
                    if (keyName) {
                        const keySpan = document.createElement('span');
                        keySpan.className = 'json-key';
                        keySpan.textContent = `"${keyName}"`;
                        line.appendChild(keySpan);
                        line.appendChild(document.createTextNode(': '));
                    }
                    const valueSpan = document.createElement('span');
                    const valueType = node === null ? 'null' : typeof node;
                    valueSpan.className = `json-value ${valueType}`;
                    valueSpan.textContent = JSON.stringify(node);
                    line.appendChild(valueSpan);
                    if (comma) {
                        line.appendChild(document.createTextNode(comma));
                    }
                    return line;
                }

                const nodeWrap = document.createElement('div');
                nodeWrap.className = 'json-node';

                const openLine = document.createElement('div');
                const toggleNode = (event) => {
                    event.stopPropagation();
                    nodeWrap.classList.toggle('collapsed');
                };
                openLine.appendChild(document.createTextNode(indent(level)));
                if (keyName) {
                    const keySpan = document.createElement('span');
                    keySpan.className = 'json-key';
                    keySpan.textContent = `"${keyName}"`;
                    openLine.appendChild(keySpan);
                    openLine.appendChild(document.createTextNode(': '));
                }
                const brace = document.createElement('span');
                brace.className = 'json-toggle';
                brace.textContent = isArray ? '[' : '{';
                brace.style.cursor = 'pointer';
                brace.addEventListener('click', toggleNode);
                openLine.appendChild(brace);

                const summary = document.createElement('span');
                const count = isArray ? node.length : Object.keys(node).length;
                summary.className = 'json-summary';
                summary.textContent = isArray ? ` … ${count} items ]` : ` … ${count} keys }`;
                openLine.appendChild(summary);

                nodeWrap.appendChild(openLine);

                const childrenWrap = document.createElement('div');
                childrenWrap.className = 'json-children';
                const entries = isArray ? node.entries() : Object.entries(node);
                const total = isArray ? node.length : Object.keys(node).length;
                let idx = 0;
                for (const [childKey, childValue] of entries) {
                    const childIsLast = idx === total - 1;
                    const childName = isArray ? null : childKey;
                    childrenWrap.appendChild(build(childValue, level + 1, childName, childIsLast));
                    idx++;
                }
                nodeWrap.appendChild(childrenWrap);

                const closeLine = document.createElement('div');
                closeLine.className = 'json-close';
                const comma = isLast ? '' : ',';
                closeLine.textContent = `${indent(level)}${isArray ? ']' : '}'}${comma}`;
                nodeWrap.appendChild(closeLine);

                return nodeWrap;
            };

            textView.appendChild(build(value, 0, null, true));
            currentJsonText = JSON.stringify(value, null, 2);
        }

        function setAllCollapsed(shouldCollapse) {
            const targets = currentView === 'tree' ? tree.querySelectorAll('li') : textView.querySelectorAll('.json-node');
            targets.forEach((li) => {
                if (shouldCollapse) {
                    li.classList.add('collapsed');
                } else {
                    li.classList.remove('collapsed');
                }
                const caret = li.querySelector('.caret');
                if (caret && caret.textContent !== '·') {
                    caret.textContent = shouldCollapse ? '▸' : '▾';
                }
            });
        }

        function runTransform() {
            const raw = input.value;
            if (!raw) {
                output.value = '';
                tree.innerHTML = '';
                currentObject = null;
                return;
            }
            const selected = mode.value === 'auto' ? autoDetect(raw) : mode.value;
            let result = raw;
            if (selected === 'json') {
                let parsed = parseJsonInput(raw);
                parsed = parseMaybeNestedJson(parsed);
                if (typeof parsed === 'object') {
                    parsed = expandEscapedLogPayload(parsed);
                    parsed = deepExpandJsonStrings(parsed);
                    currentObject = parsed;
                    result = JSON.stringify(parsed, null, 2);
                    renderTree(parsed);
                    renderText(parsed);
                } else {
                    currentObject = null;
                    result = parsed;
                    tree.innerHTML = '';
                    renderText(parsed);
                }
            } else if (selected === 'html') {
                result = decodeHtml(raw);
            } else if (selected === 'url') {
                result = decodeUrl(raw);
            } else if (selected === 'base64') {
                result = decodeBase64(raw);
            } else if (selected === 'csv') {
                result = unescapeCsv(raw);
            }
            output.value = result;
            if (selected !== 'json') {
                textView.textContent = result;
                currentJsonText = result;
                tree.innerHTML = '';
            }
        }

        async function readFile(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext === 'xlsx' || ext === 'xls') {
                if (typeof XLSX === 'undefined') {
                    textView.textContent = fileErrorXlsx;
                    return null;
                }
                const data = await file.arrayBuffer();
                const workbook = XLSX.read(data, { type: 'array' });
                const sheetName = workbook.SheetNames[0];
                const sheet = workbook.Sheets[sheetName];
                return XLSX.utils.sheet_to_json(sheet, { defval: '' });
            }
            if (ext === 'csv') {
                const text = await file.text();
                return csvToJson(text);
            }
            return file.text();
        }

        run.addEventListener('click', () => {
            runTransform();
        });

        let inputTimer = null;
        input.addEventListener('input', () => {
            if (inputTimer) clearTimeout(inputTimer);
            inputTimer = setTimeout(() => runTransform(), 250);
        });

        swap.addEventListener('click', () => {
            const temp = input.value;
            input.value = output.value;
            output.value = temp;
        });

        fileInput.addEventListener('change', async (event) => {
            const file = event.target.files[0];
            if (!file) {
                setFileLabel('');
                return;
            }
            setFileLabel(file.name);
            const ext = file.name.split('.').pop().toLowerCase();
            try {
                const result = await readFile(file);
                if (result === null) return;
                if (ext === 'csv' || ext === 'xlsx' || ext === 'xls') {
                    input.value = JSON.stringify(result, null, 2);
                    mode.value = 'json';
                } else {
                    input.value = result;
                }
                runTransform();
                fileInput.value = '';
            } catch {
                input.value = '';
                textView.textContent = fileErrorRead;
            }
        });

        copy.addEventListener('click', async () => {
            const payload = currentJsonText || output.value;
            if (!payload) return;
            try {
                await navigator.clipboard.writeText(payload);
                const original = copy.textContent;
                copy.textContent = 'Copied';
                copy.classList.add('pulse');
                setTimeout(() => {
                    copy.textContent = original;
                    copy.classList.remove('pulse');
                }, 1200);
            } catch {
                output.value = payload;
                output.select();
                document.execCommand('copy');
            }
        });

        viewText.addEventListener('click', () => setView('text'));
        viewTree.addEventListener('click', () => {
            setView('tree');
            if (currentObject) {
                renderTree(currentObject);
            }
        });
        expandBtn.addEventListener('click', () => setAllCollapsed(false));
        collapseBtn.addEventListener('click', () => setAllCollapsed(true));

        setView('text');
    </script>
@endsection
