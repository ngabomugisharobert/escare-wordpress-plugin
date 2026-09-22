---
title: "Website Management & Credentials Documentation"
---

**CONFIDENTIAL — Internal / owner use only**  
Do not email this file unencrypted. Store passwords in a password manager when possible.

**Client:** E&S Care Service LLC  
**Prepared by:** BlueField Technology LLC  
**Document date:** September 21, 2026  
**Website:** https://escare-services.com  
**Public contact email (site):** info@escareservices.com  
**Phone:** 360-742-8095  
**Business address:** 3917 Boulevard Rd SE, Olympia, WA 98501  

---

## How to use this document

1. Fill every field marked **`[FILL IN]`** with the real value after setup.
2. Update the **Last verified** date whenever a password or account changes.
3. Keep one printed copy in a secure place and one digital copy offline or in a vault.
4. When staff leave, change shared passwords immediately.

---

## 1. Domain name registration

| Field | Value |
|-------|-------|
| Registered domain(s) | `escare-services.com` — **confirm**; also note `escareservices.com` if used for email |
| Registrar (where domain was bought) | `[FILL IN]` e.g. GoDaddy, Namecheap, Google Domains / Squarespace, Cloudflare, Hostinger |
| Registrar login URL | `[FILL IN]` |
| Registrar account email | `[FILL IN]` |
| Registrar username | `[FILL IN]` |
| Registrar password | `[FILL IN]` |
| Domain expiration / renewal date | `[FILL IN]` |
| Auto-renew enabled? | Yes / No — `[FILL IN]` |
| DNS host (if different from registrar) | `[FILL IN]` |
| Nameservers | `[FILL IN]` |
| Last verified | `[FILL IN]` |

**Notes**

- Point **A / CNAME** records to the hosting account that runs WordPress.
- Point **MX** records to whatever provides company email (Google Workspace, Microsoft 365, host mail, etc.).
- After DNS changes, allow up to 24–48 hours for full propagation.

---

## 2. Hosting / control panel

| Field | Value |
|-------|-------|
| Hosting provider | `[FILL IN]` e.g. cPanel host, SiteGround, Bluehost, Cloudways, WP Engine |
| Control panel type | `[FILL IN]` cPanel / Plesk / custom dashboard |
| Control panel URL | `[FILL IN]` e.g. `https://example.com:2083` or provider dashboard |
| Control panel username | `[FILL IN]` |
| Control panel password | `[FILL IN]` |
| Server / account IP | `[FILL IN]` |
| Document root (WordPress path) | `[FILL IN]` e.g. `public_html/` |
| FTP / SFTP host | `[FILL IN]` |
| FTP / SFTP username | `[FILL IN]` |
| FTP / SFTP password | `[FILL IN]` |
| SSH access (if any) | Enabled / Disabled — `[FILL IN]` |
| Hosting plan / renewal date | `[FILL IN]` |
| Last verified | `[FILL IN]` |

**Typical control-panel tasks**

- Create / renew SSL (HTTPS)
- Manage email accounts and forwarders
- Create MySQL databases and users
- View error logs and disk usage
- Schedule or download backups

---

## 3. WordPress administration

| Field | Value |
|-------|-------|
| Site URL | https://escare-services.com |
| WordPress Admin URL | https://escare-services.com/wp-admin/ |
| Admin username | `[FILL IN]` |
| Admin email | `[FILL IN]` |
| Admin password | `[FILL IN]` |
| Active theme | **E&S Care** (`es-care`) |
| Required plugin | **ES Care Portal** (`es-care-portal`) — version 2.0.11+ |
| Database name | `[FILL IN]` |
| Database user | `[FILL IN]` |
| Database password | `[FILL IN]` |
| Database host | `[FILL IN]` usually `localhost` |
| Last verified | `[FILL IN]` |

### Portal-specific settings (after login to WP Admin)

Path: **ES Care Portal → Settings**

| Setting | Recommended / note |
|---------|-------------------|
| Notification email | Company inbox that should receive applications & contact messages — `[FILL IN]` |
| SMTP (portal mail) | Configure if verification / application emails must not use default PHP mail |
| Resume max size / types | PDF, DOC, DOCX (per Settings) |
| Retention | Default 3 years (adjust in Settings) |
| Frontend pages | Register, Sign In, Dashboard, Careers, Apply, Contact Us, etc. |

Also check: **ES Care Portal → Health** (schema, private storage writable, mail queue / cron).

### Important portal pages

| Page | Typical URL path |
|------|------------------|
| Register | `/register/` |
| Sign In | `/sign-in/` |
| Dashboard | `/portal-dashboard/` |
| Careers | `/careers/` |
| Apply | `/apply/` |
| Contact Us | `/contact-us/` |
| Lost / Reset password | `/lost-password/` · `/reset-password/` |

### Portal roles (quick reference)

