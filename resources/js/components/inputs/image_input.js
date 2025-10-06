// Potpuno nezavisan od Tabler-a / Bootstrap-a
// <image_input element_name="logo" label="Dodaj logo" src="/img/clinic.png"></image_input>

import __i from "../../vendor/repeat-toolkit/i18n.js";
export default class ImageInput extends HTMLElement {
    constructor() {
        super();
        this.state = {
            previewUrl: this.getAttribute('src') || '',
            element_name: this.getAttribute('element_name') || 'image',
            label: this.getAttribute('label') || __i("Odaberi sliku"),
            accept: this.getAttribute('accept') || 'image/*',
            width: parseInt(this.getAttribute('width') || '160', 10),
            height: parseInt(this.getAttribute('height') || '160', 10),
            disabled: this.hasAttribute('disabled'),
        };

        this.handleFileChange = this.handleFileChange.bind(this);
        this.handleRemove = this.handleRemove.bind(this);
        this.handleClick = this.handleClick.bind(this);
        this.handleKey = this.handleKey.bind(this);
        this.handleDrop = this.handleDrop.bind(this);
        this.handleDrag = this.handleDrag.bind(this);
    }

    connectedCallback() {
        this.render();
    }

    // Public helpers
    getFile() {
        const input = this.querySelector('input[type=file]');
        return input?.files?.[0] || null;
    }
    reset() {
        const input = this.querySelector('input[type=file]');
        if (input) input.value = '';
        this.state.previewUrl = '';
        this.render();
    }

    handleFileChange(e) {
        const file = e.target.files?.[0];
        if (!file) return;

        // emit custom event
        this.dispatchEvent(new CustomEvent('image_input:select', { detail: { file } }));

        const reader = new FileReader();
        reader.onload = (evt) => {
            this.state.previewUrl = evt.target.result;
            this.render();
        };
        reader.readAsDataURL(file);
    }

    handleRemove() {
        if (this.state.disabled) return;
        const input = this.querySelector('input[type=file]');
        if (input) input.value = '';
        this.state.previewUrl = '';
        this.render();
        this.dispatchEvent(new CustomEvent('image_input:clear'));
    }

    handleClick() {
        if (this.state.disabled) return;
        this.querySelector('input[type=file]')?.click();
    }

