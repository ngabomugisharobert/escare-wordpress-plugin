# ES Care Portal

WordPress plugin for **ES Care Services LLC**. It runs a job-seeker and employer portal that is separate from WordPress site users: registration, login, job listings, applications, assessments, employment forms, service requests, and a public Contact Us form.

Requires WordPress 6.0+ and PHP 7.4+. Elementor is optional.

Current version: **2.0.7**

## What it does

- Job seekers register, apply to jobs, upload resumes, take assessments, and submit employment forms.
- Employers register, verify email, wait for admin approval, then post jobs that stay pending until staff publish them.
- Portal admins manage users, jobs, applications, and contact messages from the dashboard.
- Guests can send a Contact Us message without signing in.

Portal accounts live in custom tables (`wp_esc_users`, `wp_esc_usermeta`, `wp_esc_sessions`). They are not WordPress users.

## Install

1. Copy the `es-care-portal` folder into `wp-content/plugins/`.
2. Activate **ES Care Portal** in WordPress.
3. On first activation the plugin creates tables, default pages, job categories, upload storage, and scheduled mail/retention jobs.
4. Open **ES Care Portal → Settings** and set the notification email, SMTP (if used), resume size/types, retention, and dashboard tiles.
5. Open **ES Care Portal → Health** and confirm the schema version, writable private storage, and scheduled cron.

Existing installs upgrade automatically. Schema version is stored in `esc_portal_schema_version` (currently **3**).

## Pages created on activation

| Page        | Slug             | Shortcode          |
|-------------|------------------|--------------------|
| Register    | `/register/`     | `[esc_register]`   |
| Sign In     | `/sign-in/`      | `[esc_login]`      |
| Dashboard   | `/portal-dashboard/` | `[esc_dashboard]` |
| Profile     | `/portal-profile/` | `[esc_profile]`  |
| Careers     | `/careers/`      | `[esc_jobs]`       |
| Apply       | `/apply/`        | `[esc_apply]`      |
| Post a Job  | `/post-a-job/`   | `[esc_job_form]`   |
| Lost Password | `/lost-password/` | `[esc_lost_password]` |
| Reset Password | `/reset-password/` | `[esc_reset_password]` |
| Contact Us  | `/contact-us/`   | `[esc_contact]`    |

If a page is missing, deactivate and reactivate the plugin, or recreate it with the matching shortcode. Assigned page IDs are listed under **Settings → Frontend pages**.

### Extra shortcodes

| Shortcode | Purpose |
|-----------|---------|
| `[esc_logout]` | Sign-out link |
| `[esc_dash_sidebar]` | Role-aware dashboard menu |
| `[esc_dash_home]` | Home tiles |
| `[esc_dash_view view="apply"]` | One dashboard view |
| `[esc_portal_notice]` | Flash notice after a form POST |

Dashboard views use `?esc_view=`. Examples: `apply`, `assessments`, `results`, `forms`, `request` (seeker); `profile`, `jobs`, `post`, `membership`, `request` (employer); `users`, `jobs`, `applications`, `contact` (portal admin).

## Account roles

| Role | Who | After register |
|------|-----|----------------|
| Job seeker | Applicants | Active immediately |
| Employer / company | Hiring companies | Verify email, then wait for admin approval |
| Admin | Portal staff | Created only from WordPress (**Dashboard Users**), not self-registration |

Employer job posts save as WordPress `pending` until a staff member publishes them.

## WordPress admin

Under **ES Care Portal**:

- **Overview** — counts and shortcuts
- **Jobs / Job Categories** — custom post type `esc_job`
- **Applications** — review snapshots, status, resumes
- **Dashboard Users** — create, search, approve, disable, or delete portal accounts
- **Assessments** — pre-hire tests
- **Employment Forms** — fillable forms and uploads
- **Service Requests** — inbox for dashboard requests and public Contact Us messages
- **Settings** — email, SMTP, files, colors, tiles, retention, uninstall
- **Health** — schema, storage, mail queue, logs

SMTP is used only for portal mail (verification, applications, moderation). It does not replace site-wide WordPress email.

## Privacy and files

- Social Security numbers and driver’s-license numbers are **not collected**. Legacy values are purged on schema migration.
- Resumes and sensitive uploads go in a **private directory** outside public `uploads`. Direct HTTP access to legacy `wp-content/uploads/esc-resumes/` should be denied.
- Applications and resume files expire after the retention period (default **3 years**, 1–10 in Settings).
- WordPress Tools → Export Personal Data / Erase Personal Data includes portal records.
- Uninstall **keeps data by default**. Check **Delete all portal tables…** in Settings only if you want a full wipe when the plugin is deleted.

## Security

- Per-browser CSRF tokens plus Origin/Referer checks on portal POSTs
- Action-specific rate limits
- Fail-closed uploads (rejected if the private directory is not writable)
- Encrypted SMTP password (AES-256-GCM)
- Queued email with retries

## Elementor

If Elementor is active, widgets appear under **ES Care Portal**. Use **ES Care Portal Module** to place register, login, dashboard, jobs, apply, or a single dashboard view. Shortcodes work without Elementor.

## Tests

See [tests/README.md](tests/README.md).

```bash
php tests/run-checks.php
```

PHPUnit (needs a WordPress test suite):

```bash
phpunit -c tests/phpunit.xml.dist
```

PHPCS: `phpcs.xml.dist` (WordPress coding standards).

## Plugin layout

```
es-care-portal.php          Bootstrap
includes/                   PHP modules
admin/views/                WordPress admin screens
public/css|js|templates/    Frontend
tests/                      Syntax checks and PHPUnit
uninstall.php               Optional data wipe
```

## License

GPL-2.0-or-later. Author: ES Care Services LLC. Site: [escare-services.com](https://escare-services.com)
