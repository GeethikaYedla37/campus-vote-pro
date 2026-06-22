# CampusVote Pro

CampusVote Pro is a PHP + MySQL online college election website with admin management, student registration, candidate enrollment, voting, results, CSV export, and audit logs.

## Tech Stack

- Frontend: HTML, CSS, Bootstrap Icons CDN, responsive custom UI
- Backend: PHP with PDO
- Database: MySQL
- Local runtime: XAMPP or Docker
- Deployment target: Docker hosting such as Render
- Security: password hashing, prepared SQL queries, CSRF tokens, email verification for admin login, rate limiting, secure session cookies, upload validation, and database rules for one vote per category

## Main Features

- Public home page with election summary
- Admin login with emailed verification code
- Admin dashboard
- Create, close, and delete election categories
- Enroll candidates with optional photo upload
- Activate, deactivate, and review student accounts
- Student registration and login after activation
- Vote once per open category
- Live results and percentages
- CSV export
- Audit logs and login attempt tracking

## Database Tables

- `admins`: admin accounts with hashed passwords and temporary email-code hashes
- `students`: voter accounts and approval status
- `election_categories`: election posts/categories
- `candidates`: candidates linked to categories
- `votes`: one vote per student per category
- `audit_logs`: important login, registration, vote, and admin actions
- `login_attempts`: failed/successful attempt tracking for rate limiting

## Run With Docker

1. Copy `.env.example` to `.env`.
2. Put your own database passwords and SMTP email settings in `.env`.
3. Start Docker:

```powershell
docker compose up -d --build
```

Open:

```text
Website: http://localhost:8081
phpMyAdmin: http://localhost:8082
```

Full guide: `docs/DOCKER_LOCAL.md`

## Run With XAMPP

1. Copy this folder to `C:\xampp\htdocs\campus-vote-pro`.
2. Start Apache and MySQL in XAMPP.
3. Import `database/schema.sql` in phpMyAdmin.
4. Create the admin account with `scripts/create_admin.php`.
5. Configure SMTP variables so admin verification codes can be emailed.
6. Open `http://localhost/campus-vote-pro` or your XAMPP port URL.

## Create Admin Account

The repository does not include an admin password. Create the admin from environment variables:

```powershell
$env:ADMIN_NAME="Election Administrator"
$env:ADMIN_EMAIL="admin@yourcollege.edu"
$env:ADMIN_PASSWORD="Use_A_Long_Strong_Private_Password"
php scripts/create_admin.php
```

In Docker:

```powershell
docker compose exec -e ADMIN_NAME="Election Administrator" -e ADMIN_EMAIL="admin@yourcollege.edu" -e ADMIN_PASSWORD="Use_A_Long_Strong_Private_Password" app php scripts/create_admin.php
```

The password is stored only as a hash.

## Email Verification

Admin login works like this:

1. Admin enters email and password.
2. The app generates a 6-digit code.
3. The database stores only a hash of that code.
4. The real code is sent to the admin email using SMTP.
5. Admin enters the code before accessing the dashboard.

Required mail variables:

```text
MAIL_TRANSPORT=smtp
MAIL_FROM_ADDRESS=admin@yourcollege.edu
MAIL_FROM_NAME=CampusVote Pro
SMTP_HOST=smtp.your-provider.com
SMTP_PORT=587
SMTP_USERNAME=your_smtp_username
SMTP_PASSWORD=your_smtp_password_or_app_password
SMTP_ENCRYPTION=tls
```

With Docker, test email delivery after setting SMTP values:

```powershell
docker compose exec app php scripts/test_mail.php
```

## Project Flow

1. Admin creates election categories.
2. Admin enrolls candidates.
3. Student registers.
4. Admin activates student.
5. Student logs in.
6. Student votes once per category.
7. Results are calculated from the `votes` table.

## Important Files

- `database/schema.sql`: database structure with no seeded passwords
- `database/production_schema.sql`: production database structure
- `scripts/create_admin.php`: creates or updates the real admin account
- `docs/RENDER_DEPLOY.md`: Render deployment guide
- `docs/DOCKER_LOCAL.md`: local Docker guide
- `Dockerfile`: PHP Apache Docker image
- `docker-compose.yml`: local app, MySQL, and phpMyAdmin setup
- `config/config.php`: environment and database settings
- `includes/auth.php`: login, guards, email-code verification, logout, audit logging
- `includes/helpers.php`: validation, CSRF, mail sending, and formatting helpers
