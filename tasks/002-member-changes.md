## Goal
Enhance the member UX so that registrations require admin approval, pending users see clear messaging, approved members land on `/dashboard`, and editors can protect pages by role/capability.

## Implementation Plan
1. **Bootstrap member module**
   - Load a new `inc/members.php` from `functions.php` to consolidate hooks and helper functions for member workflows.
   - Define shared constants/helper functions here (e.g. `TAILWIND_MEMBER_STATUS_META`).

2. **Registration flow**
   - Hook `user_register` to set `tailwind_member_status = pending` via `update_user_meta`.
   - Use `wp_new_user_notification()` so admins receive the core email; extend it (filter `wp_mail`) if we need to highlight the approval CTA.
   - Hook `registration_redirect` to send new signups to `/dashboard` with `add_query_arg( 'pending', 1, home_url( '/dashboard/' ) )` so the dashboard template can display a pending notice.

3. **Login handling**
   - Filter `wp_authenticate_user` to block users whose status is still `pending` by returning a localized `WP_Error`; message should explain that an admin must approve the account.
   - Hook `login_redirect` to send approved members to `/dashboard`; fall back to the original redirect when status is not `approved`.
   - Optionally clear the “pending” dashboard notice flag once the user is approved (e.g. `delete_user_meta` when status flips).

4. **Dashboard messaging**
   - Update the dashboard page template (or create a dedicated `template-parts/dashboard.php`) to read the `pending` query var and show a dismissible notice when present.
   - Guard dashboard content: when the current user is pending, hide member tools and replace with the approval-needed message.

5. **Admin approval UX**
   - Add a custom column on `users.php` via `manage_users_columns`/`manage_users_custom_column` to display the `tailwind_member_status`.
   - Inject an “Approve member” row action for pending users with `user_row_actions`; target a new `admin_post_tailwind_approve_member` handler.
   - In the handler, verify capability (`current_user_can( 'promote_users' )`), `check_admin_referer`, switch the meta to `approved`, and optionally trigger a notification email to the member.

6. **Role-based page protection**
   - Provide an editor UI (ACF select or custom meta box) that stores a required capability/role on the page (`tailwind_required_capability`).
   - On `template_redirect`, if the page has a requirement and `current_user_can( $required_cap )` fails, redirect to `/dashboard` (or the login screen) with an explanatory query arg.
   - For the dashboard and other member-only templates, rely on the same helper to keep behavior consistent.

7. **Documentation & QA**
   - Add a docs entry (e.g. `docs/membership.md`) covering approval steps, member status meanings, and how to configure protected pages.
   - Manually QA: register a new user, confirm redirect + notice, attempt login pre/post approval, approve in admin, verify dashboard redirect, and confirm protected pages respect capabilities.
