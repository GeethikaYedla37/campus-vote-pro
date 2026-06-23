# Run CampusVote Pro With XAMPP

Use this guide when you want to run the project only on your laptop with Apache, MySQL, and phpMyAdmin.

## 1. Copy Project

Copy the project folder to:

```text
C:\xampp\htdocs\campus-vote-pro
```

## 2. Start XAMPP

Open XAMPP Control Panel and start:

```text
Apache
MySQL
```

This laptop's Apache is configured on port `8080`. If Apache says that port is busy, change Apache to another port in XAMPP or close the app using port `8080`.

## 3. Create Local Environment File

In the project folder, copy:

```text
.env.example
```

Rename the copy to:

```text
.env
```

For this laptop's XAMPP, keep these database values:

```text
APP_ENV=local
DB_HOST=localhost
DB_PORT=3305
DB_NAME=campus_vote_pro
DB_USER=root
DB_PASS=
```

If your XAMPP control panel shows a different MySQL port later, change only `DB_PORT`.

Do not push `.env` to GitHub.

## 4. Import Database In phpMyAdmin

Open:

```text
http://localhost:8080/phpmyadmin
```

Then:

1. Click **Import**.
2. Choose `database/schema.sql`.
3. Click **Go**.

This creates the database:

```text
campus_vote_pro
```

## 5. Add Email Settings

Admin login needs an email verification code. Add real SMTP values in `.env`.

For Gmail, use a Gmail App Password, not your normal Gmail password.

```text
MAIL_TRANSPORT=smtp
MAIL_FROM_ADDRESS=<your Gmail address>
MAIL_FROM_NAME=CampusVote Pro
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=<your Gmail address>
SMTP_PASSWORD=<your Gmail app password>
SMTP_ENCRYPTION=tls
```

## 6. Create First Admin

Open:

```text
http://localhost:8080/campus-vote-pro/setup.php
```

Create the admin account. The password is stored as a hash.

After one admin exists, `setup.php` locks itself and does not create more admins.

## 7. Open Website

```text
Home: http://localhost:8080/campus-vote-pro/
Admin: http://localhost:8080/campus-vote-pro/admin/login.php
Student: http://localhost:8080/campus-vote-pro/student/login.php
phpMyAdmin: http://localhost:8080/phpmyadmin
```

## 8. Normal App Flow

1. Admin logs in with email code.
2. Admin creates election category.
3. Admin adds candidates.
4. Student registers.
5. Admin activates student.
6. Student logs in and votes.
7. Results update from the `votes` table.
8. Student feedback messages are submitted inside the student dashboard.
9. Admin reviews feedback from the admin support page.
10. When admin changes feedback status, the student reply email receives the selected status.

## Database Tables

- `admins`
- `students`
- `election_categories`
- `candidates`
- `votes`
- `feedback_messages`
- `audit_logs`
- `login_attempts`

## Safety

- Student and admin passwords are hashed.
- SQL uses prepared queries.
- Forms use CSRF tokens.
- Admin login uses email verification.
- Repeated login failures are rate-limited.
- Candidate uploads are validated.
- Duplicate votes are blocked by the database.
