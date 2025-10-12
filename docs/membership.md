# Membership Workflow

## Registration
- Users register through the default `/wp-login.php?action=register` form.
- They are redirected to `/dashboard/?pending=1`, where they see the “awaiting approval” notice.
- New accounts are marked with the `tailwind_member_status = pending` user meta. Core WordPress emails still fire; hook `tailwind_member_send_admin_notice` if you need additional notifications.

## Approval
- Visit **Users → All Users** in wp-admin.
- The list table now includes a **Member Status** column and an “Approve member” row action for pending profiles.
- Approving flips the status to `approved`, triggers the `tailwind_member_approved` action for follow-up tasks, and returns you to the user list.

## Login & Redirects
- Pending users are blocked from signing in and see a localized error on the login screen.
- Approved members without a requested redirect land on `/dashboard/`; site administrators keep their original redirect target.
- Attempts to access protected pages while logged out send users to the login screen with a membership hint.
- When members are logged in, any “Register” or “Login” menu links automatically switch to “Dashboard” and “Log Out”.
- A post-login hook ensures members go straight to the dashboard unless they explicitly requested a front-end page, and subscribers (plus other non-editors) are bounced out of `/wp-admin/` if they arrive there later.

## Dashboard Messaging
- The `Dashboard` page uses the dedicated `page-dashboard.php` template.
- Set the page slug to `dashboard` (or adjust the code hook) so redirects resolve to the correct URL; the theme falls back to `/dashboard/` if the page is not found.
- Pending members see approval messaging and the main content stays hidden until their status becomes `approved`.
- Query args such as `?pending=1` and `?denied=1` surface contextual notices for registration and access denials.

## Restricting Pages
- Edit the desired page and use the **Member Access** meta box to choose one of:
  - `Public (no restriction)`
  - `Approved members`
  - `Authors and above` (`edit_posts`)
  - `Editors and above` (`edit_pages`)
  - `Administrators only` (`manage_options`)
- The dashboard page defaults to “Approved members” even if no value is stored.
- When access is denied, logged-in users are redirected to `/dashboard/?denied=1`; pending users are sent to `/dashboard/?pending=1`.

## Manual QA Checklist
- Register a new user and confirm the redirect to `/dashboard/?pending=1` with the approval notice.
- Attempt to log in as the pending user and verify the “awaiting approval” error.
- Approve the user from the wp-admin list table and confirm the status column updates.
- Log in as the approved member and ensure the dashboard page loads with its content.
- Set a page to “Approved members” and confirm logged-out visitors are sent to the login form and approved members can view it.
- Set a page to “Editors and above” and confirm authors/members receive the denial redirect while editors can access it.
