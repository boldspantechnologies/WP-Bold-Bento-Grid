# Building Bold Bento Grid

## Requirements

| Tool | Version | Used for |
|------|---------|----------|
| [Node.js](https://nodejs.org/) + npm | Node 18+ | compiling `src/` → `build/` |
| PHP | 7.4+ | running the plugin / `php -l` |

## First-time setup

```sh
npm install
```

## Commands

| Command | What it does |
|---------|--------------|
| `npm run build` | One-off production build of `src/` into `build/`. |
| `npm start` | Watch mode for development. |
| `npm run package` | Full release build: clean → `build` → stage → zip. Output in `dist/`. |
| `npm run lint:js` / `lint:css` / `lint:ts` | Linting. |
| `npm run format` | Auto-format `src/`. |

`npm run package` (a.k.a. `gulp package`) produces:

```
dist/
  bold-bento-grid/            # staged plugin folder
  bold-bento-grid.zip         # canonical release archive
  bold-bento-grid-<version>.zip
```

The version is read from the `Version:` header in `bold-bento-grid.php` — bump
it there (and `Stable tag` in `readme.txt`) before packaging a release.

## What ends up in the zip

Only runtime files: `bold-bento-grid.php`, `uninstall.php`, `readme.txt`,
`LICENSE`, `includes/`, `assets/`, `build/`.

Everything else (`src/`, `node_modules/`, build config, dot-files) is excluded
by the glob list in `gulpfile.js` and mirrored in `.distignore` for
`wp-scripts plugin-zip` / `wp dist-archive`.

## Text domain

`bold-bento-grid` — declared in the plugin header (`Text Domain`), used in every
`__()` / `_e()` / `esc_html__()` call, in both `src/blocks/*/block.json`
(`"textdomain"`), and in `wp_set_script_translations()`. Do not change it without
renaming the whole set.

The plugin does not bundle a `.pot` or any `.mo`/`.json` translation files —
translate.wordpress.org generates and ships language packs automatically for
hosted plugins.

## WordPress.org SVN layout

The zip contents go into `trunk/` (and a tagged folder per release). Screenshots,
banner, and icon images go in the SVN-root `assets/` directory — **not** in the
plugin folder / zip. (`assets/` inside the plugin is runtime CSS, unrelated.)
