// resources/js/components/html_editor_component.js
// prilagodi putanju do __i po svom projektu
import FormInputComponent from "./form_input_component.js";
import __i from "../../vendor/repeat-toolkit/i18n.js"; // ako je fajl dublje: "../../vendor/repeat-toolkit/i18n.js"

export default class HtmlEditor extends FormInputComponent {
    constructor() {
        super();

        Object.assign(this.state, {
            element_placeholder: this.getAttribute('element_placeholder') ?? this.state.element_placeholder ?? "",
            element_value: this.getAttribute('element_value') ?? this.state.element_value ?? "",
        });

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

        // refs
        this.$toolbar = null;
        this.$content = null;
        this.$count   = null;
        this.$hidden  = null;
        this.$formatBtn = null;
        this.$formatMenu = null;
        this._ddOpen = false;
    }

    // --- API kao kod inputa ---
    get value() {
        return this.$content ? this.$content.innerHTML : (this.state.element_value ?? "");
    }
    set value(html) {
        this.state.element_value = html || "";
        if (this.$content) {
            this.$content.innerHTML = this.state.element_value;
            this.syncFormValue();
            this.updateCounter();
        }
    }
    focus() { this.$content?.focus(); }

    // --- Render ---
    render() {
        // Ne zovemo super.render()
        const req = this.renderRequired(); // iz FormInputComponent
        const idAttr = this.state.element_id ? `id="${this.state.element_id}"` : "";
        const wrapClass = `he-wrap ${this.state.element_class ?? ""}`.trim();
        const styleAttr = this.state.element_style ? ` style="${this.state.element_style}"` : "";

        this.innerHTML = `
            <style>
                .he-editor { border:1px solid #dfe3e6; border-radius:12px; background:#fff; overflow:hidden; }
                .he-toolbar { display:flex; flex-wrap:wrap; gap:6px; align-items:center; padding:8px; border-bottom:1px solid #eef1f3; background:#fafbfc; position: relative; }
                .he-toolbar button {
                    font-size:14px; border:1px solid #d9dee3; background:#fff; padding:6px 8px; border-radius:8px; cursor:pointer; line-height:1;
                }
                .he-toolbar button:hover { background:#f3f5f7; }
                .he-split { width:1px; height:24px; background:#e4e7ea; margin:0 6px; }
                .he-content { min-height:160px; padding:12px 14px; outline:none; }
                .he-content:empty:before { content: attr(data-placeholder); color:#9aa4ad; pointer-events:none; }
                .he-status { display:flex; justify-content:space-between; padding:6px 10px; font-size:12px; color:#6b7785; border-top:1px solid #eef1f3; background:#fafbfc; }

                /* Custom dropdown */
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

                /* Source dialog overlay (injected markup styles) */
                .he-source-overlay {
                    position: fixed; inset: 0;
                    background: rgba(0,0,0,.6);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                }
                .he-source-overlay.open { display:flex; }
                .he-source-modal {
                    background: #fff;
                    border-radius: 10px;
                    width: 90%;
                    max-width: 900px;
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    box-shadow: 0 25px 60px rgba(0,0,0,.25);
                }
                .he-source-header {
                    padding: 10px 14px;
                    border-bottom: 1px solid #ddd;
                    background: #f7f7f7;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .he-source-header h5 { margin:0; font-size:16px; }
                .he-source-body { padding:0; }
                .he-source-body textarea {
                    width:100%;
                    height:400px;
                    border:none;
                    resize:none;
                    padding:12px;
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                    font-size:14px;
                    outline:none;
                }
                .he-source-footer {
                    border-top:1px solid #ddd;
                    padding:8px 14px;
                    background:#f7f7f7;
                    display:flex;
                    justify-content:end;
                    gap:8px;
                }
                .he-source-footer button {
                    padding:6px 12px;
                    border-radius:6px;
                    border:1px solid #ccc;
                    cursor:pointer;
                    background:#fff;
                }
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
                        <!-- CUSTOM DROPDOWN UMESTO <select> -->
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

                        <button type="button" data-cmd="bold" aria-label="${__i("Bold")}"
                                form="__he__" data-no-validate="1"><b>${__i("B")}</b></button>
                        <button type="button" data-cmd="italic" aria-label="${__i("Italic")}"
                                form="__he__" data-no-validate="1"><i>${__i("I")}</i></button>
                        <button type="button" data-cmd="underline" aria-label="${__i("Underline")}"
                                form="__he__" data-no-validate="1"><u>${__i("U")}</u></button>

                        <div class="he-split" aria-hidden="true"></div>

                        <button type="button" data-cmd="insertUnorderedList" aria-label="${__i("Bullets")}"
                                form="__he__" data-no-validate="1">• ${__i("Lista")}</button>
                        <button type="button" data-cmd="insertOrderedList" aria-label="${__i("Numbers")}"
                                form="__he__" data-no-validate="1">1. ${__i("Lista")}</button>

                        <div class="he-split" aria-hidden="true"></div>

                        <button type="button" data-cmd="justifyLeft" aria-label="${__i("Left")}"
                                form="__he__" data-no-validate="1">⟸</button>
                        <button type="button" data-cmd="justifyCenter" aria-label="${__i("Center")}"
                                form="__he__" data-no-validate="1">⇔</button>
                        <button type="button" data-cmd="justifyRight" aria-label="${__i("Right")}"
                                form="__he__" data-no-validate="1">⟹</button>

                        <div class="he-split" aria-hidden="true"></div>

                        <button type="button" data-action="link" aria-label="${__i("Link")}"
                                form="__he__" data-no-validate="1">🔗</button>
                        <button type="button" data-action="image" aria-label="${__i("Image")}"
                                form="__he__" data-no-validate="1">🖼️</button>

                        <div class="he-split" aria-hidden="true"></div>

                        <button type="button" data-cmd="undo" aria-label="${__i("Undo")}"
                                form="__he__" data-no-validate="1">↶</button>
                        <button type="button" data-cmd="redo" aria-label="${__i("Redo")}"
                                form="__he__" data-no-validate="1">↷</button>
                        <button type="button" data-action="clear" aria-label="${__i("Clear formatting")}"
                                form="__he__" data-no-validate="1">⌫</button>

                        <div class="he-split" aria-hidden="true"></div>

                        <!-- NOVO: Source (HTML) dugme -->
                        <button type="button" data-action="source" aria-label="${__i("HTML izvor")}"
                                form="__he__" data-no-validate="1">🧩 HTML</button>
                    </div>

                    <div class="he-content" contenteditable="true"
                         role="textbox" aria-multiline="true"
                         data-placeholder="${this.state.element_placeholder || ''}"></div>

                    <div class="he-status">
                        <span class="he-msg">${__i("Spremno")}</span>
                        <span class="he-count">0 ${__i("zn.")}</span>
                    </div>
                </div>

                <!-- Jedino ovo polje ulazi u FormData -->
                <input type="hidden" name="${this.state.element_name}" value="${this.state.element_value ?? ''}">
            </div>
        `;

        // refs
        this.$toolbar = this.querySelector('.he-toolbar');
        this.$content = this.querySelector('.he-content');
        this.$count   = this.querySelector('.he-count');
        this.$hidden  = this.querySelector('input[type="hidden"][name]');
        this.$formatBtn  = this.querySelector('.he-dd-toggle');
        this.$formatMenu = this.querySelector('.he-dd-menu');

        // init vrednost
        if (this.state.element_value) this.$content.innerHTML = this.state.element_value;
        this.syncFormValue();
        this.updateCounter();

        // listeners
        this.attachListeners();

        // zvezdica za required (isti UX kao kod FormInputComponent)
        super.afterRender?.();
    }

