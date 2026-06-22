# Deploy CampusVote Pro On Render

Render deploys the PHP website from GitHub using the `Dockerfile`. The database should be a separate MySQL service.

## Production Architecture

```text
Browser
  -> Render HTTPS web service
  -> PHP + Apache Docker container
  -> MySQL database
  -> Uploaded candidate images storage
```

## Files Used

- `Dockerfile`: builds the PHP Apache web service.
- `.dockerignore`: keeps local secrets out of the image.
- `database/production_schema.sql`: creates empty production tables.
- `scripts/migrate.php`: creates or upgrades tables from the Render shell.
- `scripts/create_admin.php`: creates or updates the real admin account.

## Step 1: Push To GitHub

Create a GitHub repository and push this project.

Do not commit:

- `.env`
- database dumps with real voters
- real SMTP passwords
- real admin passwords
- uploaded identity documents or private files

## Step 2: Create MySQL Database

Create a MySQL service/database and keep these values ready:

```text
DB_HOST=<private database host>
DB_PORT=3306
DB_NAME=campus_vote_pro
DB_USER=<database user>
DB_PASS=<strong database password>
```

Use persistent storage and regular backups for real elections.

## Step 3: Create Render Web Service

1. Render Dashboard -> New -> Web Service.
2. Connect your GitHub repository.
3. Runtime: Docker.
4. Render uses the project `Dockerfile`.

Set environment variables:

Use `docs/RENDER_ENV_VARS.md` as the full checklist.

```text
APP_ENV=production
DB_HOST=<private database host>
DB_PORT=3306
DB_NAME=campus_vote_pro
DB_USER=<database user>
DB_PASS=<strong database password>
UPLOAD_PATH=/var/www/html/uploads/candidates

MAIL_TRANSPORT=smtp
MAIL_FROM_ADDRESS=admin@yourcollege.edu
MAIL_FROM_NAME=CampusVote Pro
SMTP_HOST=<smtp host>
SMTP_PORT=587
SMTP_USERNAME=<smtp username>
SMTP_PASSWORD=<smtp password or app password>
SMTP_ENCRYPTION=tls
```

Attach a persistent disk if uploaded candidate photos must survive redeploys:

```text
Mount path: /var/www/html/uploads/candidates
```

## Step 4: Create Production Tables

Open the Render shell for the PHP web service and run:

```bash
php scripts/migrate.php
```

This creates:

```text
admins
students
election_categories
candidates
votes
audit_logs
login_attempts
```

No users, passwords, candidates, or votes are inserted automatically.

## Step 5: Create Real Admin Account

Set these environment variables temporarily in the Render shell or service:

```text
ADMIN_NAME=<real admin name>
ADMIN_EMAIL=<real admin email>
ADMIN_PASSWORD=<strong admin password>
```

Then run:

```bash
php scripts/create_admin.php
```

The admin password is stored as a hash. Remove `ADMIN_PASSWORD` from Render after setup if you do not want it kept in the Render environment.

## Step 6: Login Flow

1. Admin enters email and password.
2. The app generates a 6-digit code.
3. The database stores only the hash of that code.
4. The app emails the real code to the admin email.
5. Admin enters the code to open the dashboard.

If email is not configured correctly, admin login will stop after password verification and show an email settings error.

## Step 7: Add Election Data

Admin creates:

1. Election categories
2. Real candidates
3. Candidate photos if needed

Students:

1. Register themselves
2. Wait for admin approval
3. Login after activation
4. Vote once per open category

## Production Checklist

- `APP_ENV=production`
- Real SMTP service configured
- Strong database password
- Strong admin password
- HTTPS URL enabled
- Database backups enabled
- Uploaded images stored persistently
- No private `.env` file committed
