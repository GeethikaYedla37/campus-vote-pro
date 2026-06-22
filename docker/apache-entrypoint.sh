#!/bin/sh
set -eu

: "${PORT:=10000}"
: "${UPLOAD_PATH:=/var/www/html/uploads/candidates}"

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
printf '%s\n' 'ServerName localhost' > /etc/apache2/conf-available/server-name.conf
a2enconf server-name >/dev/null

mkdir -p "$UPLOAD_PATH"
chown -R www-data:www-data "$UPLOAD_PATH"

if [ "$UPLOAD_PATH" = "/var/www/html/uploads/candidates" ] && [ ! -f "$UPLOAD_PATH/.htaccess" ]; then
    cat > "$UPLOAD_PATH/.htaccess" <<'EOF'
Options -Indexes

<FilesMatch "\.(php|phtml|phar)$">
    Require all denied
</FilesMatch>
EOF
    chown www-data:www-data "$UPLOAD_PATH/.htaccess"
fi

exec apache2-foreground
