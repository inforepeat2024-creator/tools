// resources/js/components/html_editor_component.js
import FormInputComponent from "./form_input_component.js";
import __i from "../../vendor/repeat-toolkit/i18n.js";
import TextHelper from "../../helpers/text_helper.js";

export default class HtmlEditor extends FormInputComponent {
    constructor() {
        super();

        // [i18n+] helper za parsiranje JSON iz atributa (bez lomljenja zbog HTML-a)
        const jsonOrNull = (str) => {
            if (!str) return null;
            try { return JSON.parse(TextHelper.decodeFromAttr(str)); } catch(e){ return null; }
        };




// [i18n+] jezici iz <html-editor locales="sr,en"> ili iz window.languages
        let localesFromWindow = [];
        if (typeof window !== 'undefined' && window.languages) {
            // može biti objekat { sr:1, en:2 } ili string "{"sr":1,"en":2}"
            const wl = window.languages;
            if (typeof wl === 'string') {
                try { localesFromWindow = Object.keys(JSON.parse(wl)); } catch {}
            } else if (typeof wl === 'object') {
                localesFromWindow = Object.keys(wl);
            }
        }
        const localesFromAttr = (this.getAttribute('locales') || '')
            .split(',')
            .map(s => s.trim())
            .filter(Boolean);

        const locales = localesFromAttr.length ? localesFromAttr : localesFromWindow;

// [i18n+] vrednosti po jezicima
// PRVO probaj element_values, ALI PRIHVATI i element_value kao JSON mapu (tako si poslao iz forme)
        const valuesFromElementValues = jsonOrNull(this.getAttribute('element_values')) || {};
        const valuesFromElementValue  = jsonOrNull(this.getAttribute('element_value')) || {};
        const initialValues = Object.keys(valuesFromElementValues).length
            ? valuesFromElementValues
            : valuesFromElementValue;

// single-value fallback (ako element_value NIJE JSON mapa, nego plain string)
        const singleValueDecoded = TextHelper.decodeFromAttr(
            this.getAttribute('element_value') ?? this.state.element_value ?? ""
        );

// aktivni jezik (prihvati i alias element_lang)
        const currentLocaleAttr =
            this.getAttribute('current_locale') ||
            this.getAttribute('element_lang') ||
            (typeof window !== 'undefined' && window.APP_LOCALE) ||
            (locales[0] || 'sr');

// translated flag – true ako je atribut "translated" (1/true/empty) ILI ako smo dobili mapu vrednosti
        const translatedAttr = this.getAttribute('translated');
        const translated =
            translatedAttr === '' || translatedAttr === '1' || translatedAttr === 'true' ||
            Object.keys(initialValues).length > 0;

// formiraj početne vrednosti: ako nema mape, upiši singleValue u currentLocale
        const startValues = { ...(initialValues || {}) };
        if (!Object.keys(startValues).length && singleValueDecoded) {
            startValues[currentLocaleAttr] = singleValueDecoded;
        }

// ako nema eksplicitne liste jezika, izvedi je iz ključeva mape
        const finalLocales = locales.length ? locales : Object.keys(startValues || {});

// base state + i18n state
        Object.assign(this.state, {
            element_placeholder: this.getAttribute('element_placeholder') ?? this.state.element_placeholder ?? "",
            element_value: singleValueDecoded, // čuvamo i dalje, ali radimo sa state.values
            translated,
            locales: finalLocales,
            current_locale: currentLocaleAttr,
            values: startValues, // mapa locale => html
        });

// Ako ipak nije translated, drži single-value režim u current_locale
        if (!this.state.translated && !this.state.values[this.state.current_locale]) {
            this.state.values[this.state.current_locale] = this.state.element_value || "";
        }

        // Ako nije translated, držimo se single-value režima
        if (!this.state.translated && !this.state.values[this.state.current_locale]) {
            this.state.values[this.state.current_locale] = this.state.element_value || "";
        }

        // bind
        this.onToolbarClick = this.onToolbarClick.bind(this);
        this.onInput        = this.onInput.bind(this);
        this.onPaste        = this.onPaste.bind(this);
        this.onDrop         = this.onDrop.bind(this);
        this.syncFormValue  = this.syncFormValue.bind(this);
        this.toggleFormatDD = this.toggleFormatDD.bind(this);
        this.applyFormat    = this.applyFormat.bind(this);
        this.handleGlobalPointer = this.handleGlobalPointer.bind(this);
        this.openSourceDialog = this.openSourceDialog.bind(this);
        // [i18n+]
        this.onLangChange   = this.onLangChange.bind(this);

        // refs
        this.$toolbar = null;
        this.$content = null;
        this.$count   = null;
        this.$formatBtn = null;
        this.$formatMenu = null;
        this.$hiddenInputsWrap = null; // [i18n+]
        this.$langSelect = null;       // [i18n+]
        this._ddOpen = false;
    }

