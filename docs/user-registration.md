# User Registration Guide

This guide explains how new members register for an account on the platform and how administrators approve their access.

---

## Overview

The platform uses an approval-based registration system. When someone registers, they cannot access member features until an administrator reviews and approves their account. This ensures only legitimate breeders gain access to the cattle registration system.

---

## User Roles

### Pending Members
New registrants who are waiting for approval. They:
- Cannot log into the platform
- See an "awaiting approval" message if they try
- Receive an email when approved

### Approved Members
Verified breeders who can:
- Log in and access the Dashboard
- Register cattle
- View and manage their registrations
- Access member-only pages

### Administrators
Platform managers who can:
- Review pending member registrations
- Approve or reject new members
- Access the WordPress admin area
- Manage all content and settings

---

## The Registration Journey

### Step 1: New User Registers

1. **Find the Registration Link**  
   Navigate to the registration page. This is typically linked from the main menu or login page.

2. **Complete the Registration Form**  
   Fill in the required information:
   - Username
   - Email address
   - Password (or have one generated)

3. **Submit the Form**  
   Click the register button to create your account.

4. **See the Pending Message**  
   You'll be redirected to the Dashboard page showing:
   > "Thanks for registering! An administrator will review your account shortly. You will receive an email once you are approved."

---

### Step 2: What Happens Behind the Scenes

When you register:
- Your account is created with a **"Pending"** status
- The site administrator receives an email notification with your username and email
- Your account is blocked from logging in until approved

---

### Step 3: Administrator Reviews the Registration

1. **Notification**  
   The administrator receives an email:
   > "A new user registered and is awaiting approval:  
   > Username: [your username]  
   > Email: [your email]  
   > Approve via Users → All Users."

2. **Access the Users List**  
   The administrator logs into WordPress admin and navigates to:
   **Users → All Users**

3. **Find Pending Members**  
   The user list includes a **"Member Status"** column showing:
   - **Pending Approval** – Waiting for review
   - **Approved** – Active member

4. **Approve the Member**  
   For pending users, the administrator hovers over the row and clicks **"Approve member"**. This:
   - Changes the status to "Approved"
   - Enables the user to log in
   - Triggers the approval notification

---

### Step 4: Member Gains Access

Once approved:

1. **Log In**  
   Use your username/email and password on the login page.

2. **Automatic Redirect**  
   After logging in, you'll be taken directly to your member Dashboard.

3. **Full Access**  
   You can now:
   - View your Dashboard
   - Register cattle
   - Access all member-only pages
   - View your submitted registrations

---

## Logging In

### For Approved Members
1. Go to the login page
2. Enter your username or email and password
3. Click "Log In"
4. You'll be redirected to your Dashboard

### For Pending Members
If you try to log in before approval, you'll see:
> "Your account is awaiting approval by an administrator."

You'll need to wait for an administrator to approve your account.

---

## After Logging In

### Dashboard
Your personal Dashboard (`/dashboard/`) is your home base. From here you can:
- See a welcome message with your name
- View all your cattle registrations
- Submit new registrations
- Check the status of pending submissions

### Menu Changes
Once logged in, you'll notice the navigation menu updates:
- "Register" links become "Dashboard"
- "Login" links become "Log Out"

### Logging Out
Click "Log Out" in the menu, or navigate to your profile and log out from there.

---

## Protected Pages

Some pages on the site are restricted to approved members only. If you try to access a protected page:

| Your Status | What Happens |
|-------------|--------------|
| Not logged in | Redirected to login page with message: "Please log in with an approved member account to continue." |
| Pending member | Redirected to Dashboard with pending notice |
| Approved member | Page loads normally |

---

## Status Reference

| Status | Meaning | Can Log In? | Can Register Cattle? |
|--------|---------|-------------|---------------------|
| Pending Approval | Waiting for admin review | No | No |
| Approved | Active member | Yes | Yes |

---

## Quick Reference: Who Does What?

| Action | New User | Pending Member | Approved Member | Administrator |
|--------|----------|----------------|-----------------|---------------|
| Register for account | ✓ | | | |
| View pending message | | ✓ | | |
| Log in | | ✗ | ✓ | ✓ |
| Access Dashboard | | | ✓ | ✓ |
| Register cattle | | | ✓ | |
| Approve new members | | | | ✓ |

---

## Troubleshooting

### "Your account is awaiting approval"
This means your account hasn't been approved yet. Please wait for an administrator to review your registration. If it's been several days, contact the administrator.

### "Please log in with an approved member account"
You're trying to access a member-only page without being logged in. Click the link to log in, or register if you don't have an account.

### "You do not have permission to view that page"
This page requires a higher access level than your account has. Contact an administrator if you believe you should have access.

### Forgot your password?
Use the "Lost your password?" link on the login page to reset it via email.

---

## Contact

For registration issues or account questions, contact the site administrator.



