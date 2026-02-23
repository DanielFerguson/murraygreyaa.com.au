# MGAA Theme UI Polish & SEO — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Improve the MGAA WordPress theme with mobile navigation, dashboard/form/search/record UI polish, a Vite+Tailwind build step, bug fixes, and SEO enhancements.

**Architecture:** All changes are within `wp-content/themes/tailwind-acf/`. The theme uses Tailwind CSS utility classes, ACF Pro for custom fields, and hand-coded PHP templates. No JS framework — vanilla JS only. The build step replaces the CDN with compiled CSS via Vite.

**Tech Stack:** PHP (WordPress), Tailwind CSS v3, Vite, PostCSS, vanilla JavaScript, ACF Pro, Yoast SEO.

**Conventions (from AGENTS.md):** Tabs for indentation, snake_case functions prefixed with `tailwind_`, `__()` with `tailwind-acf` text domain, logic in `inc/` not templates, `WP_DEBUG` for testing, concise imperative commit titles.

---

## Task 1: Extract shared option arrays (DRY fix)

Grade, sex, colour, and calving ease options are duplicated across 5+ files. Extract to shared functions.

**Files:**
- Modify: `wp-content/themes/tailwind-acf/inc/cattle-registration.php`
- Modify: `wp-content/themes/tailwind-acf/page-register-cattle.php`
- Modify: `wp-content/themes/tailwind-acf/page-animal-search.php`
- Modify: `wp-content/themes/tailwind-acf/single-cattle_registration.php`
- Modify: `wp-content/themes/tailwind-acf/page-dashboard.php`

**Step 1: Add shared option functions to `inc/cattle-registration.php`**

Add these functions before the `tailwind_register_cattle_acf_fields()` function (before line 134). These provide both "form" options (with code prefix, e.g., "PB: Pure Breed") and "label" options (display only, e.g., "Pure Breed"):

```php
/**
 * Get cattle grade options for forms.
 *
 * @return array
 */
function tailwind_cattle_get_grade_options() {
	return array(
		'PB' => __( 'PB: Pure Breed', 'tailwind-acf' ),
		'A'  => __( 'A: A Grade', 'tailwind-acf' ),
		'B'  => __( 'B: B Grade', 'tailwind-acf' ),
		'C'  => __( 'C: C Grade', 'tailwind-acf' ),
	);
}

/**
 * Get cattle grade labels for display.
 *
 * @return array
 */
function tailwind_cattle_get_grade_labels() {
	return array(
		'PB' => __( 'Pure Breed', 'tailwind-acf' ),
		'A'  => __( 'A Grade', 'tailwind-acf' ),
		'B'  => __( 'B Grade', 'tailwind-acf' ),
		'C'  => __( 'C Grade', 'tailwind-acf' ),
	);
}

/**
 * Get cattle sex options for forms.
 *
 * @return array
 */
function tailwind_cattle_get_sex_options() {
	return array(
		'M' => __( 'M: Male', 'tailwind-acf' ),
		'F' => __( 'F: Female', 'tailwind-acf' ),
		'S' => __( 'S: Steer', 'tailwind-acf' ),
	);
}

/**
 * Get cattle sex labels for display.
 *
 * @return array
 */
function tailwind_cattle_get_sex_labels() {
	return array(
		'M' => __( 'Male', 'tailwind-acf' ),
		'F' => __( 'Female', 'tailwind-acf' ),
		'S' => __( 'Steer', 'tailwind-acf' ),
	);
}

/**
 * Get cattle colour options for forms.
 *
 * @return array
 */
function tailwind_cattle_get_colour_options() {
	return array(
		'G' => __( 'G: Grey', 'tailwind-acf' ),
		'S' => __( 'S: Silver', 'tailwind-acf' ),
		'B' => __( 'B: Black', 'tailwind-acf' ),
		'D' => __( 'D: Dun', 'tailwind-acf' ),
	);
}

/**
 * Get cattle colour labels for display.
 *
 * @return array
 */
function tailwind_cattle_get_colour_labels() {
	return array(
		'G' => __( 'Grey', 'tailwind-acf' ),
		'S' => __( 'Silver', 'tailwind-acf' ),
		'B' => __( 'Black', 'tailwind-acf' ),
		'D' => __( 'Dun', 'tailwind-acf' ),
	);
}

/**
 * Get cattle calving ease options for forms.
 *
 * @return array
 */
function tailwind_cattle_get_calving_ease_options() {
	return array(
		'1' => __( '1: Unassisted', 'tailwind-acf' ),
		'2' => __( '2: Assisted', 'tailwind-acf' ),
		'3' => __( '3: Fully Assisted', 'tailwind-acf' ),
		'4' => __( '4: Caesarean', 'tailwind-acf' ),
		'5' => __( '5: Breach', 'tailwind-acf' ),
	);
}

/**
 * Get cattle calving ease labels for display.
 *
 * @return array
 */
function tailwind_cattle_get_calving_ease_labels() {
	return array(
		'1' => __( 'Unassisted', 'tailwind-acf' ),
		'2' => __( 'Assisted', 'tailwind-acf' ),
		'3' => __( 'Fully Assisted', 'tailwind-acf' ),
		'4' => __( 'Caesarean', 'tailwind-acf' ),
		'5' => __( 'Breach', 'tailwind-acf' ),
	);
}
```

**Step 2: Update ACF field definitions in the same file**

In `tailwind_register_cattle_acf_fields()`, replace the hardcoded `'choices'` arrays with calls to the shared functions:

- Grade field (around line 159): `'choices' => tailwind_cattle_get_grade_options(),`
- Sex field (around line 209): `'choices' => tailwind_cattle_get_sex_options(),`
- Colour field (around line 221): `'choices' => tailwind_cattle_get_colour_options(),`
- Calving ease field (around line 234): `'choices' => tailwind_cattle_get_calving_ease_options(),`