    // --- API ---
    get value() {
        const loc = this.state.current_locale;
        return (this.$content ? this.$content.innerHTML : (this.state.values?.[loc] ?? "")) || "";
    }
    set value(html) {
        const loc = this.state.current_locale;
        this.state.values[loc] = html || "";
        if (this.$content) {
            this.$content.innerHTML = this.state.values[loc];
            this.syncFormValue();
            this.updateCounter();
        }
    }

    // [i18n+] dodatni API
    valueBy(locale) { return this.state.values?.[locale] ?? ""; }
    setValueBy(locale, html) {
        this.state.values[locale] = html || "";
        if (locale === this.state.current_locale && this.$content) {
            this.$content.innerHTML = this.state.values[locale];
            this.syncFormValue();
            this.updateCounter();
        } else {
            this.syncFormValue(); // update hidden polja
        }
    }
    allValues() { return { ...(this.state.values || {}) }; }

    focus() { this.$content?.focus(); }

    // --- Render ---
    render() {
        const req = this.renderRequired();
        const idAttr = this.state.element_id ? `id="${this.state.element_id}"` : "";
        const wrapClass = `he-wrap ${this.state.element_class ?? ""}`.trim();
        const styleAttr = this.state.element_style ? ` style="${this.state.element_style}"` : "";

        // [i18n+] jezički selektor (ako je translated)
        const langSwitcher = this.state.translated
            ? `
                <div class="he-lang">
                    <label class="he-lang-label">${__i('Jezik')}:</label>
                    <select class="he-lang-select" form="__he__" data-no-validate="1" aria-label="${__i('Promeni jezik')}">
                        ${ (this.state.locales || [this.state.current_locale]).map(lc => {
                const sel = lc === this.state.current_locale ? 'selected' : '';
                return `<option value="${lc}" ${sel}>${lc.toUpperCase()}</option>`;
            }).join('') }
                    </select>
                </div>
              `
            : '';

        this.innerHTML = `
            <style>
                .he-editor { border:1px solid #dfe3e6; border-radius:12px; background:#fff; overflow:hidden; }
                .he-toolbar { display:flex; flex-wrap:wrap; gap:6px; align-items:center; padding:8px; border-bottom:1px solid #eef1f3; background:#fafbfc; position: relative; }
                .he-toolbar-left { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
                .he-toolbar-right { margin-left:auto; display:flex; gap:6px; align-items:center; flex-wrap:wrap; }

                .he-toolbar button {
                    font-size:14px; border:1px solid #d9dee3; background:#fff; padding:6px 8px; border-radius:8px; cursor:pointer; line-height:1;
                }
                .he-toolbar button:hover { background:#f3f5f7; }
                .he-split { width:1px; height:24px; background:#e4e7ea; margin:0 6px; }
                .he-content { min-height:160px; padding:12px 14px; outline:none; }
                .he-content:empty:before { content: attr(data-placeholder); color:#9aa4ad; pointer-events:none; }
                .he-status { display:flex; justify-content:space-between; padding:6px 10px; font-size:12px; color:#6b7785; border-top:1px solid #eef1f3; background:#fafbfc; }

                /* Dropdown */
                .he-dd { position: relative; }
                .he-dd-menu {
                    position: absolute;
                    top: calc(100% + 6px);
                    left: 0;
                    min-width: 160px;
                    border: 1px solid #d9dee3;
                    border-radius: 8px;
                    background: #fff;
                    box-shadow: 0 10px 25px rgba(0,0,0,.08);
                    padding: 6px;
                    display: none;
                    z-index: 1000;
                }
                .he-dd.open .he-dd-menu { display: block; }
                .he-dd-menu button {
                    display: block;
                    width: 100%;
                    text-align: left;
                    margin: 0;
                    border: none;
                    background: transparent;
                    padding: 6px 8px;
                    border-radius: 6px;
                }
                .he-dd-menu button:hover { background: #f3f5f7; }

                /* Lang switcher */
                .he-lang { display:flex; align-items:center; gap:6px; margin-right:8px; }
                .he-lang-select { border:1px solid #d9dee3; border-radius:8px; padding:6px 8px; background:#fff; }
                .he-lang-label { font-size:13px; color:#6b7785; }

                /* Source dialog */
                .he-source-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.6); display: none; align-items: center; justify-content: center; z-index: 9999; }
                .he-source-overlay.open { display:flex; }
                .he-source-modal { background: #fff; border-radius: 10px; width: 90%; max-width: 900px; display:flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,.25); }
                .he-source-header { padding: 10px 14px; border-bottom: 1px solid #ddd; background: #f7f7f7; display:flex; justify-content:space-between; align-items:center; }
                .he-source-header h5 { margin:0; font-size:16px; }
                .he-source-body { padding:0; }
                .he-source-body textarea { width:100%; height:400px; border:none; resize:none; padding:12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size:14px; outline:none; }
                .he-source-footer { border-top:1px solid #ddd; padding:8px 14px; background:#f7f7f7; display:flex; justify-content:end; gap:8px; }
                .he-source-footer button { padding:6px 12px; border-radius:6px; border:1px solid #ccc; cursor:pointer; background:#fff; }
                .he-source-footer button:hover { background:#f0f0f0; }

                @media (prefers-color-scheme: dark) {
                    .he-editor { background:#111418; border-color:#2a2f36; }
                    .he-toolbar { background:#0f1317; border-bottom-color:#1f242b; }
                    .he-toolbar button { background:#141a20; color:#e6eaef; border-color:#2a2f36; }
                    .he-toolbar button:hover { background:#1b222a; }
                    .he-content { color:#e6eaef; }
                    .he-status { background:#0f1317; border-top-color:#1f242b; color:#9aa4ad; }
                    .he-dd-menu { background:#141a20; border-color:#2a2f36; }
                    .he-dd-menu button:hover { background:#1b222a; }
                    .he-lang-select { background:#141a20; color:#e6eaef; border-color:#2a2f36; }

                    .he-source-modal { background:#111418; color:#e6eaef; }
                    .he-source-header, .he-source-footer { background:#0f1317; border-color:#2a2f36; }
                    .he-source-body textarea { background:#141a20; color:#e6eaef; }
                    .he-source-footer button { background:#141a20; border-color:#2a2f36; color:#e6eaef; }
                }
            </style>

            <div class="form-group ${wrapClass}" ${styleAttr}>
                ${this.getLabel()}
                <div class="he-editor" ${idAttr} ${req}>
                    <div class="he-toolbar" role="toolbar" data-no-validate="1">
                        <div class="he-toolbar-left" data-no-validate="1">
                            ${langSwitcher}
                            <div class="he-dd" data-no-validate="1">
                                <button type="button" class="he-dd-toggle" aria-haspopup="true" aria-expanded="false"
                                        form="__he__" data-no-validate="1">${__i("Format")} ▾</button>
                                <div class="he-dd-menu" role="menu">
                                    <button type="button" data-format="P"  form="__he__" data-no-validate="1">${__i("Paragraf")}</button>
                                    <button type="button" data-format="H1" form="__he__" data-no-validate="1">${__i("Naslov 1")}</button>
                                    <button type="button" data-format="H2" form="__he__" data-no-validate="1">${__i("Naslov 2")}</button>
                                    <button type="button" data-format="H3" form="__he__" data-no-validate="1">${__i("Naslov 3")}</button>
                                </div>
                            </div>

                            <button type="button" data-cmd="bold" aria-label="${__i("Bold")}" form="__he__" data-no-validate="1"><b>${__i("B")}</b></button>
                            <button type="button" data-cmd="italic" aria-label="${__i("Italic")}" form="__he__" data-no-validate="1"><i>${__i("I")}</i></button>
                            <button type="button" data-cmd="underline" aria-label="${__i("Underline")}" form="__he__" data-no-validate="1"><u>${__i("U")}</u></button>

                            <div class="he-split" aria-hidden="true"></div>

                            <button type="button" data-cmd="insertUnorderedList" aria-label="${__i("Bullets")}" form="__he__" data-no-validate="1">• ${__i("Lista")}</button>
                            <button type="button" data-cmd="insertOrderedList" aria-label="${__i("Numbers")}" form="__he__" data-no-validate="1">1. ${__i("Lista")}</button>

                            <div class="he-split" aria-hidden="true"></div>

                            <button type="button" data-cmd="justifyLeft" aria-label="${__i("Left")}" form="__he__" data-no-validate="1">⟸</button>
                            <button type="button" data-cmd="justifyCenter" aria-label="${__i("Center")}" form="__he__" data-no-validate="1">⇔</button>
                            <button type="button" data-cmd="justifyRight" aria-label="${__i("Right")}" form="__he__" data-no-validate="1">⟹</button>

                            <div class="he-split" aria-hidden="true"></div>

                            <button type="button" data-action="link" aria-label="${__i("Link")}" form="__he__" data-no-validate="1">🔗</button>
                            <button type="button" data-action="image" aria-label="${__i("Image")}" form="__he__" data-no-validate="1">🖼️</button>
                        </div>

                        <div class="he-toolbar-right" data-no-validate="1">
                            <button type="button" data-cmd="undo" aria-label="${__i("Undo")}" form="__he__" data-no-validate="1">↶</button>
                            <button type="button" data-cmd="redo" aria-label="${__i("Redo")}" form="__he__" data-no-validate="1">↷</button>
                            <button type="button" data-action="clear" aria-label="${__i("Clear formatting")}" form="__he__" data-no-validate="1">⌫</button>
                            <div class="he-split" aria-hidden="true"></div>
                            <button type="button" data-action="source" aria-label="${__i("HTML izvor")}" form="__he__" data-no-validate="1">🧩 HTML</button>
                        </div>
                    </div>

                    <div class="he-content" contenteditable="true" role="textbox" aria-multiline="true"
                         data-placeholder="${this.state.element_placeholder || ''}"></div>

                    <div class="he-status">
                        <span class="he-msg">${__i("Spremno")}</span>
                        <span class="he-count">0 ${__i("zn.")}</span>
                    </div>
                </div>

                <!-- [i18n+] Hidden inputs: jedan ili više -->
                <div class="he-hidden-inputs d-none"></div>
            </div>
        `;

        // refs
        this.$toolbar = this.querySelector('.he-toolbar');
        this.$content = this.querySelector('.he-content');
        this.$count   = this.querySelector('.he-count');
        this.$formatBtn  = this.querySelector('.he-dd-toggle');
        this.$formatMenu = this.querySelector('.he-dd-menu');
        this.$hiddenInputsWrap = this.querySelector('.he-hidden-inputs');
        this.$langSelect = this.querySelector('.he-lang-select');

        // init content
        const current = this.state.current_locale;
        const startHtml =
            (this.state.values && this.state.values[current] != null)
                ? this.state.values[current]
                : (this.state.element_value || "");
        this.$content.innerHTML = startHtml;

        // hidden inputs initial
        this.renderHiddenInputs();
        this.syncFormValue();
        this.updateCounter();

        // listeners
        this.attachListeners();

        super.afterRender?.();
    }

