/*
// resources/js/i18n.js

php artisan vendor:publish --tag=repeat-i18n-js

import __i, { loadTranslations, setLocale, _i } from './vendor/repeat-toolkit/i18n.js'

// primer inicijalizacije
const locale = window.APP_LOCALE ?? 'sr'
await loadTranslations(locale, `/i18n/${locale}.json`, { replace: true })
*/

let dictionaries = {
    sr: {},
    en: {},
}

let currentLocale = 'sr'

/**
 * Obaveštava komponente da su prevodi update-ovani
 */
const notifyUpdate = (locale) => {
    window.dispatchEvent(new CustomEvent('i18n:update', { detail: { locale } }))
}

/**
 * Postavi aktivni jezik
 */
export function setLocale(locale) {
    if (dictionaries[locale]) {
        currentLocale = locale
        notifyUpdate(locale)
    }
}

/**
 * Dodaj ili zameni prevode u memoriji
 */
export function setTranslations(locale, entries, { replace = false } = {}) {
    if (!dictionaries[locale] || replace) {
        dictionaries[locale] = { ...(replace ? {} : dictionaries[locale]), ...entries }
    } else {
        dictionaries[locale] = { ...dictionaries[locale], ...entries }
    }
    notifyUpdate(locale)
}

/**
 * Učitaj JSON fajl sa prevodima sa servera
 */
export async function loadTranslations(locale, url, { cacheBust = true, replace = false } = {}) {
    const finalUrl = cacheBust
        ? `${url}${url.includes('?') ? '&' : '?'}v=${Date.now()}`
        : url

    const res = await fetch(finalUrl, { headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error(`Failed to load ${locale} translations from ${url}`)

    const json = await res.json()
    setTranslations(locale, json, { replace })
    return json
}

/**
 * Glavna funkcija za prevod
 */
export default function __i(key, replacements = {}) {
    const dict = dictionaries[currentLocale] || {}
    let text = dict[key] ?? key
    for (const k in replacements) {
        text = text.replace(new RegExp(':'+k, 'g'), replacements[k])
    }
    return text
}

/**
 * Alias
 */
export function _i(key, repl = {}) {
    return __i(key, repl)
}
