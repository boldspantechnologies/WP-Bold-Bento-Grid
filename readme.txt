=== Bold Bento Grid ===
Contributors: boldspantechnologies
Tags: bento, grid, layout, columns, gallery, design
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, responsive Bento-style grid for Gutenberg and Elementor with curated presets, per-tile content modes, and hover effects.

== Description ==

Bold Bento Grid brings the modern "bento box" layout to WordPress with a single, high-performance CSS Grid engine that renders identically in the block editor and on the front end.

Choose a curated preset, drop in your tiles, and the grid takes care of the geometry. Every tile can show an image with an overlay, text above or below its image, or a free-form stack of inner blocks.

**Layout**

* Curated presets for 2 to 5 tiles: Split 50/50, Asymmetric 70/30, Hero Focus, Triple Row, Balanced Quad, Showcase 4, Feature 5, Asymmetric 5, and Strip 5.
* The preset list follows the tile count, and the number of tiles is controlled with a single slider.
* One 12-column CSS Grid engine — no per-block inline layout hacks.

**Tiles**

* Content modes: image overlay (with an adjustable dark scrim), text on top, text below, or custom inner blocks.
* Per-tile design controls: background colour or CSS gradient, text colour, border width and colour.
* Opt-in hover micro-interactions: lift, glow, image zoom, or a pointer-tracking 3D tilt.

**Responsive**

* Under 768px every layout collapses to a single stacked column.
* Between 768px and 1024px the 4- and 5-tile presets step down to two columns.
* The same breakpoints apply inside the editor canvas, so what you see is what you publish.

**Built for real sites**

* Works identically in the Gutenberg block editor and the Elementor page builder (widget lives under the "Bento Engine" category).
* All markup is wrapped in a `.bold-bento-wrapper` namespace so active themes and Elementor global styles cannot override the grid gap, tile padding, or typography.
* Accessibility: tiles expose an `aria-label` from their title, and all motion respects `prefers-reduced-motion`.
* Security-first: every URL is passed through `esc_url()`, CSS values are filtered, and numeric ranges are clamped before output.
* Translation-ready with the `bold-bento-grid` text domain.

== Installation ==

1. Upload the `bold-bento-grid` folder to `/wp-content/plugins/`, or install the plugin through the **Plugins > Add New** screen.
2. Activate **Bold Bento Grid** through the **Plugins** screen.
3. On activation you are taken to a short **Get started with Bento Grid** screen with step-by-step instructions for the block editor and for Elementor. You can reopen it any time from the **Get started** link on the Plugins screen.
4. In the block editor, add the **Bento Grid** block. In Elementor, drag the **Bento Grid** widget from the **Bento Engine** category.

== Frequently Asked Questions ==

= How many tiles can a grid have? =

Two to five, with a curated preset for every count. A separate commercial add-on, Bold Bento Grid Pro (https://bentogrid.boldspan.tech), adds its own larger layouts, glassmorphism presets, and dynamic WooCommerce/query grids — this plugin is fully functional on its own without it.

= Will it match my theme? =

Yes. Tiles inherit your theme's font family and body colour, but the grid gap, tile padding, and heading/caption sizing are locked to the plugin's own values inside the `.bold-bento-wrapper` namespace so theme rules can't distort the layout.

= Is the Elementor output the same as the block output? =

Yes — both produce the same HTML wrapper, classes, and `data-bento-grid` attribute, and share one stylesheet.

= Does it support reduced motion? =

Yes. When the operating system requests reduced motion, all hover transitions and transforms are disabled.

= Does the plugin load anything on pages that don't use a grid? =

No. The front-end stylesheet and runtime are only enqueued on requests that actually render a Bento Grid block or widget.

== Screenshots ==

1. The Bento Grid block in the editor with the visual layout-preset picker.
2. A four-tile "Showcase" layout on the front end.
3. Per-tile content-mode, hover, colour, and border controls.
4. The same grid collapsing to a single column on mobile.

== Source Code ==

This plugin ships compiled JavaScript and CSS in the `build/` directory. The
human-readable sources — TypeScript and SCSS under `src/`, the block metadata,
and the full build pipeline — live in the public development repository:

https://github.com/boldspantechnologies/Bold-Bento-Grid

Build tools: [Node.js](https://nodejs.org/) 18+ and npm, with
[@wordpress/scripts](https://www.npmjs.com/package/@wordpress/scripts) (webpack).

* `npm install` — install the build toolchain.
* `npm run build` — compile `src/` into `build/` (`bold-bento/grid` block, tile block, front-end runtime, stylesheet).
* `npm run package` — clean, build, and produce `dist/bold-bento-grid.zip` containing only the files WordPress needs.

The text domain is `bold-bento-grid` throughout (plugin header, every `__()` /
`_e()` call, both `block.json` files, and `wp_set_script_translations()`).
Translations are handled by translate.wordpress.org.

== Changelog ==

= 1.0.0 =
* Initial release.
* Post-activation "Get started" screen with block-editor and Elementor instructions, reachable from the Plugins screen.
* CSS Grid engine with nine curated presets (2–5 tiles) shared between the block editor and the front end.
* Gutenberg block (`bold-bento/grid` + `bold-bento/tile`) with a static save renderer.
* Elementor widget producing byte-identical markup.
* Per-tile content modes, design controls, and opt-in hover effects.
* Mobile (<768px) and tablet (768–1024px) responsive breakpoints, applied in the editor and on the front end.
* `.bold-bento-wrapper` CSS isolation namespace.
* Accessibility: title-based `aria-label`s and full `prefers-reduced-motion` support.
* Output hardening: `esc_url()` / `esc_html()` / `esc_attr()` on all output, CSS-value filtering, and clamped numeric ranges.
* Translation-ready (`bold-bento-grid` text domain).

== Upgrade Notice ==

= 1.0.0 =
Initial release of Bold Bento Grid.