| Role | Behavior |
|------|----------|
| Job seeker | Active after register; apply, assessments, forms |
| Employer | Must verify email, then wait for admin approval before posting jobs |
| Portal admin | Created in **ES Care Portal → Dashboard Users** (not public registration) |

**Employer job posts** stay **pending** until a staff member publishes them in WordPress / portal admin.

### Theme contact details (Customizer)

**Appearance → Customize → E&S Care contact**

| Field | Current / expected |
|-------|--------------------|
| Phone | 360-742-8095 |
| Public email | info@escareservices.com |
| Hours | 24/7 shift coverage |
| Address | 3917 Boulevard Rd SE, Olympia, WA 98501 |

---

## 4. Gmail / Google account (if used)

Use this section if a personal or shared **Gmail** account is used for Google services, domain recovery, or temporary mail.

| Field | Value |
|-------|-------|
| Gmail address | `[FILL IN]` |
| Password | `[FILL IN]` |
| Recovery email | `[FILL IN]` |
| Recovery phone | `[FILL IN]` |
| 2-Step Verification | On / Off — `[FILL IN]` |
| Backup codes location | `[FILL IN]` |
| Purpose of this Gmail | `[FILL IN]` e.g. registrar recovery, Google Workspace billing, Analytics |
| Last verified | `[FILL IN]` |

---

## 5. Company email

| Field | Value |
|-------|-------|
| Email provider | `[FILL IN]` Google Workspace / Microsoft 365 / hosting webmail / other |
| Webmail / admin login URL | `[FILL IN]` |
| Primary company address | info@escareservices.com *(confirm exact domain spelling)* |
| Additional mailboxes | `[FILL IN]` e.g. careers@…, admin@…, hr@… |
| Admin / billing login for email | `[FILL IN]` username |
| Admin password | `[FILL IN]` |
| Mailbox: info@… password | `[FILL IN]` |
| Mailbox: `[other]` password | `[FILL IN]` |
| Forwarding rules | `[FILL IN]` |
| MX records verified? | Yes / No — `[FILL IN]` |
| SPF / DKIM / DMARC set? | Yes / No / Partial — `[FILL IN]` |
| Last verified | `[FILL IN]` |

**Recommendation:** Use Google Workspace or Microsoft 365 on the company domain for professional mail; avoid relying only on a free Gmail address for customer-facing contact.

---

## 6. Day-to-day website management

### Content & branding

- Edit marketing pages under **Pages** in WordPress, or replace copy in the theme templates if pages are template-driven.
- Update phone / email / hours / address in **Appearance → Customize → E&S Care contact**.
- Replace logo via **Appearance → Customize → Site Identity** (Custom Logo).

### Jobs & hiring workflow

1. Employers or staff create jobs (employer posts need approval / publish).
2. Job seekers apply from **Careers** / Apply flows with resume upload.
3. Staff review applications in the portal admin / **ES Care Portal → Applications**.
4. Use assessments and employment forms as configured under the portal menus.

### Contact & service requests

- Public **Contact Us** messages and dashboard service requests appear under portal **Service Requests** / contact inbox (and notification email if configured).

### Updates & security (owner checklist)

| Task | Frequency |
|------|-----------|
| WordPress, theme, and plugin updates | Monthly (or when security releases appear) |
| Strong unique passwords + 2FA where available | Always |
| Full site backup (files + database) | Weekly minimum; before any major change |
| Review spam users / pending employers | Weekly |
| Confirm SSL certificate is valid | Quarterly |
| Domain & hosting renewal dates | Calendar reminders 30 days ahead |

### Backups

| Field | Value |
|-------|-------|
| Backup method | `[FILL IN]` host backup / UpdraftPlus / manual |
| Backup location | `[FILL IN]` |
| How to restore | `[FILL IN]` brief steps or link to host docs |
| Last successful backup tested | `[FILL IN]` |

---

## 7. Support contacts

| Role | Name / company | Contact |
|------|----------------|---------|
| Website developer | BlueField Technology LLC | `[FILL IN]` |
| Hosting support | `[FILL IN]` | `[FILL IN]` |
| Domain registrar support | `[FILL IN]` | `[FILL IN]` |
| Email provider support | `[FILL IN]` | `[FILL IN]` |
| Business owner (E&S Care) | `[FILL IN]` | 360-742-8095 |

---

## 8. Change log

| Date | What changed | Changed by |
|------|--------------|------------|
| 2026-09-21 | Document created (template with known public details) | BlueField Technology LLC |
| `[FILL IN]` | | |

---

## 9. Acknowledgement

I confirm I have received this documentation and that all `[FILL IN]` fields will be completed with current credentials and stored securely.

**Owner name:** _______________________________  
**Signature:** _______________________________  
**Date:** _______________________________  

---

*End of Website Management & Credentials Documentation — E&S Care Service LLC*
