## Scope
- Add gated login/registration, manual approval flow, and members-only dashboard to tailwind-acf theme.
- Store downloadable assets for members and ensure files remain protected from unauthenticated requests.

## Recommended Membership Plugins
- **Paid Memberships Pro + Approvals Add On**: Mature codebase, supports flexible membership levels, site-wide restrictions, and admin approval queues out of the box.
- **Ultimate Member**: Free core plugin with registration forms, profile management, and manual approval workflows; integrates easily with custom roles and content restrictions.
- **Restrict Content Pro** (optional paid alternative): Lightweight roles/capabilities management, reliable download protection, and simple developer API for theme integration.

## Implementation Checklist
- **Plugin Setup**
  - Install and activate the chosen membership plugin.
  - Create custom roles (e.g. `mgaa_member`, `mgaa_manager`) through the plugin or user role editor, defining capabilities for dashboard access and file management.
- **Registration & Approval Flow**
  - Configure front-end registration/login forms from the plugin; link to `wp_signon` and disable open registration if WordPress core registration remains exposed.
  - Enable manual approval or moderation queue; set email notifications for admins and registrants about status updates.
- **Members Dashboard**
  - Register a protected page (or custom post type) for the dashboard; limit access to the new member roles via plugin access rules.
  - Create a dedicated template under `wp-content/themes/tailwind-acf/template-parts/blocks/` and pass sanitized data from an `inc/` controller.
  - Surface dynamic content: approved file downloads, announcements (from a category or custom field group), and user-specific metadata.
- **File Protection**
  - Store restricted files under `wp-content/uploads/protected/` and block direct hits using `.htaccess` (Apache) or `nginx` location rules.
  - Serve downloads via a PHP endpoint (e.g. custom page template or plugin hook) that validates the current user capability before streaming the file.
- **Admin Tools**
  - Provide an admin-only upload interface (ACF options page or custom admin menu) tied to the protected directory.
  - Log file uploads/changes for auditing and notify members when new documents go live.

## QA & Maintenance
- Validate the workflow end-to-end: registration, approval, login, dashboard access, file download, and revocation.
- Flush caches and regenerate ACF JSON after structural changes; keep debug flags enabled during development to catch notices.
- Document the plugin configuration, custom roles, and approval steps so operations can reproduce the setup in new environments.
