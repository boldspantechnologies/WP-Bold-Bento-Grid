# 🤖 Master Agent Directives for Bento Grid Engine

**Goal:** Build a high-performance, secure, Vercel/Stripe-inspired Bento Grid plugin that supports both Gutenberg (React) and Elementor (PHP) simultaneously using a unified hybrid architecture.

You are acting as a Senior WordPress Architect. You MUST strictly adhere to all of the following rules, security protocols, and design guidelines when generating, modifying, or reviewing code in this workspace.

---

## 1. Naming Conventions & Prefixing (Strict Compliance)
To prevent fatal collisions in the wild, every custom declaration MUST use a unique prefix of at least 5 letters. Do NOT use `wp_` or `_` as a prefix under any circumstances.
- **Global Prefix:** `bento_` (for functions, database options, transients).
- **Classes/Namespaces:** `Bento_` or `Bento\` (for PHP classes and namespaces).
- **Handles/IDs:** `bento-grid-` (for script/style enqueues, block names, widget IDs).
- **Constants:** `BENTO_GRID_` (e.g., `BENTO_GRID_VERSION`, `BENTO_GRID_PATH`).
- **Text Domain:** `bento-grid` (must be used in all i18n functions).

## 2. Security & Data Integrity (Zero Trust Policy)
Never trust user input. Never assume data is safe for output.
- **Input Sanitization:** Clean all input before database insertion. Use `sanitize_text_field()`, `intval()`, `absint()`, or `wp_kses_post()`.
- **Output Escaping:** Escape output as late as possible (immediately upon echoing). Use `esc_html()`, `esc_attr()`, `esc_url()`, or `wp_kses()`.
- **Nonces:** Use `wp_create_nonce()` and verify with `wp_verify_nonce()` for all form submissions, REST API routes, or AJAX calls.
- **Capability Checks:** Always verify permissions using `current_user_can( 'manage_options' )` before executing sensitive admin operations.
- **Direct File Access:** Prevent direct execution by placing this at the top of every PHP file:
  ```php
  if ( ! defined( 'ABSPATH' ) ) {
      exit; // Exit if accessed directly.
  }


3. Error Handling & Debugging
Do not use generic die() or echo for errors in production code.

Use the native WP_Error class to return complex error states.

Log silent background errors using error_log() rather than breaking the frontend UI.

4. Asset Management (Enqueuing)
Conditional Loading: Frontend assets (CSS/JS) MUST ONLY load when the Bento Grid block/widget is actually present on the page. Do not bloat the global site header.

Cache Busting: Always use plugin_dir_url( __FILE__ ) and attach BENTO_GRID_VERSION or filemtime() to bypass browser caching when updating scripts.

5. Hooks (Actions & Filters)
Group all hooks into a centralized loader or controller class to prevent spaghetti code.

Prefix custom hooks you create so other developers can interact with them (e.g., do_action( 'bento_grid_before_render' )).

6. Database Operations
Never query the database directly if a WordPress core function exists (e.g., use get_posts() instead of raw SQL).

If a custom SQL query is unavoidable via $wpdb, you MUST wrap it in $wpdb->prepare() to prevent SQL injection.

7. Hybrid Architecture (Gutenberg + Elementor)
The plugin must maintain a single source of truth for frontend DOM and CSS.

Unified Output: The React save.tsx (or dynamic PHP render) for Gutenberg and the render() method in the Elementor PHP widget MUST output the exact same HTML wrapper and data-* attributes.

Load the Elementor widget class only when the elementor/widgets/register hook fires.

8. UI/UX & Tailwind Guidelines
The design aesthetic is minimal, modern, and clean (Apple, Vercel, Stripe).

Tailwind Scope: Never use global resets. Preflight must be disabled (preflight: false) in tailwind.config.js to ensure the plugin doesn't break the native WordPress or Elementor admin UI.

Use a custom prefix in Tailwind (prefix: 'bento-') so classes like bento-flex or bento-bg-slate-900 don't conflict with active themes.

Keep the DOM footprint minimal. Avoid deep div nesting to protect Core Web Vitals.

9. Pro vs. Lite Separation
Always assume the current working directory is the Lite MVP.


If asked to build a premium feature (like dynamic queries or Framer Motion 3D effects), prepare the hook using apply_filters( 'bento_grid_is_pro', false ) so the Pro plugin can securely intercept it later.