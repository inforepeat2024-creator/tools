// resources/stubs/vite/repeat-vite-plugin.js
import fs from 'fs'
import path from 'path'

const VIRTUAL_ID = 'virtual:repeat-components'
const RESOLVED_VIRTUAL_ID = '\0' + VIRTUAL_ID

/**
 * repeatAutoComponents + auto-inject import virtualnog modula u entry fajlove.
 *
 * Options:
 *  - componentsDir: string  (default: 'resources/js/components')
 *  - entryTest: (id: string) => boolean
 *      Funkcija koja određuje da li je fajl "entry" u koji treba ubaciti import.
 *      Default: svaki .js/.ts direktno u resources/js korenu (npr. app.js, admin.js)
 */
export function repeatAutoComponents(componentsDir = 'resources/js/components', entryTest) {
    let root = process.cwd()
    let absDir = path.resolve(root, componentsDir)

    const listFiles = () => {
        if (!fs.existsSync(absDir)) return []
        const out = []
        const walk = (dir) => {
            for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
                const p = path.join(dir, entry.name)
                if (entry.isDirectory()) walk(p)
                else if (/\.(js|ts)$/.test(entry.name)) {
                    out.push(path.relative(root, p).replaceAll('\\', '/'))
                }
            }
        }
        walk(absDir)
        return out
    }

    // Default test: targetiraj "top-level" entry-e u resources/js (bez potfoldera)
    const isEntry = entryTest || ((id) => {
        const norm = id.replaceAll('\\', '/')
        // primeri: /.../resources/js/app.js, /.../resources/js/admin.ts
        return /\/resources\/js\/[^/]+\.(js|ts)$/.test(norm)
    })

    return {
        name: 'repeat-auto-components',
        enforce: 'pre',

        configResolved(config) {
            root = config.root || process.cwd()
            absDir = path.resolve(root, componentsDir)
        },

        resolveId(id) {
            if (id === VIRTUAL_ID) return RESOLVED_VIRTUAL_ID
        },

        load(id) {
            if (id === RESOLVED_VIRTUAL_ID) {
                const files = listFiles()
                const code = files.map(f => `import '/${f}';`).join('\n')
                return code || '/* no components found */'
            }
        },

        // Automatski ubaci import virtualnog modula u ENTRY fajlove, bez menjanja korisnikovih fajlova
        transform(code, id) {
            if (!isEntry(id)) return null
            // Ako je već ubačeno, nemoj duplo
            if (code.includes(`import '${VIRTUAL_ID}'`) || code.includes(`import "${VIRTUAL_ID}"`)) {
                return null
            }
            const injected = `import '${VIRTUAL_ID}';\n${code}`
            return { code: injected, map: null }
        },

        handleHotUpdate(ctx) {
            const norm = ctx.file.replaceAll('\\', '/')
            if (norm.includes('/resources/js/components/')) {
                // invalidiraj virtualni modul da bi dodati/obrisani fajlovi odmah ušli/izašli iz grafa
                const mod = ctx.server.moduleGraph.getModuleById(RESOLVED_VIRTUAL_ID)
                if (mod) ctx.server.moduleGraph.invalidateModule(mod)
                return ctx.modules
            }
        }
    }
}

/**
 * withRepeatToolkit(userConfig, options?)
 * options:
 *   - componentsDir: string
 *   - alias: Record<string,string>
 *   - entryTest: (id: string) => boolean
 */
export function withRepeatToolkit(userConfig = {}, options = {}) {
    const {
        componentsDir = 'resources/js/components',
        alias = {},
        entryTest,
    } = options

    const merged = { ...userConfig }

    // 1) Plugins
    const userPlugins = Array.isArray(userConfig.plugins) ? userConfig.plugins : []
    merged.plugins = [...userPlugins, repeatAutoComponents(componentsDir, entryTest)]

    // 2) Aliases
    const userAliases = (userConfig.resolve && userConfig.resolve.alias) || {}
    merged.resolve = {
        ...(userConfig.resolve || {}),
        alias: {
            '@': path.resolve(process.cwd(), 'resources/js'),
            ...userAliases,
            ...alias,
        }
    }

    // 3) Prebundle hint (nije obavezno, ali pomaže)
    merged.optimizeDeps = {
        ...(userConfig.optimizeDeps || {}),
        entries: [
            ...(userConfig.optimizeDeps?.entries || []),
            VIRTUAL_ID,
        ],
    }

    return merged
}
