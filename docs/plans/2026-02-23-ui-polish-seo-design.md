# MGAA Theme — UI Polish & SEO Improvements Design

**Date:** 2026-02-23
**Author:** Claude (brainstorming session with Dan)
**Status:** Approved

## Overview

Comprehensive UI polish, mobile responsiveness, and SEO improvements for the MGAA WordPress theme. Covers member-facing and public-facing pages equally.

## Scope

1. Mobile navigation (hamburger menu)
2. Dashboard redesign (card grid, pagination, status badges)
3. Cattle registration form polish (sections, grid layout, validation UX)
4. Animal search UX (filter bar, result count, empty state)
5. Single cattle record polish (hero header, info cards, print styles)
6. Build step (Vite + Tailwind compiled CSS)
7. Bug fixes (parallax, pagination, duplicate options)
8. SEO (structured data, breadcrumbs, OG images, performance)

## Constraints

- Keep hardcoded values (bank details, GA ID, addresses) — single-site theme
- Commit built CSS to git (no build step required on deploy)
- Server-side form handling stays as-is (no AJAX)
- No new plugin dependencies

---

## 1. Mobile Navigation

**Problem:** Nav is `hidden sm:block` — invisible on mobile. Site is unnavigable on phones.

**Solution:**
- Hamburger button (3-line icon) visible on mobile (`sm:hidden`), right side of header
- Full-height slide-out drawer from right edge with backdrop overlay
- Primary menu rendered as vertical stack inside drawer
- Dropdown submenus expand inline with accordion pattern (tap parent → children slide down)
- Desktop dropdown nav unchanged
- Login/Register or Dashboard/Logout links at bottom of drawer (reuses existing `members.php` swap logic)
- Close via X button, backdrop tap, or Escape key
- Smooth CSS transition (slide + fade) with `prefers-reduced-motion` support
- Vanilla JS matching existing patterns in `header.php`

**Files affected:** `header.php`, possibly `inc/class-dropdown-nav-walker.php`

---

## 2. Dashboard Redesign

**Problem:** Plain HTML table, no pagination, basic notification banners. Functional but unpolished.

**Solution:**
- **Header:** Welcome message with display name, member-since date, prominent green "Register Cattle" CTA button with `+` icon
- **Notifications:** Rounded cards with left-color-border (green=success, amber=pending, red=denied) instead of full-width blocks
- **Cattle grid:** Replace table with responsive card grid (1 col mobile, 2 col tablet, 3 col desktop). Each card shows:
  - Name + tattoo as title
  - Status badge (colored pill)
  - Key details (grade, sex, colour, DOB) in 2×2 grid
  - Action links: "View" (always), "Edit" (if pending)
- **Empty state:** Icon + "No cattle registered yet" message with CTA to register first animal
- **Pagination:** 12 per page instead of `posts_per_page => -1`
- **Payment panel:** Restyle as card matching design language. Only shown when pending registrations exist.

**Files affected:** `page-dashboard.php`

---

## 3. Cattle Registration Form Polish

**Problem:** Single-column, no visual sections, basic error styling. Functional but plain.

**Solution:**
- **Card sections:** Form split into visually distinct cards with headers:
  - Calf Information (name, grade, year letter, tattoo)
  - Birth Details (DOB, weight, sex, colour, calving ease)
  - Flags (A.I., E.T., Twin — horizontal toggle row)
  - Parentage (sire name/tattoo, dam name/tattoo)
- **Responsive grid:** 2-column for related fields on desktop, single column on mobile
- **Help text:** Subtle guidance beneath tricky fields (e.g., year letter exclusions)
- **Validation styling:** Red left-border + background tint on error fields, red text below
- **Submit button:** Loading/disabled state ("Submitting...") to prevent double-submission
- **Edit mode:** Banner at top: "Editing: [Name] ([Tattoo])" with cancel link to dashboard
- Server-side validation unchanged

**Files affected:** `page-register-cattle.php`

---

## 4. Animal Search UX

**Problem:** Basic filter row, no result count, no empty state feedback, basic pagination.

