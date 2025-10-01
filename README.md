# Repeat Toolkit

Laravel / Vite toolkit sa abstraktnim modelima, kontrolerima, helperima i auto-učitavanjem JS komponenti.

---

## Instalacija paketa

```bash
composer require inforepeat/repeat-toolkit:@dev
```

> ⚠️ Koristi `@dev` dok paket nije tagovan.

---

## Laravel publish komande

### 1. Publish Vite plugin stub
Ovo doda `resources/stubs/vite/repeat-vite-plugin.js` u projekat:

```bash
php artisan vendor:publish --provider="RepeatToolkit\Providers\ToolkitServiceProvider" --tag=repeat-vite-merge
```

### 2. Publish JS helperi (i18n)
Ovo doda `resources/js/i18n.js` (loader za prevode):

```bash
php artisan vendor:publish --provider="RepeatToolkit\Providers\ToolkitServiceProvider" --tag=repeat-i18n-js
```

### 3. Publish ostali stubovi (ako ih bude)
```bash
php artisan vendor:publish --provider="RepeatToolkit\Providers\ToolkitServiceProvider" --tag=repeat-stubs
```

---

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

> ✅ Nije potrebno ručno menjati `app.js`. Svi fajlovi iz `resources/js/components` će se automatski importovati.

---

## I18n prevodi

1. Generisanje `.po` fajlova:

```bash
php artisan make:po-from-i
```

2. Generisanje JSON prevoda za frontend:

```bash
php artisan make:json-from-i
```

3. Publikacija prevoda:

```bash
mkdir -p public/i18n
php artisan make:json-from-i
```

> JSON fajlovi završavaju u `public/i18n/{locale}.json`.

---

## Posle svakog update-a paketa

- Odradi ponovo publish (ako su se stubovi promenili):  
  ```bash
  php artisan vendor:publish --provider="RepeatToolkit\Providers\ToolkitServiceProvider" --force
  ```

- Očisti cache:  
  ```bash
  php artisan optimize:clear
  ```

---

## Kratak rezime komandi

```bash
# Instalacija
composer require inforepeat/repeat-toolkit:@dev

# Publish vite plugina
php artisan vendor:publish --provider="RepeatToolkit\Providers\ToolkitServiceProvider" --tag=repeat-vite-merge

# Publish i18n helpera
php artisan vendor:publish --provider="RepeatToolkit\Providers\ToolkitServiceProvider" --tag=repeat-i18n-js

# Generisanje po i json prevoda
php artisan make:po-from-i
php artisan make:json-from-i
```

---

Sada u `resources/js/components` možeš slobodno da dodaješ nove komponente (npr. `test_component.js`), i one će automatski biti učitane u bundl bez ikakvih dodatnih izmjena u `app.js`.