Also update the sex_labels in `tailwind_cattle_admin_column_content()` (around line 391):
```php
$sex_labels = tailwind_cattle_get_sex_labels();
```

And in `tailwind_cattle_notify_admin_on_submission()` (around line 582):
```php
$sex_labels = tailwind_cattle_get_sex_labels();
```

**Step 3: Update `page-register-cattle.php`**

Replace lines 83-112 (the four option array definitions) with:
```php
$grade_options        = tailwind_cattle_get_grade_options();
$sex_options          = tailwind_cattle_get_sex_options();
$colour_options       = tailwind_cattle_get_colour_options();
$calving_ease_options = tailwind_cattle_get_calving_ease_options();
```

**Step 4: Update `page-animal-search.php`**

Replace lines 16-37 (grade, sex, colour options with "All" prefix) with:
```php
$grade_options  = array( '' => __( 'All Grades', 'tailwind-acf' ) ) + tailwind_cattle_get_grade_options();
$sex_options    = array( '' => __( 'All', 'tailwind-acf' ) ) + tailwind_cattle_get_sex_options();
$colour_options = array( '' => __( 'All Colours', 'tailwind-acf' ) ) + tailwind_cattle_get_colour_options();
```

Replace the `$sex_labels` and `$colour_labels` display arrays (around lines 204-215) with:
```php
$sex_labels    = tailwind_cattle_get_sex_labels();
$colour_labels = tailwind_cattle_get_colour_labels();
```

**Step 5: Update `single-cattle_registration.php`**

Replace the four label arrays (lines 37-63) with:
```php
$grade_labels   = tailwind_cattle_get_grade_labels();
$sex_labels     = tailwind_cattle_get_sex_labels();
$colour_labels  = tailwind_cattle_get_colour_labels();
$calving_labels = tailwind_cattle_get_calving_ease_labels();
```

**Step 6: Update `page-dashboard.php`**

Replace the inline `$sex_labels` array (lines 226-230) with:
```php
$sex_labels = tailwind_cattle_get_sex_labels();
```

**Step 7: Verify and commit**

Verify: Load the site, check each template renders correctly with `WP_DEBUG` enabled. Confirm no undefined function errors.

```bash
git add wp-content/themes/tailwind-acf/inc/cattle-registration.php \
        wp-content/themes/tailwind-acf/page-register-cattle.php \
        wp-content/themes/tailwind-acf/page-animal-search.php \
        wp-content/themes/tailwind-acf/single-cattle_registration.php \
        wp-content/themes/tailwind-acf/page-dashboard.php
git commit -m "Extract shared cattle option arrays to inc/cattle-registration.php"
```

---

## Task 2: Fix broken parallax and home.php pagination bug

**Files:**
- Modify: `wp-content/themes/tailwind-acf/template-parts/blocks/hero.php:112-120`
- Modify: `wp-content/themes/tailwind-acf/home.php:45-53`

**Step 1: Fix the parallax in `hero.php`**

The parallax JS sets `--parallax-y` but the hero image never uses it. On line 113, change the `<img>` tag to include an inline style that consumes the CSS custom property:

Replace:
```php
		<img
			class="h-full w-full object-cover scale-105"
			data-parallax-speed="0.15"
			data-parallax-max="36"
			src="<?php echo $bg_url; ?>"
			alt=""
			role="presentation"
			decoding="async"
		>
```

With:
```php
		<img
			class="h-full w-full object-cover scale-105"
			style="transform: scale(1.05) translateY(var(--parallax-y, 0px))"
			data-parallax-speed="0.15"
			data-parallax-max="36"
			src="<?php echo $bg_url; ?>"
			alt=""
			role="presentation"
			decoding="async"
		>
```

Note: We use inline `style` with both `scale(1.05)` and `translateY()` because the Tailwind `scale-105` class would be overridden by the inline transform. Remove the `scale-105` class from the `class` attribute.

**Step 2: Fix `home.php` pagination**

Replace the `$rest_query` (lines 45-53) to use `post__not_in` instead of `offset`:

Replace:
```php
$rest_query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => get_option('posts_per_page'),
		'paged'          => $paged,
		'offset'         => $offset,
	)
);
```

With:
```php
$rest_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => get_option( 'posts_per_page' ),
	'paged'          => $paged,
);
if ( $featured_post ) {
	$rest_args['post__not_in'] = array( $featured_post->ID );
}
$rest_query = new WP_Query( $rest_args );
```

Also remove the now-unused `$offset` variable (line 25: `$offset = 0;` and line 39: `$offset = 1;`).

**Step 3: Commit**

```bash
git add wp-content/themes/tailwind-acf/template-parts/blocks/hero.php \
        wp-content/themes/tailwind-acf/home.php
git commit -m "Fix hero parallax effect and home.php pagination bug"
```

---

## Task 3: Set up Vite + Tailwind build step

**Files:**
- Create: `wp-content/themes/tailwind-acf/package.json`
- Create: `wp-content/themes/tailwind-acf/tailwind.config.js`
- Create: `wp-content/themes/tailwind-acf/vite.config.js`
- Create: `wp-content/themes/tailwind-acf/postcss.config.js`
- Create: `wp-content/themes/tailwind-acf/src/main.css`
- Modify: `wp-content/themes/tailwind-acf/functions.php:82-141` (frontend enqueue)
- Modify: `wp-content/themes/tailwind-acf/functions.php:147-209` (editor enqueue)
- Modify: `wp-content/themes/tailwind-acf/header.php` (remove CDN inline config, move nav CSS)
- Update: `.gitignore` (add node_modules)

**Step 1: Create `package.json`**

