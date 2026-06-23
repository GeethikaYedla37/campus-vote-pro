# CampusVote Pro

CampusVote Pro is a local PHP and MySQL online college election website. It supports student registration, admin approval, election categories, candidate management, voting, live results, CSV export, and audit logs.

This version is prepared for local Apache/XAMPP use. It does not include demo users, demo passwords, real `.env` secrets, Gmail app passwords, local database data, or uploaded candidate photos.

## Tech Stack

- Frontend: HTML, CSS, Bootstrap Icons CDN
- Backend: PHP with PDO
- Database: MySQL
- Local runtime: Apache with XAMPP
- Database tool: phpMyAdmin

## Features

- Public election home page
- Student registration and login
- Admin login with emailed verification code
- Student activation by admin
- Election category management
- Candidate enrollment with optional photo upload
- One vote per student per category
- Live results and CSV export
- Public support/contact details
- Student feedback form inside the logged-in dashboard
- Admin feedback status updates with email notification to the student reply address
- Audit logs and login attempt tracking
- Visible security section on the home page

## Security Summary

- Passwords are hashed, not stored as plain text.
- SQL uses prepared queries through PDO.
- Forms use CSRF protection.
- Admin login requires email verification.
- Repeated login failures are rate-limited.
- Uploaded candidate files are validated.
- Duplicate votes are blocked by a database rule.
- Real secrets are kept in `.env`, not in GitHub.

## Database Tables

- `admins`
- `students`
- `election_categories`
- `candidates`
- `votes`
- `feedback_messages`
- `audit_logs`
- `login_attempts`

## Run Locally With XAMPP

1. Copy `.env.example` to `.env`.
2. Start Apache and MySQL in XAMPP.
3. Import `database/schema.sql` in phpMyAdmin.
4. Add SMTP email values in `.env`.
5. Open `setup.php` and create the first admin.

```text
Website: http://localhost:8080/campus-vote-pro/
Setup: http://localhost:8080/campus-vote-pro/setup.php
phpMyAdmin: http://localhost:8080/phpmyadmin
```

Full guide: `docs/XAMPP_LOCAL.md`

This laptop's XAMPP Apache port is configured as `8080`, and MySQL is configured as `3305`. If XAMPP ports change later, update the URL port and `DB_PORT` in `.env`.

## Admin Setup

The repository does not include an admin password. Create the first admin from `setup.php`.

The admin password is stored as a hash. After one admin exists, `setup.php` locks itself.

## Email Setup

Admin verification emails and feedback status emails need SMTP settings:

```text
MAIL_TRANSPORT=smtp
MAIL_FROM_ADDRESS=<sender email>
MAIL_FROM_NAME=CampusVote Pro
SMTP_HOST=<smtp host>
SMTP_PORT=587
SMTP_USERNAME=<smtp username>
SMTP_PASSWORD=<smtp password or app password>
SMTP_ENCRYPTION=tls
```

When admin marks feedback as `new`, `reviewed`, or `resolved`, the app sends an email to the reply address submitted by the student.

## Important Files

- `.env.example`: safe placeholder environment file
- `database/schema.sql`: local database schema
- `setup.php`: local first-admin setup page
- `docs/XAMPP_LOCAL.md`: local XAMPP guide

## GitHub Safety

Do not commit real passwords or production data. Keep these private:

- `.env`
- database passwords
- Gmail app password or SMTP password
- admin password
- local database data
- uploaded candidate photos