**Solution:**
- **Filter bar:** Wrap in card/panel with subtle background. Stack vertically on mobile. Add "Clear filters" link.
- **Result count:** "Showing X of Y results" above grid (or "No results found" with active filters listed)
- **Result cards:** Add hover effect (subtle shadow lift). Each shows: name, tattoo, grade pill, sex, colour, "View" link.
- **Empty state:** Friendly message with suggestions when no results match
- **Pagination:** Pill-style buttons with active/disabled states

**Files affected:** `page-animal-search.php`

---

## 5. Single Cattle Record Polish

**Problem:** Plain definition lists, basic layout. Functional but visually sparse.

**Solution:**
- **Hero header:** Cattle name + tattoo as prominent heading, status badge + grade badge inline
- **Info cards:** Each section (Registration, Birth, Parentage) as a distinct card with border. Fields in 2-column key-value grid.
- **Print styles:** `@media print` rules — hide nav/footer, clean typography, fit on one page
- **Back navigation:** Contextual — "Back to Dashboard" for logged-in users, "Back to Search" if from search

**Files affected:** `single-cattle_registration.php`

---

## 6. Build Step — Vite + Tailwind

**Problem:** Tailwind loaded from CDN at runtime (~300KB parsed in browser). Not recommended for production.

**Solution:**
- Vite + Tailwind CSS + PostCSS
- `src/main.css` with Tailwind directives + any custom CSS (inline nav styles from header.php move here)
- `tailwind.config.js` with Inter font, brand colors (replaces inline config script)
- `dist/style.css` built output — committed to git
- `npm run dev` for watch mode, `npm run build` for production
- `functions.php` enqueues `dist/style.css` instead of CDN script
- Remove CDN `<script>` and inline Tailwind config from header
- Expected CSS size: ~15-25KB gzipped (down from ~300KB)

**New files:**
```
tailwind-acf/
  src/main.css
  tailwind.config.js
  vite.config.js
  package.json
  dist/style.css  (committed)
```

**Files affected:** `functions.php`, `header.php`

---

## 7. Bug Fixes & Code Quality

### 7a. Broken parallax
`hero-parallax.js` sets `--parallax-y` CSS property but hero image has no CSS that uses it. Add `style="transform: translateY(var(--parallax-y, 0))"` to the hero `<img>` in `template-parts/blocks/hero.php`.

### 7b. home.php pagination bug
`$rest_query` uses `offset` which bypasses WordPress sticky post and pagination logic. Replace with `post__not_in => [$featured_id]`.

### 7c. Duplicate option arrays
Grade, sex, colour, calving ease arrays defined in 5 files. Extract to shared functions in `inc/cattle-registration.php`:
- `tailwind_cattle_get_grade_options()`
- `tailwind_cattle_get_sex_options()`
- `tailwind_cattle_get_colour_options()`
- `tailwind_cattle_get_calving_ease_options()`

Call these from all templates and the ACF field definitions.

### 7d. Mobile responsiveness audit
Review all templates for mobile breakpoints. Ensure tables, grids, forms stack on small screens.

---

## 8. SEO Improvements

### 8a. Cattle record structured data
Add JSON-LD on `single-cattle_registration.php` using appropriate schema.org types. Include: name, identifier (tattoo), breed information, sex, date of birth. Hook into `wp_head` or output inline in the template.

### 8b. Breadcrumb navigation
Add breadcrumb trail on interior pages:
- Home > Dashboard > [Page]
- Home > Cattle > [Cattle Name]
- Home > News > [Post Title]

Use Yoast's `yoast_breadcrumb()` if available, otherwise lightweight custom implementation. Provides both visual navigation and structured data.

### 8c. Open Graph images
Hook into Yoast's `wpseo_opengraph_image` filter for cattle records. Use a default MGAA image so shared links have thumbnails on social media.

### 8d. Performance
- Lazy load images below the fold (`loading="lazy"`)
- Add `<link rel="preconnect">` for Google Fonts and GA
- Self-host Inter font or add `font-display: swap`
- Tailwind build step provides the biggest performance win (Section 6)

---

## Out of Scope

- AJAX form submission (keep server-side POST)
- Denormalized search index
- HTML email templates
- Moving form processing to `admin-post.php` handlers
- Configurable settings (hardcoded values stay)
- New plugin dependencies
