# Run CampusVote Pro With Docker

Docker runs the website, MySQL database, and phpMyAdmin together without XAMPP.

## First Time Setup

1. Open Docker Desktop and wait until it is running.
2. Open PowerShell in the project folder:

```powershell
cd "C:\Users\ykani\Documents\New project\campus-vote-pro"
```

3. Create your private local environment file:

```powershell
copy .env.example .env
```

4. Open `.env` and replace every placeholder with your own values.

Important: `.env` is ignored by Git. Do not push it to GitHub.

5. Build and start everything:

```powershell
docker compose up -d --build
```

## URLs

```text
Website: http://localhost:8081
phpMyAdmin: http://localhost:8082
MySQL from your computer: localhost:3307
```

## Create The Admin Account

After Docker is running, create your real admin:

```powershell
docker compose exec -e ADMIN_NAME="Election Administrator" -e ADMIN_EMAIL="admin@yourcollege.edu" -e ADMIN_PASSWORD="Use_A_Long_Strong_Private_Password" app php scripts/create_admin.php
```

The admin password is stored as a hash. The real password is not stored in plain text.

## Admin Email Code

Admin login sends a 6-digit verification code to `ADMIN_EMAIL`.

For this to work, `.env` must contain real SMTP settings:

```text
MAIL_TRANSPORT=smtp
MAIL_FROM_ADDRESS=admin@yourcollege.edu
SMTP_HOST=smtp.your-provider.com
SMTP_PORT=587
SMTP_USERNAME=your_smtp_username
SMTP_PASSWORD=your_smtp_password_or_app_password
SMTP_ENCRYPTION=tls
```

You can use an SMTP provider such as Gmail app password, Brevo, Mailgun, SendGrid, or your college mail server.

After setting SMTP values, test email delivery:

```powershell
docker compose exec app php scripts/test_mail.php
```

## phpMyAdmin Login

Use the same values you placed in `.env`:

```text
Server: db
Username: MYSQL_USER
Password: MYSQL_PASSWORD
Database: MYSQL_DATABASE
```

## Stop Docker

```powershell
docker compose down
```

## Refresh Docker After Code Changes

This keeps database data:

```powershell
docker compose up -d --build
```

## Fully Reset Database

Warning: this deletes Docker database data and uploaded candidate images.

```powershell
docker compose down -v
docker compose up -d --build
```

The database is created again from:

```text
database/schema.sql
```

## Common Errors

### Docker engine is not running

Open Docker Desktop, wait until it is running, then run Docker Compose again.

### Missing variable in .env

If Docker says a variable is not set, open `.env` and fill the missing value.

### Port already used

This setup uses:

```text
8081 = website
8082 = phpMyAdmin
3307 = MySQL
```

If one is already used, edit the left side of the port mapping in `docker-compose.yml`.
