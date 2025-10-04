# Repeat Toolkit

Laravel / Vite toolkit sa abstraktnim modelima, kontrolerima, helperima i auto-učitavanjem JS komponenti.

---

## Instalacija paketa

u composer projekta

"minimum-stability": "dev",
"prefer-stable": true

u composer json dodati: 
`  "repositories": [
        { "type": "vcs", "url": "https://github.com/inforepeat2024-creator/tools.git" }
    ],
`

```bash
 λ composer require petar/repeat-toolkit:dev-main --prefer-source
```

> ⚠️ Koristi `@dev` dok paket nije tagovan.

---

## Laravel publish komande

### 1. Publish 
php artisan vendor:publish --provider="RepeatToolkit\Providers\ToolkitServiceProvider" --force
## Vite konfiguracija

U `vite.config.js` obmotaj postojeći config kroz `withRepeatToolkit`:

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { withRepeatToolkit } from './resources/js/vendor/repeat-toolkit/vite-plugin.js'

export default defineConfig(withRepeatToolkit({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js'
      ],
      refresh: true,
    }),
  ],
}, {
  componentsDir: 'resources/js/components',
}))
```

ovo je u app.js

```js
import __i, { loadTranslations, setLocale, _i } from './vendor/repeat-toolkit/i18n.js'



// primer inicijalizacije
const locale = window.APP_LOCALE ?? 'sr'
await loadTranslations(locale, `/medicinski_turizam/public/i18n/${locale}.json`, { replace: true })

```


## I18n prevodi

1. Generisanje prevoda fajlova:

```bash
php artisan i18n:make
```




4. Routes js:

```bash
php artisan repeat:routes:export resources/js/routes.gen.json --pretty --absolute
```



---


