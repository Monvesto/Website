// assets/roboforex.js – RoboForex Partner Dashboard

(function () {
    'use strict';

    const configEl = document.getElementById('rf-config');
    if (!configEl) return;
    let cfg;
    try { cfg = JSON.parse(configEl.textContent.trim()); }
    catch (e) { return; }

    const BASE       = cfg.base;
    const CONFIGURED = cfg.configured;
    let   currentAccountId = cfg.firstAccountId || '';

    // ── Meldung ───────────────────────────────────────────────────────────────
    const elMsg = document.getElementById('rf-message');
    function showMsg(type, text) {
        elMsg.className   = type === 'success' ? 'alert alert-success' : 'alert alert-error';
        elMsg.textContent = text;
        elMsg.hidden      = false;
        elMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        if (type === 'success') setTimeout(function () { elMsg.hidden = true; }, 5000);
    }

    // ── Konto-Auswahl ─────────────────────────────────────────────────────────
    const accountSelect = document.getElementById('rf-account-select');
    if (accountSelect) {
        accountSelect.addEventListener('change', function () {
            currentAccountId = this.value;
            loadAll();
        });
    }

    function apiUrl(action, extra) {
        let url = BASE + 'proxy.php?action=' + action;
        if (currentAccountId) url += '&account_id=' + encodeURIComponent(currentAccountId);
        if (window._rfForceRefresh) url += '&refresh=1';
        if (extra) url += '&' + extra;
        return url;
    }

    // ── Tabs ──────────────────────────────────────────────────────────────────
    document.querySelectorAll('.rf-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.rf-tab').forEach(function (t) { t.classList.remove('rf-tab--active'); });
            this.classList.add('rf-tab--active');
            document.querySelectorAll('.rf-tab-content').forEach(function (c) { c.hidden = true; });
            document.getElementById('rf-tab-' + this.dataset.tab).hidden = false;
        });
    });

    document.getElementById('btn-rf-refresh').addEventListener('click', function () {
        const btn = this;
        btn.disabled    = true;
        btn.textContent = 'Lädt...';

        // Alle Werte zurücksetzen
        ['rf-active-clients','rf-deposited-clients','rf-new-clients','rf-total-clients'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.textContent = '...';
        });
        ['today','tomorrow','week','month','total'].forEach(function(id) {
            const el = document.getElementById('rf-commission-' + id);
            if (el) el.textContent = '...';
        });

        sessionStorage.removeItem('rf_page_loaded_' + currentAccountId);
        sessionStorage.removeItem('rf_page_expiry_' + currentAccountId);
        window._rfForceRefresh = true;

        Promise.all([loadOverview(), loadClients(1), loadTree(), loadSymbolTable()]).finally(function () {
            btn.disabled        = false;
            btn.innerHTML       = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="13" height="13"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></svg> Aktualisieren';
            window._rfForceRefresh = false;
        });
    });

    async function loadAll() {
        await Promise.all([loadOverview(), loadClients(1), loadTree()]);
    }

    // ── Datums-Helfer ─────────────────────────────────────────────────────────
    function today()      { return new Date().toISOString().split('T')[0]; }
    function fmtDate(str) { if (!str) return '–'; return str.replace('T',' ').substring(0,16); }
    function fmtDe(str)   {
        if (!str) return '–';
        const [y,m,d] = str.split('-');
        return d + '.' + m + '.' + y;
    }
    function monday() {
        const d = new Date(), day = d.getDay();
        d.setDate(d.getDate() - day + (day === 0 ? -6 : 1));
        return d.toISOString().split('T')[0];
    }
    function monthStart() { return today().substring(0,8) + '01'; }
    function daysAgo(n)   { const d = new Date(); d.setDate(d.getDate()-n); return d.toISOString().split('T')[0]; }

    function badge(val) {
        return val === '1' || val === true
            ? '<span class="badge badge--success">Ja</span>'
            : '<span class="badge badge--muted">Nein</span>';
    }

    // ════════════════════════════════════════════════════════════════════════
    // ÜBERSICHT
    // ════════════════════════════════════════════════════════════════════════
    async function loadOverview() {
        if (!CONFIGURED) return;

        if (window._rfForceRefresh) {
            // Progressiv: Referralinfo zuerst, dann einzelne Provisions-Ranges
            await loadReferralInfo();
            const ranges = [
                ['today',    daysAgo(1),    daysAgo(1)],
                ['tomorrow', today(),       today()],
                ['week',     monday(),      today()],
                ['month',    monthStart(),  today()],
                ['total',    daysAgo(90),   today()],
            ];
            // Datum-Labels setzen
            setDateLabels();
            // Parallel laden
            await Promise.all(ranges.map(function([id, from, to]) {
                return loadSingleRange(id, from, to);
            }));
            setSyncTime('api');
        } else {
            // Aus Cache: alles auf einmal
            try {
                const res  = await fetch(apiUrl('overview'));
                const data = await res.json();
                console.log('overview source:', data.source);
                if (!data.success) return;
                applyOverviewData(data.data, data.source);
            } catch (e) { /* silent */ }
        }
    }

    async function loadReferralInfo() {
        try {
            const res  = await fetch(apiUrl('referralinfo'));
            const data = await res.json();
            if (!data.success || !data.data) return;
            const d = data.data;
            document.getElementById('rf-active-clients').textContent    = d.active_clients_in_one_month ?? '–';
            document.getElementById('rf-deposited-clients').textContent = d.deposited_clients           ?? '–';
            document.getElementById('rf-new-clients').textContent       = d.registrations               ?? '–';
            document.getElementById('rf-total-clients').textContent     = d.all_referral_count          ?? '–';
            const mnArr = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
            const nowTs = new Date();
            const moLbl = 'seit 01. ' + mnArr[nowTs.getMonth()];
            const sub  = document.getElementById('rf-active-clients-sub');
            if (sub)  sub.textContent  = moLbl;
            const sub2 = document.getElementById('rf-new-clients-sub');
            if (sub2) sub2.textContent = moLbl;
        } catch (e) { /* silent */ }
    }

    async function loadSingleRange(id, from, to) {
        const el = document.getElementById('rf-commission-' + id);
        try {
            const res  = await fetch(apiUrl('commission_range', 'from=' + from + '&to=' + to + '&cache_key=' + id));
            const data = await res.json();
            if (el) el.textContent = data.success ? (parseFloat(data.total) >= 0 ? '+' : '') + parseFloat(data.total).toFixed(4) + ' USD' : '–';
        } catch (e) { if (el) el.textContent = '–'; }
    }

    function setSyncTime(source) {
        const syncEl = document.getElementById('rf-sync-time');
        if (!syncEl) return;
        const now = new Date();
        const src = source === 'cache' ? 'Cache' : 'API';
        syncEl.textContent = '(aktualisiert am ' + now.toLocaleDateString('de-DE') + ' um ' + now.toLocaleTimeString('de-DE', {hour:'2-digit',minute:'2-digit'}) + ' Uhr · ' + src + ')';
    }

    function setDateLabels() {
        const mnArr   = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
        const nowDate = new Date();
        const monthEl = document.getElementById('rf-commission-month-label');
        if (monthEl) monthEl.textContent = 'Diesen Monat (' + mnArr[nowDate.getMonth()] + ')';
        document.getElementById('rf-commission-today-date').textContent    = 'Trades vom ' + fmtDe(daysAgo(1));
        document.getElementById('rf-commission-tomorrow-date').textContent = 'Trades vom ' + fmtDe(today());
        document.getElementById('rf-commission-week-date').textContent     = 'seit ' + fmtDe(monday());
        document.getElementById('rf-commission-month-date').textContent    = 'seit ' + fmtDe(monthStart());
        document.getElementById('rf-commission-total-date').textContent    = 'seit ' + fmtDe(daysAgo(90));
    }

    function applyOverviewData(d, source) {
        const ri = d.referralinfo || {};
        const co = d.commission   || {};

        document.getElementById('rf-active-clients').textContent    = ri.active_clients_in_one_month ?? '–';
        document.getElementById('rf-deposited-clients').textContent = ri.deposited_clients           ?? '–';
        document.getElementById('rf-new-clients').textContent       = ri.registrations               ?? '–';
        document.getElementById('rf-total-clients').textContent     = ri.all_referral_count          ?? '–';

        const mnArr = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
        const nowTs = new Date();
        const moLbl = 'seit 01. ' + mnArr[nowTs.getMonth()];
        const sub  = document.getElementById('rf-active-clients-sub');
        if (sub)  sub.textContent  = moLbl;
        const sub2 = document.getElementById('rf-new-clients-sub');
        if (sub2) sub2.textContent = moLbl;

        setDateLabels();

        function setC(id, val) {
            const el = document.getElementById('rf-commission-' + id);
            if (el) el.textContent = val !== undefined ? (parseFloat(val) >= 0 ? '+' : '') + parseFloat(val).toFixed(4) + ' USD' : '–';
        }
        setC('today',    co.today);
        setC('tomorrow', co.tomorrow);
        setC('week',     co.week);
        setC('month',    co.month);
        setC('total',    co.total);

        setSyncTime(source);
    }

    async function loadCommissionRange(id, from, to) {
        const el = document.getElementById('rf-commission-' + id);
        if (!el) return;
        el.textContent = '...';
        try {
            const res  = await fetch(apiUrl('commission_range', 'from=' + from + '&to=' + to + '&cache_key=' + id));
            const data = await res.json();
            el.textContent = data.success ? (data.total >= 0 ? '+' : '') + data.total.toFixed(4) + ' USD' : '–';
        } catch (e) { el.textContent = '–'; }
    }

    // Symbol-Tabelle
    document.getElementById('btn-rf-symbol-load').addEventListener('click', loadSymbolTable);

    async function loadSymbolTable() {
        const from  = document.getElementById('rf-symbol-from').value;
        const to    = document.getElementById('rf-symbol-to').value;
        const tbody = document.querySelector('#rf-symbol-table tbody');
        tbody.innerHTML = '<tr><td colspan="5" class="empty-state">Lade...</td></tr>';
        document.getElementById('rf-symbol-total').hidden = true;

        try {
            const res  = await fetch(apiUrl('commission_by_symbol', 'from=' + from + '&to=' + to));
            const data = await res.json();
            if (!data.success || !data.symbols.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="empty-state">Keine Daten für diesen Zeitraum.</td></tr>';
                return;
            }
            let totalComm = 0, totalLot = 0;
            tbody.innerHTML = data.symbols.map(function (s) {
                totalComm += s.commission;
                totalLot  += s.volume;
                return '<tr>'
                    + '<td class="fw-700">' + s.symbol + '</td>'
                    + '<td>' + s.trades + '</td>'
                    + '<td>' + s.volume.toFixed(2) + '</td>'
                    + '<td class="text-green fw-700">+' + s.commission.toFixed(4) + '</td>'
                    + '<td class="text-muted">' + s.per_lot.toFixed(4) + '</td>'
                    + '</tr>';
            }).join('');
            const el = document.getElementById('rf-symbol-total');
            el.innerHTML = '<strong>Gesamt:</strong> +' + totalComm.toFixed(4) + ' USD &nbsp;|&nbsp; '
                         + totalLot.toFixed(2) + ' Lot &nbsp;|&nbsp; '
                         + data.symbols.length + ' Symbole &nbsp;|&nbsp; '
                         + fmtDe(from) + ' – ' + fmtDe(to);
            el.hidden = false;
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-state">Fehler: ' + e.message + '</td></tr>';
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // CLIENTS
    // ════════════════════════════════════════════════════════════════════════
    let clientsData  = [];
    let labelsData   = {};

    async function loadClients(page) {
        if (!CONFIGURED) return;
        const tbody = document.querySelector('#rf-clients-table tbody');
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Lade...</td></tr>';
        try {
            const res  = await fetch(apiUrl('partners', 'page=' + (page || 1)));
            const data = await res.json();
            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-state error">' + data.message + '</td></tr>';
                return;
            }
            labelsData  = data.labels || {};
            clientsData = data.clients || [];

            const src = data.source === 'cache' ? ' (Cache)' : ' (API)';
            document.getElementById('rf-clients-count').textContent =
                clientsData.length + ' Konten' + src;

            sortAndRenderClients();
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Fehler: ' + e.message + '</td></tr>';
        }
    }

    let clientsSort = { col: 'client_account_id', dir: 1 };

    function sortAndRenderClients() {
        const col = clientsSort.col;
        const dir = clientsSort.dir;
        const sorted = clientsData.slice().sort(function (a, b) {
            let av = a[col] || '', bv = b[col] || '';
            if (col === 'has_reached_deposit_threshold' || col === 'is_active_accrual_of_commission') {
                av = parseInt(av); bv = parseInt(bv);
            }
            if (av < bv) return -1 * dir;
            if (av > bv) return  1 * dir;
            return 0;
        });
        // Sortier-Icons aktualisieren
        document.querySelectorAll('#rf-clients-table .rf-sortable').forEach(function (th) {
            const icon = th.querySelector('.rf-sort-icon');
            if (!icon) return;
            if (th.dataset.col === col) {
                th.classList.add('rf-sort-active');
                icon.textContent = dir === 1 ? '↑' : '↓';
            } else {
                th.classList.remove('rf-sort-active');
                icon.textContent = '↕';
            }
        });
        renderClients(sorted);
    }

    function renderClients(list) {
        const tbody = document.querySelector('#rf-clients-table tbody');
        if (!list.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Keine Konten.</td></tr>'; return; }
        tbody.innerHTML = list.map(function (r) {
            const id    = r.client_account_id || '–';
            const label = r.label || '';
            return '<tr>'
                + '<td class="fw-700">' + id + '</td>'
                + '<td>'
                +   '<span class="rf-label-display" data-id="' + id + '">'
                +     (label ? '<span class="badge badge--success">' + label + '</span>' : '<span class="text-muted">–</span>')
                +   '</span>'
                +   '<button class="btn btn-xs btn-ghost rf-btn-label" data-id="' + id + '" data-label="' + (label || '') + '" title="Name bearbeiten">✏</button>'
                + '</td>'
                + '<td>' + (r.account_type || '–') + '</td>'
                + '<td class="text-muted">' + fmtDate(r.reg_date || '') + '</td>'
                + '<td>' + badge(r.has_reached_deposit_threshold) + '</td>'
                + '<td>' + badge(r.is_active_accrual_of_commission) + '</td>'
                + '</tr>';
        }).join('');
    }

    // Clients Sortierung
    document.querySelector('#rf-clients-table thead').addEventListener('click', function (e) {
        const th = e.target.closest('.rf-sortable');
        if (!th) return;
        const col = th.dataset.col;
        clientsSort.dir = (clientsSort.col === col) ? clientsSort.dir * -1 : 1;
        clientsSort.col = col;
        sortAndRenderClients();
    });

    // Label-Edit inline
    document.querySelector('#rf-clients-table').addEventListener('click', function (e) {
        const btn = e.target.closest('.rf-btn-label');
        if (!btn) return;
        openLabelModal(btn.dataset.id, btn.dataset.label);
    });

    // Suche
    document.getElementById('rf-clients-search').addEventListener('input', function () {
        const q    = this.value.toLowerCase();
        const list = q
            ? clientsData.filter(function (r) {
                return (r.client_account_id || '').toLowerCase().includes(q)
                    || (r.label || '').toLowerCase().includes(q);
              })
            : clientsData;
        sortAndRenderClients();
    });

    // ── Label-Modal ───────────────────────────────────────────────────────────
    let labelModalAccountId = '';

    function openLabelModal(accountId, currentLabel) {
        labelModalAccountId = accountId;
        document.getElementById('rf-label-modal-id').textContent = accountId;
        document.getElementById('rf-label-input').value          = currentLabel || '';
        document.getElementById('rf-label-modal').hidden         = false;
        document.getElementById('rf-label-input').focus();
    }

    document.getElementById('rf-label-cancel').addEventListener('click', function () {
        document.getElementById('rf-label-modal').hidden = true;
    });

    document.getElementById('rf-label-save').addEventListener('click', async function () {
        const label = document.getElementById('rf-label-input').value.trim();
        const fd    = new FormData();
        fd.append('client_account_id', labelModalAccountId);
        fd.append('label', label);
        try {
            const res  = await fetch(BASE + 'proxy.php?action=save_label', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showMsg('success', 'Name gespeichert.');
                document.getElementById('rf-label-modal').hidden = true;
                clientsData = clientsData.map(function (r) {
                    if (r.client_account_id === labelModalAccountId) r.label = label;
                    return r;
                });
                labelsData[labelModalAccountId] = { label: label };
                renderClients(clientsData);
                // Baum-Knoten Label aktualisieren ohne neu zu laden
                document.querySelectorAll('.rf-tree-edit-btn[data-id="' + labelModalAccountId + '"]').forEach(function(btn) {
                    btn.dataset.label = label;
                    const row    = btn.closest('.rf-tree-row');
                    const oldLbl = row.querySelector('.rf-tree-label');
                    if (oldLbl) oldLbl.remove();
                    if (label) {
                        const idSpan = row.querySelector('.rf-tree-id');
                        const newLbl = document.createElement('span');
                        newLbl.className   = 'rf-tree-label';
                        newLbl.textContent = label;
                        idSpan.parentNode.insertBefore(newLbl, idSpan.nextSibling);
                    }
                });
            } else { showMsg('error', data.message); }
        } catch (e) { showMsg('error', e.message); }
    });

    // ════════════════════════════════════════════════════════════════════════
    // PARTNER-BAUM
    // ════════════════════════════════════════════════════════════════════════
    async function loadTree() {
        if (!CONFIGURED) return;
        const container = document.getElementById('rf-tree-container');
        container.innerHTML = '<div class="empty-state">Lade Partner-Baum...</div>';
        try {
            const res  = await fetch(apiUrl('tree'));
            const data = await res.json();
            if (!data.success) { container.innerHTML = '<div class="empty-state error">' + data.message + '</div>'; return; }
            const labels = data.labels || labelsData || {};

            if (data.source === 'cache') {
                // Cache-Format: flache Zeilen rekonstruieren
                renderTreeFromCache(data.tree, data.root, container, labels);
            } else {
                // API-Format: vollständiger Baum
                container.innerHTML = '<div class="rf-tree">' + buildTreeNodeNew(data.treeData, 0, labels) + '</div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="empty-state">Fehler: ' + e.message + '</div>';
        }
    }

    function buildTreeNodeNew(node, depth, labels) {
        if (!node) return '';
        const id       = node.id || '–';
        const type     = node.type || '';
        const children = node.children || [];
        const lbl      = labels[id] ? labels[id].label : '';
        const isRoot   = depth === 0;
        const expanded = isRoot;
        const levelLabel = 'L' + depth;

        let html = '<div class="rf-tree-node rf-tree-depth-' + depth + '">'
                 + '<div class="rf-tree-row">'
                 + (children.length
                    ? '<span class="rf-tree-toggle" data-expanded="' + (expanded?'1':'0') + '">' + (expanded?'▼':'▶') + '</span>'
                    : '<span class="rf-tree-toggle-empty"></span>')
                 + '<span class="rf-tree-account' + (isRoot ? ' rf-tree-account--root' : '') + '">'
                 + '<span class="rf-tree-level rf-tree-level-' + depth + '">' + levelLabel + '</span>'
                 + '<span class="rf-tree-id">' + id + '</span>'
                 + (lbl ? ' <span class="rf-tree-label">' + lbl + '</span>' : '')
                 + '<span class="rf-tree-type">&nbsp;' + type + '</span>'
                 + (children.length ? '<span class="rf-tree-count">' + children.length + '</span>' : '')
                 + '</span>'
                 + (isRoot ? '' : '<button class="btn btn-xs btn-ghost rf-btn-label rf-tree-edit-btn" data-id="' + id + '" data-label="' + (lbl || '') + '" title="Name bearbeiten">✏</button>')
                 + '</div>';

        if (children.length) {
            html += '<div class="rf-tree-children"' + (expanded ? '' : ' hidden') + '>';
            children.forEach(function(child) {
                html += buildTreeNodeNew(child, depth + 1, labels);
            });
            html += '</div>';
        }
        html += '</div>';
        return html;
    }

    function renderTreeFromCache(rows, rootId, container, labels) {
        if (!rows || !rows.length) {
            container.innerHTML = '<div class="empty-state">Keine Baum-Daten im Cache.</div>';
            return;
        }
        // Flache Zeilen in verschachtelten Baum umwandeln
        const nodeMap = {};
        nodeMap[rootId] = { id: rootId, type: '', children: [] };
        rows.forEach(function(row) {
            if (!nodeMap[row.child_id]) nodeMap[row.child_id] = { id: row.child_id, type: row.account_type || '', children: [] };
            if (!nodeMap[row.parent_id]) nodeMap[row.parent_id] = { id: row.parent_id, type: '', children: [] };
            nodeMap[row.parent_id].children.push(nodeMap[row.child_id]);
        });
        container.innerHTML = '<div class="rf-tree">' + buildTreeNodeNew(nodeMap[rootId], 0, labels) + '</div>';
    }

    function buildTreeNode(data, depth, labels) {
        labels = labels || {};
        if (!data) return '';
        const id      = data['@attributes'] ? data['@attributes'].id : '–';
        const type    = data.type || '';
        const lbl     = labels[id] ? labels[id].label : '';
        const accs    = data.referrals ? (data.referrals.account || []) : [];
        const list    = Array.isArray(accs) ? accs : (accs && accs['@attributes'] ? [accs] : []);
        const isRoot  = depth === 0;
        const expanded= isRoot;

        let html = '<div class="rf-tree-node rf-tree-depth-' + depth + '">'
                 + '<div class="rf-tree-row">'
                 + (list.length
                    ? '<span class="rf-tree-toggle" data-expanded="' + (expanded?'1':'0') + '">' + (expanded?'▼':'▶') + '</span>'
                    : '<span class="rf-tree-toggle-empty"></span>')
                 + '<span class="rf-tree-account' + (isRoot ? ' rf-tree-account--root' : '') + '">'
                 + '<span class="rf-tree-id">' + id + '</span>'
                 + (lbl ? ' <span class="rf-tree-label">' + lbl + '</span>' : '')
                 + '<span class="rf-tree-type">&nbsp;&nbsp;' + type + '</span>'
                 + (list.length ? '<span class="rf-tree-count">' + list.length + '</span>' : '')
                 + '</span></div>';

        if (list.length) {
            html += '<div class="rf-tree-children"' + (expanded ? '' : ' hidden') + '>';
            list.forEach(function (acc) {
                const cid     = acc['@attributes'] ? acc['@attributes'].id : '–';
                const ctype   = acc.type || '';
                const clbl    = labels[cid] ? labels[cid].label : '';
                const subs    = acc.referrals ? (acc.referrals.account || []) : [];
                const subList = Array.isArray(subs) ? subs : (subs && subs['@attributes'] ? [subs] : []);

                html += '<div class="rf-tree-node rf-tree-depth-' + (depth+1) + '">'
                      + '<div class="rf-tree-row">'
                      + (subList.length ? '<span class="rf-tree-toggle" data-expanded="0">▶</span>' : '<span class="rf-tree-toggle-empty"></span>')
                      + '<span class="rf-tree-account">'
                      + '<span class="rf-tree-id">' + cid + '</span>'
                      + (clbl ? ' <span class="rf-tree-label">' + clbl + '</span>' : '')
                      + '<span class="rf-tree-type">&nbsp;&nbsp;' + ctype + '</span>'
                      + (subList.length ? '<span class="rf-tree-count">' + subList.length + '</span>' : '')
                      + '</span></div>';

                if (subList.length) {
                    html += '<div class="rf-tree-children" hidden>';
                    subList.forEach(function (sub) {
                        const sid   = sub['@attributes'] ? sub['@attributes'].id : '–';
                        const stype = sub.type || '';
                        const slbl  = labels[sid] ? labels[sid].label : '';
                        html += '<div class="rf-tree-node rf-tree-depth-' + (depth+2) + '">'
                              + '<div class="rf-tree-row"><span class="rf-tree-toggle-empty"></span>'
                              + '<span class="rf-tree-account rf-tree-account--leaf">'
                              + '<span class="rf-tree-id">' + sid + '</span>'
                              + (slbl ? ' <span class="rf-tree-label">' + slbl + '</span>' : '')
                              + '<span class="rf-tree-type">&nbsp;&nbsp;' + stype + '</span>'
                              + '</span></div></div>';
                    });
                    html += '</div>';
                }
                html += '</div>';
            });
            html += '</div>';
        }
        html += '</div>';
        return html;
    }

    // Toggle auf/zuklappen
    document.getElementById('rf-tree-container').addEventListener('click', function (e) {
        const toggle = e.target.closest('.rf-tree-toggle');
        if (toggle) {
            const node     = toggle.closest('.rf-tree-node');
            const children = node.querySelector('.rf-tree-children');
            if (!children) return;
            const expanded = toggle.dataset.expanded === '1';
            children.hidden          = expanded;
            toggle.dataset.expanded  = expanded ? '0' : '1';
            toggle.textContent       = expanded ? '▶' : '▼';
            return;
        }
        // Name bearbeiten im Baum
        const editBtn = e.target.closest('.rf-tree-edit-btn');
        if (editBtn) {
            openLabelModal(editBtn.dataset.id, editBtn.dataset.label);
        }
    });

    document.getElementById('btn-rf-tree-search').addEventListener('click', async function () {
        const ref = document.getElementById('rf-tree-search').value.trim();
        if (!ref) return;
        const container = document.getElementById('rf-tree-container');
        container.innerHTML = '<div class="empty-state">Suche ' + ref + '...</div>';
        try {
            const res  = await fetch(apiUrl('tree_search', 'referral=' + encodeURIComponent(ref)));
            const data = await res.json();
            if (!data.success) { container.innerHTML = '<div class="empty-state error">' + data.message + '</div>'; return; }
            container.innerHTML = '<div class="rf-tree">' + buildTreeNode(data.data, 0) + '</div>';
        } catch (e) {
            container.innerHTML = '<div class="empty-state">Fehler: ' + e.message + '</div>';
        }
    });

    // ════════════════════════════════════════════════════════════════════════
    // PROVISIONEN DETAIL
    // ════════════════════════════════════════════════════════════════════════
    // ── Provisions-Sortierung ─────────────────────────────────────────────────
    let commissionData  = [];
    let commissionSort  = { col: 'amount', dir: -1 }; // Standard: Provision absteigend

    function sortAndRenderCommission() {
        const col = commissionSort.col;
        const dir = commissionSort.dir;
        const sorted = commissionData.slice().sort(function (a, b) {
            let va = a[col] || '';
            let vb = b[col] || '';
            if (col === 'amount' || col === 'volume' || col === 'level') {
                va = parseFloat(va) || 0;
                vb = parseFloat(vb) || 0;
            }
            if (va < vb) return -1 * dir;
            if (va > vb) return  1 * dir;
            return 0;
        });

        // Sort-Icons aktualisieren
        document.querySelectorAll('#rf-commission-table .rf-sortable').forEach(function (th) {
            const icon = th.querySelector('.rf-sort-icon');
            if (th.dataset.col === col) {
                icon.textContent = dir === 1 ? '↑' : '↓';
                th.classList.add('rf-sort-active');
            } else {
                icon.textContent = '↕';
                th.classList.remove('rf-sort-active');
            }
        });

        const tbody = document.querySelector('#rf-commission-table tbody');
        let total = 0;
        tbody.innerHTML = sorted.map(function (r) {
            const id     = r['@attributes'] ? r['@attributes'].id : (r.id || '–');
            const login  = r.login || '–';
            const lbl    = labelsData[login] ? labelsData[login].label : '';
            const amount = parseFloat(r.amount || 0);
            total += amount;
            return '<tr>'
                + '<td class="fw-700">' + id + '</td>'
                + '<td>' + login + '</td>'
                + '<td>' + (lbl ? '<span class="rf-tree-label">' + lbl + '</span>' : '') + '</td>'
                + '<td>' + (r.symbol || '–') + '</td>'
                + '<td class="text-muted">' + (r.volume || '–') + '</td>'
                + '<td class="text-muted">' + fmtDate(r.close_time || '') + '</td>'
                + '<td class="text-muted">' + (r.server || '–') + '</td>'
                + '<td class="text-muted">' + (r.level  || '–') + '</td>'
                + '<td class="text-green fw-700">+' + amount.toFixed(4) + '</td>'
                + '</tr>';
        }).join('');

        document.getElementById('rf-commission-sum').textContent  = total.toFixed(4);
        document.getElementById('rf-commission-rows').textContent = sorted.length;
        document.getElementById('rf-commission-summary').hidden   = false;
    }

    document.querySelector('#rf-commission-table thead').addEventListener('click', function (e) {
        const th = e.target.closest('.rf-sortable');
        if (!th) return;
        const col = th.dataset.col;
        commissionSort.dir = (commissionSort.col === col) ? commissionSort.dir * -1 : -1;
        commissionSort.col = col;
        sortAndRenderCommission();
    });

    document.getElementById('btn-rf-commission-load').addEventListener('click', function () {
        const date = document.getElementById('rf-commission-date').value;
        if (date) loadCommissionDetail(date, 1);
    });

    async function loadCommissionDetail(date, page) {
        const tbody = document.querySelector('#rf-commission-table tbody');
        tbody.innerHTML = '<tr><td colspan="9" class="empty-state">Lade...</td></tr>';
        document.getElementById('rf-commission-summary').hidden = true;
        try {
            const res  = await fetch(apiUrl('commission', 'date=' + date + '&page=' + page));
            const data = await res.json();
            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="8" class="empty-state error">' + data.message + '</td></tr>';
                return;
            }
            const d    = data.data || {};
            const meta = d['@attributes'] || {};
            const items= d.ticket || [];
            commissionData = Array.isArray(items) ? items : (items && items['@attributes'] ? [items] : []);

            if (!commissionData.length) {
                tbody.innerHTML = '<tr><td colspan="9" class="empty-state">Keine Provisionen für ' + fmtDe(date) + '.</td></tr>';
                return;
            }

            sortAndRenderCommission();

            renderPagination('rf-commission-pagination', parseInt(meta.pages || 1), page, function (p) {
                loadCommissionDetail(date, p);
            });
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="8" class="empty-state">Fehler: ' + e.message + '</td></tr>';
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // KONTO-VERWALTUNG
    // ════════════════════════════════════════════════════════════════════════
    function openAccountModal(id, label, accountId, apiKey, sort, userId) {
        document.getElementById('rf-modal-title').textContent   = id ? 'Konto bearbeiten' : 'Konto hinzufügen';
        document.getElementById('rf-modal-id').value            = id || '';
        document.getElementById('rf-modal-label').value         = label || '';
        document.getElementById('rf-modal-account-id').value    = accountId || '';
        document.getElementById('rf-modal-api-key').value       = id ? '••••••••' : '';
        document.getElementById('rf-modal-sort').value          = sort || 0;
        const userSel = document.getElementById('rf-modal-user');
        if (userSel) userSel.value = userId || '';
        document.getElementById('rf-account-modal').hidden      = false;
    }

    ['btn-rf-add-account', 'btn-rf-add-account2'].forEach(function (btnId) {
        const btn = document.getElementById(btnId);
        if (btn) btn.addEventListener('click', function () { openAccountModal(); });
    });

    document.getElementById('rf-modal-cancel').addEventListener('click', function () {
        document.getElementById('rf-account-modal').hidden = true;
    });

    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.btn-rf-edit-account');
        if (editBtn) {
            openAccountModal(
                editBtn.dataset.id,
                editBtn.dataset.label,
                editBtn.dataset.accountId,
                editBtn.dataset.apiKey,
                editBtn.dataset.sort,
                editBtn.dataset.userId
            );
        }
        const delBtn = e.target.closest('.btn-rf-delete-account');
        if (delBtn) {
            if (!confirm('Konto "' + delBtn.dataset.label + '" wirklich löschen?')) return;
            const fd = new FormData();
            fd.append('id', delBtn.dataset.id);
            fetch(BASE + 'proxy.php?action=delete_account', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) { showMsg('success', 'Konto gelöscht. Seite wird neu geladen...'); setTimeout(function () { location.reload(); }, 1200); }
                    else showMsg('error', data.message);
                });
        }
    });

    document.getElementById('rf-modal-save').addEventListener('click', async function () {
        const fd = new FormData();
        fd.append('id',          document.getElementById('rf-modal-id').value);
        fd.append('label',       document.getElementById('rf-modal-label').value);
        fd.append('account_id',  document.getElementById('rf-modal-account-id').value);
        fd.append('api_key',     document.getElementById('rf-modal-api-key').value);
        fd.append('sort_order',  document.getElementById('rf-modal-sort').value);
        const userSel = document.getElementById('rf-modal-user');
        if (userSel) fd.append('user_id', userSel.value);
        try {
            const res  = await fetch(BASE + 'proxy.php?action=save_account', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showMsg('success', 'Gespeichert. Seite wird neu geladen...');
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                showMsg('error', data.message);
            }
        } catch (e) { showMsg('error', e.message); }
        document.getElementById('rf-account-modal').hidden = true;
    });

    // ── Pagination ────────────────────────────────────────────────────────────
    function renderPagination(containerId, totalPages, currentPage, onPage) {
        const el = document.getElementById(containerId);
        if (!el || totalPages <= 1) { if (el) el.innerHTML = ''; return; }
        let html = '<div class="rf-pages">';
        for (let i = 1; i <= Math.min(totalPages, 20); i++) {
            html += '<button class="btn btn-xs ' + (i === currentPage ? 'btn-primary' : 'btn-ghost') + ' rf-page-btn" data-page="' + i + '">' + i + '</button>';
        }
        html += '</div>';
        el.innerHTML = html;
        el.querySelectorAll('.rf-page-btn').forEach(function (btn) {
            btn.addEventListener('click', function () { onPage(parseInt(this.dataset.page)); });
        });
    }

    // ── Initial laden – immer aus Cache, API nur wenn Cache leer ─────────────
    if (CONFIGURED) {
        loadAll();
        loadSymbolTable();
    }

})();