    handleKey(e) {
        if (this.state.disabled) return;
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.handleClick();
        }
    }

    handleDrag(e) {
        e.preventDefault();
        e.stopPropagation();
        if (this.state.disabled) return;

        if (e.type === 'dragenter' || e.type === 'dragover') {
            this.classList.add('ii--dragover');
        } else if (e.type === 'dragleave') {
            this.classList.remove('ii--dragover');
        }
    }

    handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        if (this.state.disabled) return;

        this.classList.remove('ii--dragover');
        const dt = e.dataTransfer;
        const file = dt?.files?.[0];
        if (!file) return;

        // Set into input for form submit
        const input = this.querySelector('input[type=file]');
        if (input) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
        }
        this.handleFileChange({ target: { files: [file] } });
    }

    render() {
        const { previewUrl, element_name, label, accept, width, height, disabled } = this.state;

        this.innerHTML = `
            <style>
                :host { display: inline-block; }
                .ii-card {
                    --ii-bg: #fff;
                    --ii-bg-soft: #f8f9fa;
                    --ii-border: #e6e8eb;
                    --ii-border-strong: #cfd4da;
                    --ii-text-muted: #6b7280;
                    --ii-text: #111827;
                    --ii-accent: #2f6feb;
                    --ii-danger: #e03131;

                    border-radius: 0.75rem;
                    background: var(--ii-bg);
                    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
                    padding: 0.75rem 0.75rem 1rem;
                    text-align: center;
                    font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji";
                }
                .ii-drop {
                    position: relative;
                    width: ${width}px;
                    height: ${height}px;
                    margin: 0 auto;
                    border: 2px dashed var(--ii-border);
                    border-radius: 0.5rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: var(--ii-bg-soft);
                    cursor: ${disabled ? 'not-allowed' : 'pointer'};
                    overflow: hidden;
                    transition: border-color .15s ease, transform .08s ease;
                    outline: none;
                }
                .ii-drop:hover { border-color: var(--ii-border-strong); }
                .ii--dragover .ii-drop { border-color: var(--ii-accent); transform: scale(1.01); }
                .ii-preview {
                    width: 100%; height: 100%; object-fit: cover;
                }
                .ii-placeholder {
                    color: var(--ii-text-muted);
                    font-size: .95rem;
                    padding: .5rem .75rem;
                    line-height: 1.2;
                    user-select: none;
                }
                .ii-overlay {
                    position: absolute;
                    left: 0; right: 0; bottom: 0;
                    background: rgba(0,0,0,.55);
                    color: #fff;
                    font-size: .8rem;
                    padding: .35rem 0;
                }
                .ii-actions {
                    margin-top: .75rem;
                    display: flex;
                    gap: .5rem;
                    justify-content: center;
                }
                .ii-btn {
                    border: 1px solid var(--ii-border);
                    background: #fff;
                    border-radius: .5rem;
                    padding: .375rem .65rem;
                    font-size: .875rem;
                    line-height: 1.2;
                    cursor: pointer;
                    transition: background .12s ease, border-color .12s ease, color .12s ease, opacity .12s ease;
                    user-select: none;
                }
                .ii-btn:hover { background: var(--ii-bg-soft); border-color: var(--ii-border-strong); }
                .ii-btn:disabled { opacity: .6; cursor: not-allowed; }
                .ii-btn-danger {
                    color: var(--ii-danger); border-color: rgba(224,49,49,.35);
                    background: rgba(224,49,49,.04);
                }
                .ii-btn-danger:hover {
                    background: rgba(224,49,49,.08); border-color: rgba(224,49,49,.55);
                }
                input[type=file] { display: none; }

                /* Dark mode (auto) */
                @media (prefers-color-scheme: dark) {
                    .ii-card {
                        --ii-bg: #111418;
                        --ii-bg-soft: #151a21;
                        --ii-border: #2a2f37;
                        --ii-border-strong: #39414d;
                        --ii-text-muted: #9aa4b2;
                        --ii-text: #e5e7eb;
                        --ii-accent: #6ea8fe;
                        --ii-danger: #ff6b6b;
                        box-shadow: 0 10px 26px rgba(0,0,0,.45);
                    }
                    .ii-placeholder { color: var(--ii-text-muted); }
                }

                /* Responsivnost */
                @media (max-width: 480px) {
                    .ii-drop { width: min(${width}px, 80vw); height: min(${height}px, 80vw); }
                }
            </style>

            <div class="ii-card" aria-live="polite">
                <div class="ii-drop" role="button" tabindex="${disabled ? '-1':'0'}"
                    aria-label="${label}" aria-disabled="${disabled ? 'true':'false'}">
                    ${
            previewUrl
                ? `<img class="ii-preview" src="${previewUrl}" alt="Preview">`
                : `<div class="ii-placeholder">${label}<br><small>Click, drop ili paste</small></div>`
        }
                    <input type="file" name="${element_name}" accept="${accept}" ${disabled ? 'disabled':''}/>
                    ${ previewUrl ? `<div class="ii-overlay">Klikni da promeniš</div>` : '' }
                </div>

                <div class="ii-actions">
                    <button type="button" class="ii-btn" ${disabled ? 'disabled':''} data-action="choose">Odaberi</button>
                    ${ previewUrl ? `<button type="button" class="ii-btn ii-btn-danger" ${disabled ? 'disabled':''} data-action="remove">Ukloni</button>` : '' }
                </div>
            </div>
        `;

        // DOM refs
        const drop = this.querySelector('.ii-drop');
        const input = this.querySelector('input[type=file]');
        const chooseBtn = this.querySelector('[data-action="choose"]');
        const removeBtn = this.querySelector('[data-action="remove"]');

        // Click / Keyboard
        drop.addEventListener('click', this.handleClick);
        drop.addEventListener('keydown', this.handleKey);

        // File change
        input.addEventListener('change', this.handleFileChange);

        // Drag & drop
        ['dragenter','dragover'].forEach(evt => drop.addEventListener(evt, this.handleDrag));
        ['dragleave','drop'].forEach(evt => drop.addEventListener(evt, this.handleDrag));
        drop.addEventListener('drop', this.handleDrop);

        // Paste (npr. iz clipboard-a)
        drop.addEventListener('paste', (e) => {
            if (this.state.disabled) return;
            const item = [...(e.clipboardData?.items || [])].find(i => i.type.startsWith('image/'));
            if (!item) return;
            const file = item.getAsFile();
            if (!file) return;

            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            this.handleFileChange({ target: { files: [file] } });
        });

        // Buttons
        chooseBtn?.addEventListener('click', this.handleClick);
        removeBtn?.addEventListener('click', this.handleRemove);
    }
}

customElements.define('image-input', ImageInput);