```json
{
  "name": "tailwind-acf",
  "private": true,
  "scripts": {
    "dev": "vite build --watch",
    "build": "vite build"
  },
  "devDependencies": {
    "autoprefixer": "^10.4.20",
    "postcss": "^8.4.49",
    "tailwindcss": "^3.4.17",
    "vite": "^6.0.0"
  }
}
```

**Step 2: Create `tailwind.config.js`**

```js
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        brand: {
          DEFAULT: '#2563eb',
          light: '#60a5fa',
          dark: '#1d4ed8',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
```

Note: Add `@tailwindcss/forms` and `@tailwindcss/typography` to devDependencies too:
```json
"@tailwindcss/forms": "^0.5.9",
"@tailwindcss/typography": "^0.5.15"
```

**Step 3: Create `postcss.config.js`**

```js
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
};
```

**Step 4: Create `vite.config.js`**

```js
import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: 'src/main.css',
      output: {
        assetFileNames: '[name][extname]',
      },
    },
  },
});
```

**Step 5: Create `src/main.css`**

This file contains the Tailwind directives plus the nav animation CSS currently inline in `header.php` (lines 51-170) and the hero animation CSS from `hero.php`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Navigation link animations */
.nav-animated ul {
  display: flex;
  gap: 2rem;
}

.nav-animated a {
  position: relative;
  padding: 0.5rem 0;
  transition: color 0.2s ease;
}

.nav-animated a::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #fbbf24, #f59e0b);
  transition: width 0.3s ease;
  border-radius: 1px;
}

.nav-animated a:hover::after,
.nav-animated .current-menu-item a::after {
  width: 100%;
}

.nav-animated a:hover {
  color: #fde68a;
}

.nav-animated .current-menu-item a {
  color: #fde68a;
}

/* Dropdown styles */
.nav-animated .has-dropdown {
  position: relative;
}

.nav-animated ul {
  align-items: center;
}

.nav-animated .has-dropdown > a {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.nav-animated > ul > li > a {
  display: inline-flex;
  align-items: center;
}

.nav-animated .dropdown-chevron {
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.nav-animated .has-dropdown.is-open > a .dropdown-chevron,
.nav-animated .has-dropdown:hover > a .dropdown-chevron {
  transform: rotate(180deg);
}

.nav-animated .sub-menu {
  position: absolute;
  top: 100%;
  left: 0;
  min-width: 160px;
  background: rgba(55, 65, 81, 0.95);
  border-radius: 0.375rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
  padding: 0.25rem 0;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.15s ease, visibility 0.15s;
  z-index: 50;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-top: 0.25rem;
}

.nav-animated .has-dropdown:hover > .sub-menu,
.nav-animated .has-dropdown.is-open > .sub-menu {
  opacity: 1;
  visibility: visible;
}

.nav-animated .sub-menu li {
  margin: 0;
  width: 100%;
}

.nav-animated .sub-menu a {
  display: block;
  width: 100%;
  padding: 0.25rem 0.75rem;
  white-space: nowrap;
  color: #fff;
  font-size: 0.875rem;
  transition: background-color 0.15s ease, color 0.15s ease;
}

.nav-animated .sub-menu a::after {
  display: none;
}

.nav-animated .sub-menu a:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fde68a;
}

.nav-animated .sub-menu .current-menu-item > a {
  color: #fde68a;
}

/* Print styles */
@media print {
  header,
  footer,
  .no-print {
    display: none !important;
  }

  main {
    padding: 0 !important;
  }

  body {
    font-size: 12pt;
    color: #000;
    background: #fff;
  }

  a {
    color: #000;
    text-decoration: none;
  }

  .rounded-xl {
    border: 1px solid #ccc;
  }
}
```

**Step 6: Install dependencies and build**

Run from `wp-content/themes/tailwind-acf/`:
```bash
npm install
npm run build
```

Verify `dist/main.css` is created and contains compiled Tailwind utilities.

**Step 7: Update `functions.php` — frontend enqueue**

Replace the `tailwind_acf_enqueue_frontend_assets` function (lines 82-142). Remove the CDN script enqueue and inline config. Enqueue the compiled CSS instead:

```php
function tailwind_acf_enqueue_frontend_assets() {
	$dist_css = get_template_directory() . '/dist/main.css';
	if ( file_exists( $dist_css ) ) {
		wp_enqueue_style(
			'tailwind-acf-style',
			get_template_directory_uri() . '/dist/main.css',
			array(),
			filemtime( $dist_css )
		);
	}

	$carousel_js = get_template_directory() . '/assets/js/carousel.js';
	if ( file_exists( $carousel_js ) ) {
		wp_enqueue_script(
			'tailwind-acf-carousel',
			get_template_directory_uri() . '/assets/js/carousel.js',
			array(),
			filemtime( $carousel_js ),
			true
		);
	}

	$hero_parallax_js = get_template_directory() . '/assets/js/hero-parallax.js';
	if ( file_exists( $hero_parallax_js ) ) {
		wp_enqueue_script(
			'tailwind-acf-hero-parallax',
			get_template_directory_uri() . '/assets/js/hero-parallax.js',
			array(),
			filemtime( $hero_parallax_js ),
			true
		);
	}
}
```

**Step 8: Update `functions.php` — editor enqueue**

Replace the `tailwind_acf_enqueue_block_editor_assets` function (lines 147-210). The block editor still needs the CDN for live Tailwind class generation in the editor preview:

```php
function tailwind_acf_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'tailwind-acf-editor-cdn',
		'https://cdn.tailwindcss.com?plugins=forms,typography',
		array(),
		null,
		false
	);

	$carousel_js = get_template_directory() . '/assets/js/carousel.js';
	if ( file_exists( $carousel_js ) ) {
		wp_enqueue_script(
			'tailwind-acf-carousel',
			get_template_directory_uri() . '/assets/js/carousel.js',
			array(),
			filemtime( $carousel_js ),
			true
		);
	}

	$hero_parallax_js = get_template_directory() . '/assets/js/hero-parallax.js';
	if ( file_exists( $hero_parallax_js ) ) {
		wp_enqueue_script(
			'tailwind-acf-hero-parallax',
			get_template_directory_uri() . '/assets/js/hero-parallax.js',
			array(),
			filemtime( $hero_parallax_js ),
			true
		);
	}

	$editor_css = get_template_directory() . '/assets/css/editor.css';
	if ( file_exists( $editor_css ) ) {
		wp_enqueue_style(
			'tailwind-acf-editor-style',
			get_template_directory_uri() . '/assets/css/editor.css',
			array(),
			filemtime( $editor_css )
		);
	}

	$config = 'tailwind.config = window.tailwindConfig ?? ' . wp_json_encode(
		array(
			'theme' => array(
				'extend' => array(
					'fontFamily' => array(
						'sans' => array( 'Inter', 'ui-sans-serif', 'system-ui' ),
					),
					'colors' => array(
						'brand' => array(
							'DEFAULT' => '#2563eb',
							'light'   => '#60a5fa',
							'dark'    => '#1d4ed8',
						),
					),
				),
			),
		)
	) . ';';

	wp_add_inline_script( 'tailwind-acf-editor-cdn', $config, 'before' );
}
```

**Step 9: Update `header.php`**

Remove the inline `<style>` block (lines 50-170) — these CSS rules now live in `src/main.css`.

Keep the inline `<script>` for dropdown JS (lines 171-224) — it's small, depends on DOM, and doesn't need bundling.

**Step 10: Update `.gitignore`**

Add `node_modules/` to the repo root `.gitignore`. Do NOT gitignore `dist/` since we want to commit built CSS.

**Step 11: Build, verify, and commit**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
```