    attachListeners() {
        // Toolbar
        this.$toolbar.addEventListener('click', this.onToolbarClick);
        // Ne daj globalnim handlerima da „pogase” otvoren meni / uzmu fokus
        this.$toolbar.addEventListener('pointerdown', (e) => {
            if (e.target.closest('button') || e.target.closest('.he-dd')) {
                e.stopPropagation();
            }
        }, { capture: true });

        // Custom dropdown
        this.$formatBtn.addEventListener('click', this.toggleFormatDD);
        this.$formatMenu.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-format]');
            if (!btn) return;
            this.applyFormat(btn.getAttribute('data-format'));
        });

        // Zatvaranje dropdowna klikom van
        document.addEventListener('pointerdown', this.handleGlobalPointer);

        // Content
        this.$content.addEventListener('input', this.onInput);
        this.$content.addEventListener('paste', this.onPaste);
        this.$content.addEventListener('drop', this.onDrop);
    }

    disconnectedCallback() {
        this.$toolbar?.removeEventListener('click', this.onToolbarClick);
        document.removeEventListener('pointerdown', this.handleGlobalPointer);
        this.$content?.removeEventListener('input', this.onInput);
        this.$content?.removeEventListener('paste', this.onPaste);
        this.$content?.removeEventListener('drop', this.onDrop);
    }

    connectedCallback() {
        this.render();
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
        if (e.target.closest('.he-dd')) return; // klik unutar menija
        const dd = this.$formatBtn?.closest('.he-dd');
        dd?.classList.remove('open');
        this._ddOpen = false;
        this.$formatBtn?.setAttribute('aria-expanded', 'false');
    }

    applyFormat(blockTag) {
        document.execCommand('formatBlock', false, blockTag);
        this.$content.focus();
        this.onInput();
        // zatvori meni
        const dd = this.$formatBtn.closest('.he-dd');
        dd.classList.remove('open');
        this._ddOpen = false;
        this.$formatBtn.setAttribute('aria-expanded', 'false');
    }

    // --- Toolbar ---
    onToolbarClick(e) {
        const el = e.target.closest('button');
        if (!el || el.closest('.he-dd')) return; // custom dropdown rukujemo posebno

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

    onDrop(e) {
        e.preventDefault(); // nema direktnog drop-a
    }

    // --- Source (HTML) Editor ---
    openSourceDialog() {
        // overlay
        const overlay = document.createElement('div');
        overlay.className = 'he-source-overlay open';
        overlay.innerHTML = `
            <div class="he-source-modal">
                <div class="he-source-header">
                    <h5>${__i("HTML kod")}</h5>
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
            this.value = clean;       // setuje i hidden i counter kroz set value + onInput()
            this.onInput();
            close();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) close();
        });

        // Escape za zatvaranje
        const onKey = (ev) => { if (ev.key === 'Escape') { close(); document.removeEventListener('keydown', onKey); } };
        document.addEventListener('keydown', onKey);
    }

    // --- Helpers ---
    syncFormValue() {
        const html = this.$content.innerHTML;
        this.$hidden.value = html;
        this.state.element_value = html;
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
