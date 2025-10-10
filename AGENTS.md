# Repository Guidelines

## Project Structure & Module Organization
- Core WordPress files live in the repository root; avoid editing them so updates remain painless.
- Custom theme work resides in `wp-content/themes/tailwind-acf`, with shared logic and block registration under `inc/`.
- Advanced Custom Fields block definitions are maintained in `wp-content/themes/tailwind-acf/inc/acf-blocks.php`; pair updates there with matching templates in `template-parts/blocks/`.
- Bundled plugins sit in `wp-content/plugins/` (e.g., `advanced-custom-fields-pro`, `akismet`); treat vendor updates as isolated commits.
- Media uploads and generated assets belong in `wp-content/uploads/`; keep Git clean by ignoring large binaries and environment-specific files.

## Build, Test, and Development Commands
- `wp server --docroot=$(pwd)`: start a local development server that boots this checkout directly.
- `wp theme activate tailwind-acf`: ensure the custom theme is active before QA.
- `wp plugin activate advanced-custom-fields-pro`: enable required fields for custom blocks after migrations or fresh installs.
- `wp option update show_on_front page`: mirror production reading settings when reproducing homepage issues.

## Coding Style & Naming Conventions
- Follow WordPress PHP standards: tabs for indentation, opening braces on the same line, and snake_case function names (`tailwind_acf_enqueue_frontend_assets`).
- Localize user-facing strings with `__()` or `_e()` and the `tailwind-acf` text domain.
- Prefix block names, ACF field keys, and custom filters with `tailwind_` to avoid collisions.
- Keep template partials slim; move logic into `inc/` and pass prepared data to templates.

## Testing Guidelines
- No automated suite is bundled; rely on manual verification in wp-admin by inserting the updated block in both the block editor and front-end preview.
- Enable debug flags in `wp-config.php` (`WP_DEBUG`, `SCRIPT_DEBUG`) during development and capture notices before submitting.
- After structural changes, clear any persistent caches (`wp cache flush`) and re-sync ACF JSON if applicable.

## Commit & Pull Request Guidelines
- Use concise, imperative commit titles (`Add Tailwind hero secondary CTA`) as seen in recent history.
- Group unrelated changes into separate commits (theme templates vs. plugin updates).
- Pull requests should outline the intent, affected templates or blocks, manual test steps, and attach screenshots or screen recordings for visual updates.
- Link Jira/GitHub issues when available and note any required database or ACF field migrations.

## Security & Configuration Tips
- Store environment secrets outside of Git; base new environments on `wp-config-sample.php` and keep machine-specific configs in `.env` or local overrides.
- Set `DISALLOW_FILE_EDIT` and strong salts in `wp-config.php` for shared environments.
- Review third-party plugin updates in a staging site before merging to main.
