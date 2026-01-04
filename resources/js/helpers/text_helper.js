// resources/js/helpers/text_helper.js

export default class TextHelper {
    /**
     * Enkoduje tekst u Base64 (UTF-8 safe)
     * — koristi se za bezbedno prosleđivanje HTML sadržaja u atributima
     *
     * @param {string} str
     * @returns {string}
     */
    static encodeForAttr(str = "") {
        try {
            const bytes = new TextEncoder().encode(str);
            let binary = "";
            bytes.forEach(b => (binary += String.fromCharCode(b)));
            return btoa(binary);
        } catch (e) {
            console.error("TextHelper.encodeForAttr error:", e);
            return "";
        }
    }

    /**
     * Dekodira Base64 nazad u običan UTF-8 tekst
     *
     * @param {string} b64
     * @returns {string}
     */
    static decodeFromAttr(b64 = "") {
        try {
            const binary = atob(b64);
            const bytes = new Uint8Array([...binary].map(ch => ch.charCodeAt(0)));
            return new TextDecoder().decode(bytes);
        } catch (e) {
            console.error("TextHelper.decodeFromAttr error:", e);
            return "";
        }
    }

    /**
     * Postavlja enkodiranu vrednost kao atribut na dati element
     *
     * @param {HTMLElement} el
     * @param {string} rawValue
     */
    static setEncodedAttr(el, rawValue = "") {
        if (!(el instanceof HTMLElement)) {
            console.warn("TextHelper.setEncodedAttr: target nije HTMLElement");
            return;
        }
        el.setAttribute("element_value_b64", this.encodeForAttr(rawValue ?? ""));
    }

    /**
     * Čita i dekodira vrednost iz atributa elementa
     *
     * @param {HTMLElement} el
     * @returns {string}
     */
    static getDecodedAttr(el) {
        if (!(el instanceof HTMLElement)) return "";
        const b64 = el.getAttribute("element_value_b64");
        return b64 ? this.decodeFromAttr(b64) : "";
    }
}
