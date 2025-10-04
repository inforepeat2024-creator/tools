// Očekuje objekat { baseUrl, routes } (iz routes.gen.json/.js)
export function makeRouter(routesPayload) {
    const BASE_URL = routesPayload.baseUrl || '';

    function fillTemplate(template, params, defaults = {}) {
        // zamenjuje {param} i {param?}; baca grešku ako nedostaje obavezni param
        return template.replace(/\{(\w+)\??\}/g, (_, key) => {
            if (params?.[key] != null) return encodeURIComponent(String(params[key]));
            if (defaults?.[key] != null) return encodeURIComponent(String(defaults[key]));
            if (template.includes(`{${key}}`)) {
                throw new Error(`Missing required param "${key}"`);
            }
            // optional param bez vrednosti -> ukloni ceo segment pre eventualnog sledećeg "/" (ali ovde već radimo replace, pa vrati prazno)
            return '';
        })
            // očisti eventualne dvostruke kos crte nastale uklanjanjem optional segmenata
            .replace(/\/+/g, '/')
            .replace(/\/$/, '') || '/';
    }

    function buildQuery(query = {}) {
        const parts = [];
        Object.entries(query).forEach(([k, v]) => {
            if (v == null) return;
            if (Array.isArray(v)) {
                v.forEach(it => parts.push(`${encodeURIComponent(k)}[]=${encodeURIComponent(String(it))}`));
            } else if (typeof v === 'object') {
                // plitko: obj -> k[sub]=v
                Object.entries(v).forEach(([sub, val]) => {
                    if (val != null) parts.push(`${encodeURIComponent(k)}[${encodeURIComponent(sub)}]=${encodeURIComponent(String(val))}`);
                });
            } else {
                parts.push(`${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`);
            }
        });
        return parts.length ? `?${parts.join('&')}` : '';
    }

    function route(name, params = {}, query = {}, opts = {}) {
        const r = routesPayload.routes[name] || routesPayload.routes[`/${name}`]; // fallback
        if (!r) throw new Error(`Route "${name}" not found`);

        // domain može imati {param} (npr. {tenant}.example.com)
        let host = '';
        if (r.domain) {
            host = fillTemplate(r.domain, params, r.defaults);
            // ako baseUrl postoji i uključuje protokol, uzmi ga; inače pretpostavi isti protokol
            if (BASE_URL) {
                const u = new URL(BASE_URL);
                host = `${u.protocol}//${host}`;
            } else {
                host = `${location.protocol}//${host}`;
            }
        }

        let path = fillTemplate(r.uri, params, r.defaults);
        // apsolutni ili relativni URL
        const absolute = opts.absolute ?? ( !!BASE_URL || !!r.domain );
        const prefix = r.domain ? host : (absolute ? (BASE_URL || '') : '');

        const url = `${prefix}${path}${buildQuery(query)}`;
        return url || '/';
    }

    return { route };
}