Verify: The site should look identical. Check that all Tailwind classes render correctly (nav links, dropdowns, page layouts, form styling).

```bash
git add wp-content/themes/tailwind-acf/package.json \
        wp-content/themes/tailwind-acf/tailwind.config.js \
        wp-content/themes/tailwind-acf/vite.config.js \
        wp-content/themes/tailwind-acf/postcss.config.js \
        wp-content/themes/tailwind-acf/src/main.css \
        wp-content/themes/tailwind-acf/dist/ \
        wp-content/themes/tailwind-acf/functions.php \
        wp-content/themes/tailwind-acf/header.php \
        .gitignore
git commit -m "Add Vite + Tailwind build step, replace CDN with compiled CSS"
```

---

## Task 4: Add mobile navigation

**Files:**
- Modify: `wp-content/themes/tailwind-acf/header.php`
- Modify: `wp-content/themes/tailwind-acf/src/main.css`

**Step 1: Add hamburger button and mobile drawer to `header.php`**

After the closing `</nav>` tag (around line 255 in the current version), add the hamburger button inside the header `<div>` (before the closing `</div>` of the inner wrapper):

```php
<!-- Mobile menu button -->
<button
	type="button"
	class="sm:hidden inline-flex items-center justify-center rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/20"
	aria-label="<?php esc_attr_e( 'Open menu', 'tailwind-acf' ); ?>"
	aria-expanded="false"
	id="mobile-menu-button"
>
	<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
		<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
	</svg>
</button>
```

After the `</header>` tag, add the drawer overlay and panel:

```php
<!-- Mobile navigation drawer -->
<div id="mobile-menu-overlay" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
	<div class="mobile-menu-backdrop fixed inset-0 bg-black/50" id="mobile-menu-backdrop"></div>
	<nav class="mobile-menu-panel fixed inset-y-0 right-0 w-full max-w-xs bg-green-950 px-6 py-6 shadow-xl overflow-y-auto">
		<div class="flex items-center justify-between mb-8">
			<?php if ( has_site_icon() ) : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img class="h-8 w-auto" src="<?php echo esc_url( get_site_icon_url( 192 ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>
			<?php else : ?>
				<a class="text-lg font-semibold text-white" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
			<button
				type="button"
				class="inline-flex items-center justify-center rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white"
				aria-label="<?php esc_attr_e( 'Close menu', 'tailwind-acf' ); ?>"
				id="mobile-menu-close"
			>
				<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</button>
		</div>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'menu_class'     => 'mobile-nav-menu space-y-1',
				'fallback_cb'    => false,
				'container'      => false,
			)
		);
		?>
	</nav>
</div>
```

**Step 2: Add mobile nav CSS to `src/main.css`**

```css
/* Mobile navigation drawer */
.mobile-menu-backdrop {
  opacity: 0;
  transition: opacity 0.3s ease;
}

.mobile-menu-panel {
  transform: translateX(100%);
  transition: transform 0.3s ease;
}

#mobile-menu-overlay.is-open .mobile-menu-backdrop {
  opacity: 1;
}

#mobile-menu-overlay.is-open .mobile-menu-panel {
  transform: translateX(0);
}

@media (prefers-reduced-motion: reduce) {
  .mobile-menu-backdrop,
  .mobile-menu-panel {
    transition: none;
  }
}

/* Mobile nav menu styles */
.mobile-nav-menu a {
  display: block;
  padding: 0.75rem 0;
  color: #fff;
  font-size: 1rem;
  font-weight: 500;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  transition: color 0.15s ease;
}

.mobile-nav-menu a:hover {
  color: #fde68a;
}

.mobile-nav-menu .current-menu-item > a {
  color: #fde68a;
}

.mobile-nav-menu .sub-menu {
  padding-left: 1rem;
}

.mobile-nav-menu .sub-menu a {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.7);
  border-bottom-color: rgba(255, 255, 255, 0.05);
}

.mobile-nav-menu .sub-menu a:hover {
  color: #fde68a;
}

/* Accordion chevron for mobile dropdowns */
.mobile-nav-menu .has-dropdown > a {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.mobile-nav-menu .has-dropdown > .sub-menu {
  display: none;
}

.mobile-nav-menu .has-dropdown.is-open > .sub-menu {
  display: block;
}
```

