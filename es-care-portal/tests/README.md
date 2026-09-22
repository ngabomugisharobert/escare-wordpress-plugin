# ES Care Portal 2.0 verification

## Staged upgrade
1. Back up the WordPress database and `wp-content/uploads/esc-resumes` (plus any private storage directory).
2. Deploy plugin 2.0.0 and activate. Schema migrations run once and store `esc_portal_schema_version`.
3. Confirm **Health** shows the target schema version, writable private storage, and a scheduled mail/retention cron.
4. For large datasets, watch Health logs while resumes migrate and identity fields are purged (counts only, never values).

## Automated checks
- `php tests/run-checks.php` — PHP syntax plus static assertions for CSRF, privacy, uninstall, pagination, and bootstrap.
- PHPUnit: copy `phpunit.xml.dist` usage with `WP_TESTS_DIR` pointing at a WordPress test suite, then `phpunit -c tests/phpunit.xml.dist`.

## Manual authorization regression
- Job seeker: register, apply, take an assessment, submit a service request. Confirm Apply is visible on Careers.
- Employer: register, verify email, wait for admin approval, submit a job (stays pending), receive moderation email. Confirm Apply is hidden on Careers while signed in.
- Portal admin (portal session on `/sign-in/`, not WordPress `/wp-admin/`): approve/reject employers and jobs, paginate users/jobs/applications. Open Careers and a job page — Apply must not appear. Dashboard menu is Overview, Users, Jobs, Applications (no Contact Us).
- WordPress administrator with no portal cookie: treated as a guest on Careers (Apply still shows).
- Guest, seeker, employer, and portal admin can all open the public Contact Us page.

## Accessibility
- Sortable headers expose `scope="col"` and `aria-sort`.
- Empty tables announce a zero-results row.
- Filter controls have associated labels.

## Direct file access
- Confirm HTTP GET to any legacy `wp-content/uploads/esc-resumes/` URL is denied.
- New uploads must fail closed if the private directory is not writable.
