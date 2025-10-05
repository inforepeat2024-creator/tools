export default class UrlHelper {

    /**
     * Vrati vrednost parametra iz trenutnog URL-a
     * @param {string} key - ime parametra
     * @param {string|null} defaultValue - vrednost ako parametar ne postoji
     * @returns {string|null}
     */
    static getParam(key, defaultValue = null) {
        let params = new URLSearchParams(window.location.search);
        return params.has(key) ? params.get(key) : defaultValue;
    }

    static getParamAll(key, url = null) {
        if (!url) {
            url = window.location.href;
        }

        let params = new URL(url).searchParams;
        let values = params.getAll(key);

        if (values.length === 0) {
            return [];
        }

        return values.map(v => this.normalizeValue(v));
    }

    static normalizeValue(value) {
        if (value === null) return null;

        // Ako je ceo broj
        if (/^-?\d+$/.test(value)) {
            return parseInt(value, 10);
        }

        // Ako je decimalni broj
        if (/^-?\d+\.\d+$/.test(value)) {
            return parseFloat(value);
        }

        // Inače string
        return value;
    }

    /**
     * Vrati sve parametre iz URL-a kao objekat
     * @returns {Object}
     */
    static getAllParams() {
        let params = new URLSearchParams(window.location.search);
        let result = {};
        for (let [key, value] of params.entries()) {
            result[key] = value;
        }
        return result;
    }

    /**
     * Dodaj ili promeni parametar u URL-u (opciono reload stranice)
     * @param {string} key - ime parametra
     * @param {string} value - nova vrednost
     * @param {boolean} reload - da li da osveži stranicu (default: false)
     */
    static setParam(key, value, reload = false) {
        let url = new URL(window.location.href);
        url.searchParams.set(key, value);

        if (reload) {
            window.location.href = url.toString();
        } else {
            window.history.pushState({}, '', url.toString());
        }
    }

    /**
     * Ukloni parametar iz URL-a (opciono reload stranice)
     * @param {string} key - ime parametra
     * @param {boolean} reload - da li da osveži stranicu (default: false)
     */
    static removeParam(key, reload = false) {
        let url = new URL(window.location.href);
        url.searchParams.delete(key);

        if (reload) {
            window.location.href = url.toString();
        } else {
            window.history.pushState({}, '', url.toString());
        }
    }
}