**Step 3: Add mobile menu JS to `header.php`**

Add to the existing `<script>` block in `header.php`, after the existing dropdown code:

```javascript
// Mobile menu
var mobileOverlay = document.getElementById('mobile-menu-overlay');
var mobileButton = document.getElementById('mobile-menu-button');
var mobileClose = document.getElementById('mobile-menu-close');
var mobileBackdrop = document.getElementById('mobile-menu-backdrop');

function openMobileMenu() {
  mobileOverlay.classList.remove('hidden');
  // Force reflow for transition
  mobileOverlay.offsetHeight;
  mobileOverlay.classList.add('is-open');
  mobileButton.setAttribute('aria-expanded', 'true');
  document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
  mobileOverlay.classList.remove('is-open');
  mobileButton.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
  setTimeout(function() {
    mobileOverlay.classList.add('hidden');
  }, 300);
}

if (mobileButton) {
  mobileButton.addEventListener('click', openMobileMenu);
}
if (mobileClose) {
  mobileClose.addEventListener('click', closeMobileMenu);
}
if (mobileBackdrop) {
  mobileBackdrop.addEventListener('click', closeMobileMenu);
}

// Escape key closes mobile menu too
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' && mobileOverlay && !mobileOverlay.classList.contains('hidden')) {
    closeMobileMenu();
  }
});

// Mobile accordion submenus
var mobileDropdowns = document.querySelectorAll('.mobile-nav-menu .has-dropdown');
mobileDropdowns.forEach(function(dropdown) {
  var link = dropdown.querySelector(':scope > a');
  link.addEventListener('click', function(e) {
    e.preventDefault();
    dropdown.classList.toggle('is-open');
  });
});
```

**Step 4: Rebuild CSS and commit**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
git add wp-content/themes/tailwind-acf/header.php \
        wp-content/themes/tailwind-acf/src/main.css \
        wp-content/themes/tailwind-acf/dist/
