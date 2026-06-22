# CampusVote Pro

CampusVote Pro is a PHP + MySQL online college election website with admin management, student registration, candidate enrollment, voting, results, CSV export, and audit logs.

The repository is prepared for a real project workflow. It does not contain demo passwords, demo admin accounts, demo student accounts, real `.env` secrets, Gmail app passwords, or local database data.

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

## What Is Not Pushed To GitHub

These files and values must stay private and are ignored by Git:

- `.env`
- real database passwords
- Gmail app password or SMTP password
- real admin password
- local Docker database volume
- uploaded candidate photos
- real voter/candidate production data dumps

The pushed file `.env.example` contains only placeholder names so another developer knows which environment variables are required.

## Complete Application Process

1. A visitor opens the public home page.
2. A student opens Student Login and clicks New Student Registration.
3. The student submits name, roll number, branch, year, semester, and password.
4. The backend validates all inputs.
5. The student password is hashed using PHP `password_hash`.
6. The student row is inserted into the `students` table with status `pending`.
7. Admin account is created by the system owner using `scripts/create_admin.php`.
8. Admin opens Admin Login and enters admin email/password.
9. The backend verifies the admin password using `password_verify`.
10. The backend generates a 6-digit email verification code.
11. The database stores only the hash of that code in `admins.email_code_hash`.
12. The real code is sent to the admin email using SMTP.
13. Admin enters the emailed code.
14. The backend verifies the code hash and expiry time.
15. Admin dashboard opens.
16. Admin creates election categories.
17. Admin enrolls candidates into categories.
18. Admin activates valid student accounts.
19. Activated student logs in.
20. Student can vote once per open category.
21. Vote is inserted into the `votes` table.
22. Database unique rule `uq_one_vote_per_category` blocks duplicate votes.
23. Results pages count rows from `votes` using SQL.
24. Audit logs store important actions for review.

## Where Data Is Stored

| Action | Database table | Important columns |
| --- | --- | --- |
| Create admin | `admins` | `name`, `email`, `password_hash`, `email_verification_enabled` |
| Admin login code | `admins` | `email_code_hash`, `email_code_expires_at`, `email_code_sent_at` |
| Student registration | `students` | `name`, `roll_number`, `password_hash`, `branch`, `study_year`, `semester`, `status` |
| Create election category | `election_categories` | `name`, `description`, `starts_at`, `ends_at`, `status` |
| Add candidate | `candidates` | `category_id`, `name`, `roll_number`, `branch`, `manifesto`, `photo_path`, `status` |
| Cast vote | `votes` | `student_id`, `candidate_id`, `category_id`, `created_at` |
| Login/vote/admin activity | `audit_logs` | `actor_type`, `actor_id`, `action`, `details` |
| Rate limit tracking | `login_attempts` | `attempt_type`, `identifier`, `ip_address`, `successful` |

Passwords and verification codes are not stored as plain text. They are stored as hashes.

## Hidden Security Process

- Every form includes a CSRF token from `csrf_field`.
- Every POST action calls `verify_csrf`.
- If a form token is old, the app redirects back with a friendly form-expired message.
- SQL uses PDO prepared statements instead of string-built queries.
- Student and admin passwords are verified with `password_verify`.
- Admin email verification codes expire after `EMAIL_CODE_TTL_MINUTES`.
- Login attempts are recorded and blocked after repeated failures.
- Candidate upload code validates file type and size.
- Uploaded candidate files cannot execute PHP because `uploads/candidates/.htaccess` blocks PHP-like files.
- Session cookies are HTTP-only and use strict same-site settings.
- Browser security headers are set in `config/config.php`.
- One-student-one-vote-per-category is enforced in the database, not only in PHP.

## Verify Data Is Stored In Database

With Docker running, open phpMyAdmin:

```text
http://localhost:8082
```

Login using the values from your private `.env`:

```text
Server: db
Username: MYSQL_USER
Password: MYSQL_PASSWORD
Database: MYSQL_DATABASE
```

Check these tables:

- `admins`: admin account row after running `scripts/create_admin.php`
- `students`: student row after registration
- `election_categories`: category row after admin creates a category
- `candidates`: candidate row after admin adds a candidate
- `votes`: vote row after student votes
- `audit_logs`: background record of important actions

You can also verify from PowerShell:

```powershell
$vars = Get-Content .env | Where-Object { $_ -match '^[A-Za-z_][A-Za-z0-9_]*=' } | ConvertFrom-StringData
docker compose exec db mysql -u $vars.MYSQL_USER "-p$($vars.MYSQL_PASSWORD)" $vars.MYSQL_DATABASE -e "SELECT COUNT(*) AS students FROM students; SELECT COUNT(*) AS admins FROM admins; SELECT COUNT(*) AS votes FROM votes;"
```

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

Short version:

1. System owner creates admin.
2. Admin logs in with email verification.
3. Admin creates election categories.
4. Admin enrolls candidates.
5. Student registers.
6. Admin activates student.
7. Student logs in.
8. Student votes once per category.
9. Results are calculated from the `votes` table.

## Important Files

- `database/schema.sql`: database structure with no seeded passwords
- `database/production_schema.sql`: production database structure
- `scripts/create_admin.php`: creates or updates the real admin account
- `docs/RENDER_DEPLOY.md`: Render deployment guide
- `docs/RENDER_ENV_VARS.md`: Render database, SMTP, and admin environment variable checklist
- `docs/DOCKER_LOCAL.md`: local Docker guide
- `Dockerfile`: PHP Apache Docker image
- `docker-compose.yml`: local app, MySQL, and phpMyAdmin setup
- `config/config.php`: environment and database settings
- `includes/auth.php`: login, guards, email-code verification, logout, audit logging
- `includes/helpers.php`: validation, CSRF, mail sending, and formatting helpers
