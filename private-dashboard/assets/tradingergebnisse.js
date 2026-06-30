// ════════════════════════════════════════════════
// assets/tradingergebnisse.js – Trading Tagesupdate
// CSP-konform: keine inline-Styles, keine onclick-Handler
// ════════════════════════════════════════════════

(function () {
    'use strict';

    // ── Konfiguration aus JSON-Tag ────────────────────────────────────────────
    const configEl = document.getElementById('trading-config');
    if (!configEl) { console.error('trading-config fehlt'); return; }
    let cfg;
    try { cfg = JSON.parse(configEl.textContent.trim()); }
    catch (e) { console.error('trading-config JSON-Fehler:', e); return; }

    const BASE           = cfg.base;
    const TRADING_START  = cfg.tradingStart;
    const START_BALANCES = cfg.startBalances;
    const CALC_BASES     = cfg.calcBases;

    const formCard = document.getElementById('trading-form-card');
    if (formCard) formCard.style.marginBottom = '24px';

    // ── Handelstag berechnen ──────────────────────────────────────────────────
    function calcTradingDay(dateStr) {
        const start  = new Date(TRADING_START + 'T00:00:00');
        const target = new Date(dateStr       + 'T00:00:00');
        return Math.max(1, Math.round((target - start) / 86400000) + 1);
    }

    document.getElementById('entry_date').addEventListener('change', function () {
        document.getElementById('trading-day-display').value = 'Tag ' + calcTradingDay(this.value);
    });

    // ── €↔% Umrechnung (nur Gewinn + Rendite, kein Kontostand mehr) ──────────
    let converting = false;
    document.querySelectorAll('input[data-account]').forEach(function (el) {
        el.addEventListener('input', function () {
            if (converting) return;
            converting = true;
            const account = this.dataset.account;
            const type    = this.dataset.type;
            const val     = parseFloat(this.value.replace(',', '.'));
            // Berechnungsgrundlage, Fallback Startsumme
            const basis   = (CALC_BASES[account] !== null && CALC_BASES[account] !== undefined)
                          ? CALC_BASES[account] : START_BALANCES[account];

            if (!isNaN(val) && basis) {
                const elP = document.getElementById(account + '_profit');
                const elR = document.getElementById(account + '_return');
                if (type === 'profit') {
                    elR.value = (val / basis * 100).toFixed(2);
                }
                if (type === 'return') {
                    elP.value = (val / 100 * basis).toFixed(2);
                }
            }
            converting = false;
        });
    });

    // ── Meldung ───────────────────────────────────────────────────────────────
    const elMessage = document.getElementById('trading-message');
    function showMessage(type, text) {
        elMessage.className = type === 'success' ? 'alert alert-success' : 'alert alert-error';
        elMessage.textContent = text;
        elMessage.hidden = false;
        elMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        if (type === 'success') setTimeout(function () { elMessage.hidden = true; }, 6000);
    }

    // ── Formular zurücksetzen ─────────────────────────────────────────────────
    function resetForm() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('entry_date').value          = today;
        document.getElementById('entry_date').readOnly       = false;
        document.getElementById('trading-day-display').value = 'Tag ' + calcTradingDay(today);
        document.getElementById('edit_id').value             = '';
        document.getElementById('force_update').value        = '0';
        document.getElementById('form-headline').textContent = 'Neuer Eintrag';
        document.getElementById('btn-cancel-edit').hidden    = true;
        ['main', 'ea', 'challenge'].forEach(function (k) {
            ['profit', 'return'].forEach(function (t) {
                document.getElementById(k + '_' + t).value = '';
            });
            const posEl = document.getElementById('open-positions-' + k);
            if (posEl) posEl.hidden = true;
            document.getElementById(k + '_open_json').value = '';
        });
    }

    // ── Speichern ─────────────────────────────────────────────────────────────
    async function doSave(force) {
        const fd = new FormData();
        fd.append('entry_date',   document.getElementById('entry_date').value);
        fd.append('edit_id',      document.getElementById('edit_id').value);
        fd.append('force_update', force ? '1' : '0');
        ['main', 'ea', 'challenge'].forEach(function (k) {
            fd.append(k + '_return', document.getElementById(k + '_return').value);
            fd.append(k + '_profit', document.getElementById(k + '_profit').value);
            fd.append(k + '_open',   document.getElementById(k + '_open_json').value);
        });
        let res, data;
        try {
            res  = await fetch(BASE + 'save.php', { method: 'POST', body: fd });
            data = await res.json();
        } catch (e) {
            showMessage('error', 'Netzwerkfehler: ' + e.message);
            return;
        }
        if (data.exists && !force) {
            document.getElementById('modal-text').textContent =
                'Für den ' + formatDate(document.getElementById('entry_date').value) +
                ' existiert bereits ein Eintrag. Überschreiben?';
            document.getElementById('trading-modal').hidden = false;
            return;
        }
        if (data.success) {
            showMessage('success', data.message || 'Gespeichert ✓');
            resetForm();
            reloadTable();
            reloadStats();
            if (data.id) {
                createImageAfterSave(data.id);
                telegramPostAfterSave(data.id);
            } else if (data.action === 'update') {
                const editId = document.getElementById('edit_id').value;
                if (editId) {
                    createImageAfterSave(editId);
                    telegramPostAfterSave(editId);
                }
            }
        } else {
            showMessage('error', data.message || 'Fehler.');
        }
    }

    document.getElementById('btn-save').addEventListener('click',        function () { doSave(false); });
    document.getElementById('btn-cancel-edit').addEventListener('click', resetForm);
    document.getElementById('modal-cancel').addEventListener('click',    function () { document.getElementById('trading-modal').hidden = true; });
    document.getElementById('modal-confirm').addEventListener('click',   function () { document.getElementById('trading-modal').hidden = true; doSave(true); });

    // ── Einstellungen-Modal öffnen ────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-startbal');
        if (!btn) return;

        const key       = btn.dataset.key;
        const currency  = btn.dataset.currency  || 'USD';
        const mfxId     = btn.dataset.mfxid     || '';
        const startBal  = btn.dataset.startbal  || '';
        const startDate = btn.dataset.startdate || '';
        const calcBasis = btn.dataset.calcbasis || '';
        const rfType    = btn.dataset.rftype    || '';
        const rfAccId   = btn.dataset.rfaccid   || '';
        const rfServer  = btn.dataset.rfserver  || '';
        const rfLeverage = btn.dataset.rfleverage || '';
        const mode      = btn.dataset.mode      || 'full'; // 'full' oder 'calcbasis'
        const labels    = { main: 'Main Account', ea: 'Monvesto EA', challenge: 'Road to 100k' };

        // Werte vorausfüllen
        document.getElementById('startbal-account-key').value  = key;
        document.getElementById('startbal-input').value        = startBal;
        document.getElementById('startdate-input').value       = startDate;
        document.getElementById('calcbasis-input').value       = calcBasis;
        document.getElementById('startbal-currency').value     = currency;
        document.getElementById('startbal-myfxbook-id').value  = mfxId;
        document.getElementById('rftype-input').value           = rfType;
        document.getElementById('rfaccid-input').value          = rfAccId;
        document.getElementById('rfserver-input').value         = rfServer;
        document.getElementById('rfleverage-input').value       = rfLeverage;

        const hasStart = startBal !== '' && startBal !== '0';
        const hasMfxId = mfxId    !== '';
        const hasDate  = startDate !== '';

        if (mode === 'calcbasis') {
            // Nur Berechnungsgrundlage ändern
            document.getElementById('startbal-modal-title').textContent = labels[key] + ' – Berechnungsgrundlage';
            document.getElementById('startbal-field').classList.add('hidden');
            document.getElementById('startdate-field').classList.add('hidden');
            document.getElementById('currency-field').classList.add('hidden');
            document.getElementById('myfxbook-field').classList.add('hidden');
        } else {
            // Alle Felder immer sichtbar (full-Modus vom KPI-Karten-Button)
            document.getElementById('startbal-modal-title').textContent = labels[key] + ' – Einstellungen';
            document.getElementById('startbal-field').classList.remove('hidden');
            document.getElementById('startdate-field').classList.remove('hidden');
            document.getElementById('currency-field').classList.remove('hidden');
            document.getElementById('myfxbook-field').classList.remove('hidden');
        }

        document.getElementById('startbal-modal').hidden = false;
    });

    document.getElementById('startbal-cancel').addEventListener('click', function () {
        document.getElementById('startbal-modal').hidden = true;
    });

    document.getElementById('startbal-save').addEventListener('click', async function () {
        const fd = new FormData();
        fd.append('account_key',   document.getElementById('startbal-account-key').value);
        fd.append('start_balance', document.getElementById('startbal-input').value);
        fd.append('start_date',    document.getElementById('startdate-input').value);
        fd.append('calc_basis',    document.getElementById('calcbasis-input').value);
        fd.append('currency',      document.getElementById('startbal-currency').value);
        fd.append('myfxbook_id',   document.getElementById('startbal-myfxbook-id').value);
        fd.append('rf_account_type', document.getElementById('rftype-input').value);
        fd.append('rf_account_id',   document.getElementById('rfaccid-input').value);
        fd.append('rf_server',       document.getElementById('rfserver-input').value);
        fd.append('rf_leverage',     document.getElementById('rfleverage-input').value);
        try {
            const res  = await fetch(BASE + 'myfxbook_proxy.php?action=save_settings', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showMessage('success', 'Gespeichert. Seite wird neu geladen...');
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                showMessage('error', data.message);
            }
        } catch (e) {
            showMessage('error', 'Fehler: ' + e.message);
        }
        document.getElementById('startbal-modal').hidden = true;
    });

    // ── Telegram-Button in Tabelle ────────────────────────────────────────────
    document.getElementById('trading-table').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-telegram-post');
        if (!btn) return;
        if (!confirm('Post to Telegram channel?')) return;
        const entryId = btn.dataset.id;
        btn.disabled = true;
        btn.textContent = '...';
        fetch(BASE + 'telegram_post.php?action=post&entry_id=' + entryId + '&type=combined&format=feed')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showMessage('success', 'Posted to Telegram ✓');
                } else {
                    showMessage('error', 'Telegram: ' + data.message);
                }
                btn.disabled = false;
                btn.textContent = 'Post';
            })
            .catch(function(e) {
                showMessage('error', 'Error: ' + e.message);
                btn.disabled = false;
                btn.textContent = 'Post';
            });
    });

    // ── Nach Speichern: Telegram auto-post ───────────────────────────────────
    async function telegramPostAfterSave(entryId) {
        if (!document.getElementById('chk-telegram-post').checked) return;
        try {
            const res  = await fetch(BASE + 'telegram_post.php?action=post&entry_id=' + entryId + '&type=combined&format=feed');
            const data = await res.json();
            if (data.success) {
                showMessage('success', 'Posted to Telegram ✓');
            } else {
                showMessage('error', 'Telegram: ' + (data.message || 'Error'));
            }
        } catch (e) { /* silent */ }
    }

    // ── GD-Test ───────────────────────────────────────────────────────────────
    document.getElementById('btn-gd-test').addEventListener('click', async function () {
        const res  = await fetch(BASE + 'generate_image.php?action=test');
        const data = await res.json();
        alert('GD: ' + JSON.stringify(data, null, 2));
    });

    // Telegram Test
    const tgTestBtn = document.createElement('button');
    tgTestBtn.className = 'btn btn-ghost btn-sm';
    tgTestBtn.textContent = 'TG-Test';
    tgTestBtn.style.marginLeft = '8px'; // wird durch CSP blockiert aber funktional
    document.getElementById('btn-gd-test').parentNode.appendChild(tgTestBtn);
    tgTestBtn.addEventListener('click', async function () {
        const res  = await fetch(BASE + 'telegram_post.php?action=test');
        const data = await res.json();
        alert('Telegram: ' + JSON.stringify(data, null, 2));
    });

    // ── Grafik-Button in Tabelle ──────────────────────────────────────────────
    document.getElementById('trading-table').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-create-image');
        if (!btn) return;
        document.getElementById('img-entry-id').value = btn.dataset.id;
        document.getElementById('img-preview').hidden = true;
        document.getElementById('image-modal').hidden = false;
    });

    document.getElementById('image-modal-cancel').addEventListener('click', function () {
        document.getElementById('image-modal').hidden = true;
    });
    document.getElementById('image-backdrop').addEventListener('click', function () {
        document.getElementById('image-modal').hidden = true;
    });

    document.getElementById('image-modal-generate').addEventListener('click', async function () {
        const btn      = this;
        const entryId  = document.getElementById('img-entry-id').value;
        const type     = document.getElementById('img-type').value;
        const format   = document.getElementById('img-format').value;

        btn.disabled = true;
        btn.textContent = 'Erstelle...';

        try {
            const res  = await fetch(BASE + 'generate_image.php?action=generate'
                + '&entry_id=' + entryId
                + '&type='     + type
                + '&format='   + format);

            let data;
            try { data = await res.json(); }
            catch (e) {
                const text = await res.text().catch(() => '');
                showMessage('error', 'PHP-Fehler: ' + (text || 'Leere Antwort'));
                btn.disabled = false; btn.textContent = 'Erstellen';
                return;
            }

            if (data.success) {
                const previewEl = document.getElementById('img-preview');
                document.getElementById('img-preview-img').src = data.url + '?t=' + Date.now();
                document.getElementById('img-download-link').href =
                    BASE + 'generate_image.php?action=download'
                    + '&entry_id=' + entryId
                    + '&type='     + type
                    + '&format='   + format;
                previewEl.hidden = false;
                showMessage('success', 'Grafik erstellt ✓');
            } else {
                showMessage('error', 'Grafik-Fehler: ' + (data.message || 'Unbekannt'));
            }
        } catch (e) {
            showMessage('error', 'Netzwerkfehler: ' + e.message);
        }

        btn.disabled = false;
        btn.textContent = 'Erstellen';
    });

    // ── Grafik nach Speichern automatisch erstellen ───────────────────────────
    async function createImageAfterSave(entryId) {
        if (!document.getElementById('chk-create-image').checked) return;
        try {
            const res = await fetch(BASE + 'generate_image.php?action=generate'
                + '&entry_id=' + entryId
                + '&type=combined&format=feed');
            // Nicht auf JSON warten – Grafik ist optional, Fehler werden ignoriert
        } catch (e) { /* silent */ }
    }

    // ── Edit-Button in Tabelle ────────────────────────────────────────────────
    document.getElementById('trading-table').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit-row');
        if (!btn) return;
        const d = btn.closest('tr').dataset;
        document.getElementById('entry_date').value          = d.date;
        document.getElementById('entry_date').readOnly       = true;
        document.getElementById('trading-day-display').value = 'Tag ' + calcTradingDay(d.date);
        document.getElementById('edit_id').value             = d.id;
        document.getElementById('force_update').value        = '1';
        document.getElementById('form-headline').textContent = 'Eintrag bearbeiten – ' + formatDate(d.date);
        document.getElementById('btn-cancel-edit').hidden    = false;
        document.getElementById('main_return').value        = d.mainReturn      || '';
        document.getElementById('main_profit').value        = d.mainProfit      || '';
        document.getElementById('ea_return').value          = d.eaReturn        || '';
        document.getElementById('ea_profit').value          = d.eaProfit        || '';
        document.getElementById('challenge_return').value   = d.challengeReturn || '';
        document.getElementById('challenge_profit').value   = d.challengeProfit || '';
        document.getElementById('trading-form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // ── MyFxBook laden ────────────────────────────────────────────────────────
    document.getElementById('btn-myfxbook').addEventListener('click', async function () {
        const btn = this;
        btn.disabled = true; btn.textContent = 'Lade...';
        let res, data;
        try {
            res  = await fetch(BASE + 'myfxbook_proxy.php?action=fetch_all');
            data = await res.json();
        } catch (e) {
            showMessage('error', 'MyFxBook Fehler: ' + e.message);
            btn.disabled = false; btn.textContent = '↺ MyFxBook laden'; return;
        }
        if (!data.success) {
            showMessage('error', 'MyFxBook: ' + (data.message || 'Fehler.'));
            btn.disabled = false; btn.textContent = '↺ MyFxBook laden'; return;
        }
        Object.entries(data.accounts).forEach(function ([key, acc]) {
            if (acc.error) { showMessage('error', acc.label + ': ' + acc.error); return; }
            if (acc.today_profit !== null) {
                document.getElementById(key + '_profit').value = acc.today_profit.toFixed(2);
                document.getElementById(key + '_profit').dispatchEvent(new Event('input'));
            }
            if (acc.today_return !== null) {
                document.getElementById(key + '_return').value = acc.today_return.toFixed(2);
            }
            if (acc.open_positions && acc.open_positions.length > 0) {
                document.getElementById(key + '_open_json').value = JSON.stringify(acc.open_positions);
                const posEl = document.getElementById('open-positions-' + key);
                posEl.innerHTML = '<strong>' + acc.open_trades + ' offen:</strong> '
                    + acc.open_positions.map(function (p) {
                        const cls = parseFloat(p.profit) >= 0 ? 'text-green' : 'text-red';
                        return p.symbol + ' ' + p.type.toUpperCase()
                             + ' <span class="' + cls + '">' + parseFloat(p.profit).toFixed(2) + '</span>';
                    }).join(' · ');
                posEl.hidden = false;
            }
        });
        showMessage('success', 'MyFxBook geladen – bitte prüfen und Speichern.');
        btn.disabled = false; btn.textContent = '↺ MyFxBook laden';
    });

    // ── Tabelle neu laden ─────────────────────────────────────────────────────
    async function reloadTable() {
        try {
            const res  = await fetch(BASE + 'get_entries.php?limit=10');
            const data = await res.json();
            if (!data.entries) return;
            const tbody = document.querySelector('#trading-table tbody');
            if (data.entries.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="empty-state">Noch keine Einträge vorhanden.</td></tr>';
                return;
            }
            tbody.innerHTML = data.entries.map(function (r) {
                return '<tr'
                    + ' data-id="'               + r.id + '"'
                    + ' data-date="'             + r.entry_date + '"'
                    + ' data-main-return="'      + (r.main_account_return      ?? '') + '"'
                    + ' data-ea-return="'        + (r.ea_account_return        ?? '') + '"'
                    + ' data-challenge-return="' + (r.challenge_account_return ?? '') + '"'
                    + ' data-main-profit="'      + (r.main_account_profit      ?? '') + '"'
                    + ' data-ea-profit="'        + (r.ea_account_profit        ?? '') + '"'
                    + ' data-challenge-profit="' + (r.challenge_account_profit ?? '') + '">'
                    + '<td>' + formatDate(r.entry_date) + '</td>'
                    + '<td>Tag ' + r.trading_day + '</td>'
                    + '<td>' + fmtReturn(r.main_account_return)      + '</td>'
                    + '<td class="text-muted">' + fmtMoney(r.main_account_profit)      + '</td>'
                    + '<td>' + fmtReturn(r.ea_account_return)        + '</td>'
                    + '<td class="text-muted">' + fmtMoney(r.ea_account_profit)        + '</td>'
                    + '<td>' + fmtReturn(r.challenge_account_return) + '</td>'
                    + '<td class="text-muted">' + fmtMoney(r.challenge_account_profit) + '</td>'
                    + '<td class="text-muted">' + formatDateTime(r.updated_at) + '</td>'
                    + '<td class="col-actions">'
                    + '<button class="btn btn-xs btn-ghost btn-edit-row" type="button">Bearbeiten</button> '
                    + '<button class="btn btn-xs btn-ok btn-create-image" type="button" data-id="' + r.id + '" data-date="' + r.entry_date + '">Grafik</button>'
                    + '</td>'
                    + '</tr>';
            }).join('');
        } catch (e) { /* silent */ }
    }

    // ── Stats neu laden ───────────────────────────────────────────────────────
    async function reloadStats() {
        try {
            const res  = await fetch(BASE + 'get_entries.php?stats=1');
            const data = await res.json();
            if (!data.stats) return;
            const s = data.stats;
            document.getElementById('trading-stats').innerHTML =
                statCard('Main Account',  s.main.all,      s.main.week)      +
                statCard('Monvesto EA',   s.ea.all,        s.ea.week)        +
                statCard('Road to 100k',  s.challenge.all, s.challenge.week);
        } catch (e) { /* silent */ }
    }

    function statCard(label, all, week) {
        const aCls = (all  ?? 0) >= 0 ? 'text-green' : 'text-red';
        const wCls = (week ?? 0) >= 0 ? 'text-green' : 'text-red';
        return '<div class="kpi-card">'
            + '<div class="kpi-label">' + label + '</div>'
            + '<div class="kpi-value--md ' + aCls + '">' + fmtReturnStat(all) + '</div>'
            + '<div class="kpi-sub">seit Start</div>'
            + '<div class="kpi-sub tr-week" style="margin-top:4px">Woche: <strong class="' + wCls + '">' + fmtReturnStat(week) + '</strong></div>'
            + '</div>';
    }

    // ── Hilfsfunktionen ───────────────────────────────────────────────────────
    function formatDate(str) {
        if (!str) return '';
        const [y, m, d] = str.split('-');
        return d + '.' + m + '.' + y;
    }
    function formatDateTime(str) {
        if (!str) return '';
        const dt = new Date(str);
        return dt.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit' }) + ' '
             + dt.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
    }
    function fmtReturn(val) {
        if (val === null || val === '' || val === undefined) return '<span class="text-muted">–</span>';
        const n = parseFloat(val);
        return '<span class="' + (n >= 0 ? 'text-green' : 'text-red') + ' fw-700">'
             + (n >= 0 ? '+' : '') + n.toFixed(2) + '%</span>';
    }
    function fmtMoney(val) {
        if (val === null || val === '' || val === undefined) return '–';
        const n = parseFloat(val);
        return (n >= 0 ? '+' : '') + n.toFixed(2);
    }
    function fmtReturnStat(val) {
        if (val === null || val === undefined) return '–';
        const n = parseFloat(val);
        return (n >= 0 ? '+' : '') + n.toFixed(2) + '%';
    }

})();