git commit -m "Add mobile hamburger menu with slide-out drawer"
```

---

## Task 5: Redesign the dashboard

**Files:**
- Modify: `wp-content/themes/tailwind-acf/page-dashboard.php`

**Step 1: Rewrite `page-dashboard.php`**

The entire template needs rewriting. Key changes:
- Add member-since date to welcome header
- Replace table with responsive card grid
- Add pagination (12 per page) using `paged` GET parameter
- Improve notification styling with left-border cards
- Keep the payment info panel and empty state (already good, just refine)

The PHP logic at the top stays nearly the same, but the query changes to support pagination:

Replace `'posts_per_page' => -1` (line 66) with:
```php
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$cattle_page = isset( $_GET['cattle_page'] ) ? max( 1, absint( $_GET['cattle_page'] ) ) : 1;
$per_page    = 12;
```

And the query becomes:
```php
$user_query = new WP_Query(
    array(
        'post_type'      => 'cattle_registration',
        'author'         => $current_user->ID,
        'post_status'    => array( 'pending', 'publish' ),
        'posts_per_page' => $per_page,
        'paged'          => $cattle_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    )
);
$user_registrations = $user_query->posts;
$total_registrations = $user_query->found_posts;
$total_pages = $user_query->max_num_pages;
```

For the card grid, replace the `<table>` section with:

```php
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ( $user_registrations as $registration ) : ?>
        <?php
        $calf_name = get_field( 'calf_name', $registration->ID );
        $tattoo    = get_field( 'tattoo_number', $registration->ID );
        $sex       = get_field( 'sex', $registration->ID );
        $grade     = get_field( 'grade', $registration->ID );
        $colour    = get_field( 'colour', $registration->ID );
        $dob       = get_field( 'date_of_birth', $registration->ID );
        $sex_labels    = tailwind_cattle_get_sex_labels();
        $colour_labels = tailwind_cattle_get_colour_labels();
        ?>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-slate-900 truncate">
                        <?php echo esc_html( $calf_name ); ?>
                    </h3>
                    <p class="text-sm font-mono text-slate-500">
                        <?php echo esc_html( $tattoo ); ?>
                    </p>
                </div>
                <?php
                if ( function_exists( 'tailwind_get_cattle_status_badge' ) ) {
                    echo tailwind_get_cattle_status_badge( $registration->post_status );
                }
                ?>
            </div>
            <dl class="grid grid-cols-2 gap-2 text-sm mb-4">
                <div>
                    <dt class="text-slate-500"><?php esc_html_e( 'Grade', 'tailwind-acf' ); ?></dt>
                    <dd class="font-medium text-slate-900"><?php echo esc_html( $grade ); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php esc_html_e( 'Sex', 'tailwind-acf' ); ?></dt>
                    <dd class="font-medium text-slate-900"><?php echo esc_html( $sex_labels[ $sex ] ?? $sex ); ?></dd>
                </div>
                <?php if ( $colour ) : ?>
                <div>
                    <dt class="text-slate-500"><?php esc_html_e( 'Colour', 'tailwind-acf' ); ?></dt>
                    <dd class="font-medium text-slate-900"><?php echo esc_html( $colour_labels[ $colour ] ?? $colour ); ?></dd>
                </div>
                <?php endif; ?>
                <?php if ( $dob ) : ?>
                <div>
                    <dt class="text-slate-500"><?php esc_html_e( 'Born', 'tailwind-acf' ); ?></dt>
                    <dd class="font-medium text-slate-900"><?php echo esc_html( date_i18n( 'M Y', strtotime( $dob ) ) ); ?></dd>
                </div>
                <?php endif; ?>
            </dl>
            <div class="flex gap-3 text-sm font-medium">
                <?php if ( 'publish' === $registration->post_status ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $registration ) ); ?>" class="text-brand hover:text-brand-dark transition">
                        <?php esc_html_e( 'View', 'tailwind-acf' ); ?>
                    </a>
                <?php elseif ( 'pending' === $registration->post_status && $register_cattle_url ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'edit', $registration->ID, $register_cattle_url ) ); ?>" class="text-brand hover:text-brand-dark transition">
                        <?php esc_html_e( 'Edit', 'tailwind-acf' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
```

Add pagination after the grid if there are multiple pages:
```php
<?php if ( $total_pages > 1 ) : ?>
    <nav class="mt-8 flex justify-center gap-2" aria-label="<?php esc_attr_e( 'Pagination', 'tailwind-acf' ); ?>">
        <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
            <?php
            $page_url = add_query_arg( 'cattle_page', $i, get_permalink() );
            $is_current = ( $i === $cattle_page );
            ?>
            <a
                href="<?php echo esc_url( $page_url ); ?>"
                class="inline-flex h-10 min-w-[2.5rem] items-center justify-center rounded-lg border px-3 text-sm font-medium transition <?php echo $is_current ? 'bg-green-700 border-green-700 text-white' : 'border-slate-200 text-slate-700 hover:bg-slate-50'; ?>"
                <?php if ( $is_current ) : ?>aria-current="page"<?php endif; ?>
            >
                <?php echo esc_html( $i ); ?>
            </a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
```

Also update the welcome header to include member-since date:
```php
<p class="mt-1 text-base text-slate-600">
    <?php
    printf(
        /* translators: 1: display name, 2: registration date */
        esc_html__( 'Welcome back, %1$s. Member since %2$s.', 'tailwind-acf' ),
        esc_html( $current_user->display_name ),
        esc_html( date_i18n( 'F Y', strtotime( $current_user->user_registered ) ) )
    );
    ?>
</p>
```

Update notification styling — replace full-width blocks with left-border cards:

Success notifications:
```php
<div class="mb-6 rounded-lg border-l-4 border-green-500 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-start gap-3">
```

Warning notifications:
```php
<div class="mb-6 rounded-lg border-l-4 border-yellow-500 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 flex items-start gap-3">
```

**Step 2: Rebuild CSS and commit**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
git add wp-content/themes/tailwind-acf/page-dashboard.php \
        wp-content/themes/tailwind-acf/dist/
git commit -m "Redesign dashboard with card grid, pagination, and improved notifications"
```

---

## Task 6: Polish the cattle registration form

**Files:**
- Modify: `wp-content/themes/tailwind-acf/page-register-cattle.php`

**Step 1: Add submit button loading state**

Add a small inline `<script>` at the bottom of the template (before `get_footer()`) for the loading state:

```php
<script>
document.addEventListener('DOMContentLoaded', function() {
	var form = document.querySelector('#primary form');
	var submitBtn = form ? form.querySelector('button[type="submit"]') : null;
	if (form && submitBtn) {
		var originalText = submitBtn.textContent.trim();
		form.addEventListener('submit', function() {
			submitBtn.disabled = true;
			submitBtn.textContent = '<?php echo esc_js( __( 'Submitting...', 'tailwind-acf' ) ); ?>';
			submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
		});
	}
});
</script>
```

**Step 2: Improve edit mode indicator**

Replace the existing edit mode header text (around lines 303-314). When in edit mode, add a prominent banner before the form:

After the `<?php if ( ! empty( $errors ) ) : ?>` error block, add:
```php
<?php if ( $is_edit_mode ) : ?>
    <div class="mb-6 rounded-lg border-l-4 border-blue-500 bg-blue-50 px-4 py-3 text-sm text-blue-800 flex items-center justify-between">
        <span>
            <?php
            printf(
                /* translators: 1: calf name, 2: tattoo number */
                esc_html__( 'Editing: %1$s (%2$s)', 'tailwind-acf' ),
                esc_html( tailwind_cattle_old_value( 'calf_name', $form_data ) ),
                esc_html( tailwind_cattle_old_value( 'tattoo_number', $form_data ) )
            );
            ?>
        </span>
        <a href="<?php echo esc_url( tailwind_member_get_dashboard_url() ); ?>" class="font-medium text-blue-700 hover:text-blue-900 transition">
            <?php esc_html_e( 'Cancel', 'tailwind-acf' ); ?>
        </a>
    </div>
<?php endif; ?>
```

**Step 3: Improve validation styling**

Update the `tailwind_cattle_field_error_class` helper to include a background tint on error:

```php
function tailwind_cattle_field_error_class( $field, $field_errors ) {
    return isset( $field_errors[ $field ] )
        ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500'
        : 'border-slate-300 focus:border-brand focus:ring-brand';
}
```

**Step 4: Rebuild CSS and commit**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
git add wp-content/themes/tailwind-acf/page-register-cattle.php \
        wp-content/themes/tailwind-acf/dist/
git commit -m "Polish cattle registration form with loading state and improved validation"
```

---

## Task 7: Improve animal search UX

**Files:**
- Modify: `wp-content/themes/tailwind-acf/page-animal-search.php`

**Step 1: Minor UX improvements**

The search page is already quite good. The main improvements:

1. Show result count even when no filters are active (show total count)
2. Improve mobile stacking of filter grid

For the result count, change the condition (around line 361) from `<?php if ( $has_filters ) : ?>` to always show:

```php
<div class="mb-6 flex items-center justify-between">
    <p class="text-sm text-slate-600">
        <?php if ( $has_filters ) : ?>
            <?php
            printf(
                esc_html( _n( '%d result found', '%d results found', $cattle_query->found_posts, 'tailwind-acf' ) ),
                esc_html( $cattle_query->found_posts )
            );
            ?>
        <?php else : ?>
            <?php
            printf(
                esc_html( _n( '%d animal registered', '%d animals registered', $cattle_query->found_posts, 'tailwind-acf' ) ),
                esc_html( $cattle_query->found_posts )
            );
            ?>
        <?php endif; ?>
    </p>
</div>
```

**Step 2: Rebuild CSS and commit**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
git add wp-content/themes/tailwind-acf/page-animal-search.php \
        wp-content/themes/tailwind-acf/dist/
git commit -m "Show total result count on animal search page"
```

---

## Task 8: Polish single cattle record

**Files:**
- Modify: `wp-content/themes/tailwind-acf/single-cattle_registration.php`

**Step 1: Add grade badge to header**

In the header section, alongside the status badge, add a grade badge. After the status badge div (around line 93-98), add a grade pill:

Within the `<div class="flex items-start justify-between gap-4">`, modify the right side to show both badges:

```php
<div class="flex items-center gap-2 mt-1">
    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
        <?php echo esc_html( $grade_labels[ $grade ] ?? $grade ); ?>
    </span>
    <?php if ( function_exists( 'tailwind_get_cattle_status_badge' ) ) : ?>
        <?php echo tailwind_get_cattle_status_badge( get_post_status() ); ?>
    <?php endif; ?>
</div>
```

**Step 2: Add contextual back link**

Replace the simple "Back to Dashboard" link with a contextual one. Before the existing nav (around line 72-81):

```php
<nav class="mb-4">
    <?php
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
    $referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
    $search_page = get_page_by_path( 'animal-search' );
    $search_url  = $search_page ? get_permalink( $search_page ) : '';
    $is_from_search = $search_url && $referer && strpos( $referer, $search_url ) !== false;
    ?>
    <?php if ( $is_from_search ) : ?>
        <a href="<?php echo esc_url( $referer ); ?>" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-brand transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <?php esc_html_e( 'Back to Search', 'tailwind-acf' ); ?>
        </a>
    <?php elseif ( is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( tailwind_member_get_dashboard_url() ); ?>" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-brand transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <?php esc_html_e( 'Back to Dashboard', 'tailwind-acf' ); ?>
        </a>
    <?php endif; ?>
</nav>
```

**Step 3: Commit**

```bash
git add wp-content/themes/tailwind-acf/single-cattle_registration.php
git commit -m "Add grade badge and contextual back navigation to cattle record"
```

---

## Task 9: SEO — Breadcrumbs

**Files:**
- Modify: `wp-content/themes/tailwind-acf/functions.php`
- Modify: `wp-content/themes/tailwind-acf/single-cattle_registration.php`
- Modify: `wp-content/themes/tailwind-acf/single.php`
- Modify: `wp-content/themes/tailwind-acf/page-animal-search.php`
- Modify: `wp-content/themes/tailwind-acf/page-dashboard.php`

**Step 1: Add breadcrumb helper to `functions.php`**

Add a wrapper function that uses Yoast's breadcrumbs if available:

```php
/**
 * Render breadcrumb navigation.
 */
function tailwind_render_breadcrumbs() {
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb(
			'<nav class="mb-4 text-sm text-slate-500" aria-label="' . esc_attr__( 'Breadcrumb', 'tailwind-acf' ) . '">',
			'</nav>'
		);
	}
}
```

**Step 2: Add breadcrumbs to templates**

In each template, call `tailwind_render_breadcrumbs()` at the top of the content area, replacing or supplementing the existing back-navigation links. For example, in `single-cattle_registration.php`, add it inside the `<header>` before the nav:

```php
<?php tailwind_render_breadcrumbs(); ?>
```

Add similarly to `single.php`, `page-animal-search.php` (inside the header div), and `page-dashboard.php` (after the `<header>` opening).

**Step 3: Rebuild and commit**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
git add wp-content/themes/tailwind-acf/functions.php \
        wp-content/themes/tailwind-acf/single-cattle_registration.php \
        wp-content/themes/tailwind-acf/single.php \
        wp-content/themes/tailwind-acf/page-animal-search.php \
        wp-content/themes/tailwind-acf/page-dashboard.php \
        wp-content/themes/tailwind-acf/dist/
git commit -m "Add Yoast breadcrumb navigation to interior pages"
```

---

## Task 10: SEO — Cattle record structured data

**Files:**
- Modify: `wp-content/themes/tailwind-acf/inc/cattle-registration.php`

**Step 1: Add JSON-LD structured data hook**

Add a function hooked to `wp_head` that outputs JSON-LD for single cattle records:

```php
/**
 * Output JSON-LD structured data for cattle registration pages.
 */
function tailwind_cattle_structured_data() {
	if ( ! is_singular( 'cattle_registration' ) ) {
		return;
	}

	$post_id   = get_the_ID();
	$calf_name = get_field( 'calf_name', $post_id );
	$tattoo    = get_field( 'tattoo_number', $post_id );
	$grade     = get_field( 'grade', $post_id );
	$sex       = get_field( 'sex', $post_id );
	$dob       = get_field( 'date_of_birth', $post_id );

	$grade_labels = tailwind_cattle_get_grade_labels();
	$sex_labels   = tailwind_cattle_get_sex_labels();

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Thing',
		'name'        => $calf_name,
		'identifier'  => $tattoo,
		'description' => sprintf(
			/* translators: 1: grade, 2: sex, 3: breed */
			__( '%1$s %2$s Murray Grey cattle', 'tailwind-acf' ),
			$grade_labels[ $grade ] ?? $grade,
			$sex_labels[ $sex ] ?? $sex
		),
		'url'         => get_permalink( $post_id ),
	);

	if ( $dob ) {
		$schema['dateCreated'] = $dob;
	}

	$author = get_userdata( get_post_field( 'post_author', $post_id ) );
	if ( $author ) {
		$schema['creator'] = array(
			'@type' => 'Person',
			'name'  => $author->display_name,
		);
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'tailwind_cattle_structured_data' );
```

**Step 2: Commit**

```bash
git add wp-content/themes/tailwind-acf/inc/cattle-registration.php
git commit -m "Add JSON-LD structured data for cattle registration pages"
```

---

## Task 11: SEO — Performance (lazy loading, preconnect, font optimization)

**Files:**
- Modify: `wp-content/themes/tailwind-acf/header.php`
- Modify: `wp-content/themes/tailwind-acf/functions.php`

**Step 1: Add preconnect hints and font-display to `header.php`**

Inside `<head>`, before `<?php wp_head(); ?>`, add:

```php
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://www.googletagmanager.com">
```

If Inter is loaded via Google Fonts, ensure the link includes `&display=swap`. If it's loaded via the Tailwind CDN's font-family declaration only (system fallback), just keep the preconnects for GA.

**Step 2: Add lazy loading to images in templates**

WordPress 5.5+ adds `loading="lazy"` to images automatically via `wp_get_attachment_image()`. For manually constructed `<img>` tags (like the hero background), the hero image should NOT be lazy-loaded (it's above the fold). Other images (carousel, blog thumbnails) already use WP functions that add it automatically.

In `functions.php`, add a filter to skip lazy loading on hero images:

```php
/**
 * Skip lazy loading for hero background images.
 *
 * @param string $value   The loading attribute value.
 * @param string $image   The HTML img tag.
 * @param string $context Additional context.
 * @return string
 */
add_filter( 'wp_img_tag_add_loading_attr', function ( $value, $image, $context ) {
	if ( strpos( $image, 'data-parallax-speed' ) !== false ) {
		return false;
	}
	return $value;
}, 10, 3 );
```

**Step 3: Rebuild and commit**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
git add wp-content/themes/tailwind-acf/header.php \
        wp-content/themes/tailwind-acf/functions.php \
        wp-content/themes/tailwind-acf/dist/
git commit -m "Add preconnect hints and optimize image loading"
```

---

## Task 12: SEO — Open Graph default image for cattle records

**Files:**
- Modify: `wp-content/themes/tailwind-acf/functions.php`

**Step 1: Hook into Yoast's OG image filter**

```php
/**
 * Set a default Open Graph image for cattle registrations.
 *
 * @param array $image OG image array from Yoast.
 * @return array
 */
function tailwind_cattle_og_image( $image ) {
	if ( ! is_singular( 'cattle_registration' ) ) {
		return $image;
	}

	// If Yoast already found an image, keep it.
	if ( ! empty( $image ) ) {
		return $image;
	}

	// Use the site icon as fallback OG image.
	$icon_url = get_site_icon_url( 512 );
	if ( $icon_url ) {
		return $icon_url;
	}

	return $image;
}
add_filter( 'wpseo_opengraph_image', 'tailwind_cattle_og_image' );
```

**Step 2: Commit**

```bash
git add wp-content/themes/tailwind-acf/functions.php
git commit -m "Add default Open Graph image for cattle registration pages"
```

---

## Task 13: Final rebuild, mobile responsiveness audit, and verification

**Files:**
- Potentially any template file needing responsive tweaks.

**Step 1: Full CSS rebuild**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
```

**Step 2: Manual QA checklist**

Test each page at mobile (375px), tablet (768px), and desktop (1280px) viewports:

- [ ] Homepage — hero renders, carousel works, latest news grid stacks
- [ ] Mobile nav — hamburger appears, drawer opens/closes, accordion submenus work, escape key closes
- [ ] Dashboard — card grid stacks to 1 column on mobile, pagination works, notifications display correctly
- [ ] Register Cattle — form sections stack properly, two-column fields collapse to single column, submit loading state works
- [ ] Animal Search — filters stack vertically on mobile, result cards stack, pagination works
- [ ] Single cattle record — info cards stack, badges display, print styles work (Ctrl+P), back link is contextual
- [ ] Blog archive — featured post and grid display correctly
- [ ] Single post — hero header and prose content are readable on mobile
- [ ] Footer — columns stack on mobile, social icons display
- [ ] Breadcrumbs — display on interior pages (requires Yoast breadcrumbs enabled in Yoast settings)

**Step 3: Fix any responsive issues discovered and rebuild**

**Step 4: Final commit**

```bash
cd wp-content/themes/tailwind-acf && npm run build && cd ../../..
git add wp-content/themes/tailwind-acf/dist/
git commit -m "Final CSS build after UI polish and SEO improvements"
```

---

## Summary of all commits

1. `Extract shared cattle option arrays to inc/cattle-registration.php`
2. `Fix hero parallax effect and home.php pagination bug`
3. `Add Vite + Tailwind build step, replace CDN with compiled CSS`
4. `Add mobile hamburger menu with slide-out drawer`
5. `Redesign dashboard with card grid, pagination, and improved notifications`
6. `Polish cattle registration form with loading state and improved validation`
7. `Show total result count on animal search page`
8. `Add grade badge and contextual back navigation to cattle record`
9. `Add Yoast breadcrumb navigation to interior pages`
10. `Add JSON-LD structured data for cattle registration pages`
11. `Add preconnect hints and optimize image loading`
12. `Add default Open Graph image for cattle registration pages`
13. `Final CSS build after UI polish and SEO improvements`