    renderHiddenInputs() {
        const name = this.state.element_name || 'content';
        if (!this.state.translated) {
            // single
            this.$hiddenInputsWrap.innerHTML = `
                <textarea name="${name}" class="d-none">${this.state.values[this.state.current_locale] ?? ""}</textarea>
            `;
            return;
        }
        // multi: name[locale]
        const locales = this.state.locales && this.state.locales.length ? this.state.locales : [this.state.current_locale];
        this.$hiddenInputsWrap.innerHTML = locales.map(loc => {
            const v = this.state.values?.[loc] ?? "";
            return `<textarea name="${name}[${loc}]" data-locale="${loc}" class="d-none">${v}</textarea>`;
        }).join('');
    }

    attachListeners() {
        this.$toolbar.addEventListener('click', this.onToolbarClick);
        this.$toolbar.addEventListener('pointerdown', (e) => {
            if (e.target.closest('button') || e.target.closest('.he-dd')) e.stopPropagation();
        }, { capture: true });

        this.$formatBtn?.addEventListener('click', this.toggleFormatDD);
        this.$formatMenu?.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-format]');
            if (!btn) return;
            this.applyFormat(btn.getAttribute('data-format'));
        });

        document.addEventListener('pointerdown', this.handleGlobalPointer);

        this.$content.addEventListener('input', this.onInput);
        this.$content.addEventListener('paste', this.onPaste);
        this.$content.addEventListener('drop', this.onDrop);

        // [i18n+]
        if (this.$langSelect) this.$langSelect.addEventListener('change', this.onLangChange);
    }

    disconnectedCallback() {
        this.$toolbar?.removeEventListener('click', this.onToolbarClick);
        document.removeEventListener('pointerdown', this.handleGlobalPointer);
        this.$content?.removeEventListener('input', this.onInput);
        this.$content?.removeEventListener('paste', this.onPaste);
        this.$content?.removeEventListener('drop', this.onDrop);
        this.$langSelect?.removeEventListener('change', this.onLangChange);
    }

    connectedCallback() {
        this.render();
    }

    // --- Lang switching [i18n+] ---
    onLangChange(e) {
        // 1) Snimi trenutno stanje u values[current]
        const curr = this.state.current_locale;
        this.state.values[curr] = this.$content.innerHTML || "";

        // 2) Promeni current_locale
        const next = e.target.value;
        this.state.current_locale = next;

        // 3) Učitaj HTML za novi jezik
        const html = this.state.values[next] || "";
        this.$content.innerHTML = html;

        // 4) Sync hidden inputs + counter
        this.syncFormValue();
        this.updateCounter();

        // 5) Emit event (opciono)
        this.dispatchEvent(new CustomEvent('lang-change', { detail: { locale: next } }));
    }

    // --- Custom dropdown logic ---
    toggleFormatDD() {
        const dd = this.$formatBtn.closest('.he-dd');
        this._ddOpen = !this._ddOpen;
        dd.classList.toggle('open', this._ddOpen);
        this.$formatBtn.setAttribute('aria-expanded', this._ddOpen ? 'true' : 'false');
    }

    handleGlobalPointer(e) {
        if (!this._ddOpen) return;
        if (e.target.closest('.he-dd')) return;
        const dd = this.$formatBtn?.closest('.he-dd');
        dd?.classList.remove('open');
        this._ddOpen = false;
        this.$formatBtn?.setAttribute('aria-expanded', 'false');
    }

    applyFormat(blockTag) {
        document.execCommand('formatBlock', false, blockTag);
        this.$content.focus();
        this.onInput();
        const dd = this.$formatBtn.closest('.he-dd');
        dd.classList.remove('open');
        this._ddOpen = false;
        this.$formatBtn.setAttribute('aria-expanded', 'false');
    }

    // --- Toolbar ---
    onToolbarClick(e) {
        const el = e.target.closest('button');
        if (!el || el.closest('.he-dd')) return;

        const cmd = el.getAttribute('data-cmd');
        const action = el.getAttribute('data-action');

        if (cmd) {
            document.execCommand(cmd, false, null);
            this.$content.focus();
            this.onInput();
            return;
        }

        if (action === 'link') {
            const url = prompt(__i('URL? (https://)'));
            if (url) document.execCommand('createLink', false, url);
            this.onInput();
            return;
        }

        if (action === 'image') {
            const url = prompt(__i('URL slike?'));
            if (url) document.execCommand('insertImage', false, url);
            this.onInput();
            return;
        }

        if (action === 'clear') {
            document.execCommand('removeFormat', false, null);
            this.onInput();
            return;
        }

        if (action === 'source') {
            this.openSourceDialog();
            return;
        }
    }

    // --- Input ---
    onInput() {
        this.sanitizeInline();
        this.syncFormValue();
        this.updateCounter();
        this.dispatchEvent(new Event('input', { bubbles: true }));
    }

    onPaste(e) {
        e.preventDefault();
        const html = (e.clipboardData || window.clipboardData).getData('text/html');
        const text = (e.clipboardData || window.clipboardData).getData('text/plain');
        const toInsert = html || (text ? `<p>${this.escapeHtml(text)}</p>` : '');
        const clean = this.sanitize(toInsert);
        document.execCommand('insertHTML', false, clean);
    }

    onDrop(e) { e.preventDefault(); }

    // --- Source (HTML) Editor ---
    openSourceDialog() {
        const overlay = document.createElement('div');
        overlay.className = 'he-source-overlay open';
        overlay.innerHTML = `
            <div class="he-source-modal">
                <div class="he-source-header">
                    <h5>${__i("HTML kod")} — ${this.state.current_locale.toUpperCase()}</h5>
                    <button class="btn-close" aria-label="${__i("Zatvori")}">✖</button>
                </div>
                <div class="he-source-body">
                    <textarea spellcheck="false"></textarea>
                </div>
                <div class="he-source-footer">
                    <button class="btn-cancel">${__i("Otkaži")}</button>
                    <button class="btn-apply btn-primary">${__i("Primeni")}</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const textarea = overlay.querySelector('textarea');
        const btnClose = overlay.querySelector('.btn-close');
        const btnCancel = overlay.querySelector('.btn-cancel');
        const btnApply = overlay.querySelector('.btn-apply');
        textarea.value = this.value;

        const close = () => overlay.remove();

        btnClose.addEventListener('click', close);
        btnCancel.addEventListener('click', close);
        btnApply.addEventListener('click', () => {
            const html = textarea.value;
            const clean = this.sanitize(html);
            this.value = clean;
            this.onInput();
            close();
        });
        overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
        const onKey = (ev) => { if (ev.key === 'Escape') { close(); document.removeEventListener('keydown', onKey); } };
        document.addEventListener('keydown', onKey);
    }

    // --- Helpers ---
    syncFormValue() {
        const loc = this.state.current_locale;
        const html = this.$content.innerHTML || "";
        // upiši u state
        this.state.values[loc] = html;

        // upiši u odgovarajući hidden input(e)
        if (!this.state.translated) {
            const ta = this.$hiddenInputsWrap.querySelector('textarea[name]');
            if (ta) ta.value = html;
            this.state.element_value = html;
            return;
        }

        // multi-locale
        // osveži/kreiraj textarea za trenutni jezik
        let ta = this.$hiddenInputsWrap.querySelector(`textarea[data-locale="${loc}"]`);
        if (!ta) {
            // ako je korisnik naknadno dodao jezik
            const name = this.state.element_name || 'content';
            ta = document.createElement('textarea');
            ta.className = 'd-none';
            ta.setAttribute('name', `${name}[${loc}]`);
            ta.setAttribute('data-locale', loc);
            this.$hiddenInputsWrap.appendChild(ta);
        }
        ta.value = html;
    }

    updateCounter() {
        const text = this.$content.textContent || '';
        this.$count.textContent = `${text.length} ${__i("zn.")}`;
    }

    sanitizeInline() {
        this.$content.querySelectorAll('[style]').forEach(n => n.removeAttribute('style'));
    }

    sanitize(html) {
        if (window.DOMPurify) {
            return window.DOMPurify.sanitize(html, {
                ALLOWED_TAGS: ['p','br','b','i','u','strong','em','ul','ol','li','a','h1','h2','h3','blockquote','img'],
                ALLOWED_ATTR: { 'a': ['href','target','rel'], 'img': ['src','alt'] }
            });
        }
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        tmp.querySelectorAll('*').forEach(node => {
            const tag = node.tagName.toLowerCase();
            const ok = ['p','br','b','i','u','strong','em','ul','ol','li','a','h1','h2','h3','blockquote','img'].includes(tag);
            if (!ok) node.replaceWith(...node.childNodes);
            [...node.attributes].forEach(a => {
                if (!['href','target','rel','src','alt'].includes(a.name)) node.removeAttribute(a.name);
            });
        });
        return tmp.innerHTML;
    }

    escapeHtml(str) {
        return str.replace(/[&<>"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]));
    }
}

customElements.define('html-editor', HtmlEditor);
