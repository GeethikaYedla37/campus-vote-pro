# CampusVote Pro

CampusVote Pro is a PHP and MySQL online college election website. It supports student registration, admin approval, election categories, candidate management, voting, live results, CSV export, and audit logs.

This repository is prepared for a real project workflow. It does not include demo users, demo passwords, real `.env` secrets, Gmail app passwords, local database data, or uploaded candidate photos.

## Tech Stack

- Frontend: HTML, CSS, Bootstrap Icons CDN
- Backend: PHP with PDO
- Database: MySQL
- Local runtime: Docker or XAMPP
- Deployment: Docker web service, such as Render, with a separate MySQL-compatible database

Important: this project uses MySQL. Render managed databases are Postgres, so use an external MySQL-compatible database provider unless you rewrite the app for Postgres.

## Features

- Public election home page
- Student registration and login
- Admin login with emailed verification code
- Student activation by admin
- Election category management
- Candidate enrollment with optional photo upload
- One vote per student per category
- Live results and CSV export
- Audit logs and login attempt tracking

## Security Summary

- Passwords are hashed, not stored as plain text.
- SQL uses prepared queries through PDO.
- Forms use CSRF protection.
- Admin login requires email verification.
- Repeated login failures are rate-limited.
- Uploaded candidate files are validated.
- Duplicate votes are blocked by a database rule.
- Real secrets are kept in `.env` or hosting environment variables, not in GitHub.

## Database Tables

- `admins`
- `students`
- `election_categories`
- `candidates`
- `votes`
- `audit_logs`
- `login_attempts`

## Run Locally With Docker

1. Copy `.env.example` to `.env`.
2. Add your local database and SMTP values in `.env`.
3. Start the app:

```powershell
docker compose up -d --build
```

Open:

```text
Website: http://localhost:8081
phpMyAdmin: http://localhost:8082
```

Full Docker guide: `docs/DOCKER_LOCAL.md`

## Run Locally With XAMPP

1. Copy the project folder to `C:\xampp\htdocs\campus-vote-pro`.
2. Start Apache and MySQL.
3. Import `database/schema.sql` in phpMyAdmin.
4. Configure database and SMTP values.
5. Create the admin account with `scripts/create_admin.php`.

## Admin Setup

The repository does not include an admin password. Create the first admin from environment variables:

```powershell
docker compose exec -e ADMIN_NAME="Election Administrator" -e ADMIN_EMAIL="admin@yourcollege.edu" -e ADMIN_PASSWORD="Use_A_Long_Strong_Private_Password" app php scripts/create_admin.php
```

The admin password is stored as a hash.

## Email Setup

Admin verification emails need SMTP settings:

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

Test email delivery:

```powershell
docker compose exec app php scripts/test_mail.php
```

## Deployment

Render deployment notes are in:

- `docs/RENDER_DEPLOY.md`
- `docs/RENDER_ENV_VARS.md`

After deploying and adding environment variables, run:

```bash
php scripts/migrate.php
php scripts/create_admin.php
php scripts/test_mail.php
```

## Important Files

- `Dockerfile`: PHP Apache Docker image
- `docker-compose.yml`: local app, MySQL, and phpMyAdmin
- `.env.example`: safe placeholder environment file
- `database/schema.sql`: local database schema
- `database/production_schema.sql`: production schema
- `scripts/migrate.php`: creates or upgrades production tables
- `scripts/create_admin.php`: creates or updates the real admin account
- `scripts/test_mail.php`: tests SMTP delivery

## GitHub Safety

Do not commit real passwords or production data. Keep these private:

- `.env`
- database passwords
- Gmail app password or SMTP password
- admin password
- local database data
- uploaded candidate photos
