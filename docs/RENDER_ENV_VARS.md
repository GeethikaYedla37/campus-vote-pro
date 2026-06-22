# Render Environment Variables

Add these in Render Dashboard -> your Web Service -> Environment.

Do not put real passwords in GitHub. Keep them only in Render.

## App

```text
APP_ENV=production
UPLOAD_PATH=/var/www/html/uploads/candidates
```

## Database

Use the values from your Render MySQL/database service.

```text
DB_HOST=<your Render private database host>
DB_PORT=3306
DB_NAME=campus_vote_pro
DB_USER=<your database username>
DB_PASS=<your database password>
```

## Mail / Admin Verification Code

For Gmail, use a Gmail App Password, not your normal Gmail password.

```text
MAIL_TRANSPORT=smtp
MAIL_FROM_ADDRESS=<your sender email>
MAIL_FROM_NAME=CampusVote Pro
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=<your sender email>
SMTP_PASSWORD=<your Gmail app password or SMTP password>
SMTP_ENCRYPTION=tls
```

## First Admin Setup

Set these temporarily before running `scripts/create_admin.php`.

```text
ADMIN_NAME=<admin full name>
ADMIN_EMAIL=<admin login email>
ADMIN_PASSWORD=<strong admin password>
```

After the admin is created, remove `ADMIN_PASSWORD` from Render if you do not want it stored there.

## Render Shell Commands

Run these in the Render web service shell after deployment:

```bash
php scripts/migrate.php
php scripts/create_admin.php
php scripts/test_mail.php
```

If `test_mail.php` says the test email was sent, admin login email verification is ready.
