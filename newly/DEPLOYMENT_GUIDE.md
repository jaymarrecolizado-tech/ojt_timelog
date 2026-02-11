# DEPLOYMENT GUIDE - Hostinger VPS KVM2 (Ubuntu)
## OJT Time Log Management System

---

## 📋 PRE-DEPLOYMENT CHECKLIST

✅ All 12 critical security issues have been fixed:
- [x] Default credentials removed (using env variables)
- [x] Hardcoded location secrets replaced with cryptographically secure keys
- [x] APP_DEBUG set to false for production
- [x] Rate limiting added to auth routes (5 attempts/minute login, 3/hour register)
- [x] Session encryption enabled
- [x] QR tokens now use HMAC-SHA256 signing
- [x] Password policy strengthened (12 chars, uppercase, lowercase, number, special char)
- [x] CORS locked down to specific origins
- [x] Security headers middleware added (CSP, HSTS, X-Frame-Options, etc.)
- [x] Account lockout mechanism implemented (5 attempts = 30 min lockout)
- [x] Manual log input validation fixed (time format, XSS prevention)
- [x] Session fixation vulnerability fixed

---

## 🚀 STEP-BY-STEP DEPLOYMENT

### STEP 1: Prepare Your Local Environment

1. **Update .env.production file** with your actual values:
```bash
# Copy the template
cp .env.production .env

# Edit with your values
nano .env
```

2. **Required environment variables**:
```env
APP_NAME="OJT Time Log Management System"
APP_ENV=production
APP_KEY=  # Generate this in Step 3
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ojt_timelog
DB_USERNAME=ojt_user
DB_PASSWORD=your_secure_password_here

DEFAULT_ADMIN_EMAIL=admin@your-domain.com
DEFAULT_ADMIN_PASSWORD=YourSecureAdminPassword123!
```

---

### STEP 2: Connect to Hostinger VPS

```bash
# SSH into your VPS (replace with your actual IP)
ssh root@YOUR_SERVER_IP

# Update system
apt update && apt upgrade -y

# Install required packages
apt install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-bcmath php8.1-curl php8.1-zip unzip git
```

---

### STEP 3: Configure MySQL Database

```bash
# Secure MySQL installation
mysql_secure_installation

# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE ojt_timelog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ojt_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON ojt_timelog.* TO 'ojt_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### STEP 4: Upload Application Files

**Option A: Using Git (Recommended)**
```bash
# On your VPS
cd /var/www
git clone https://github.com/yourusername/ojt-timelog.git
mv ojt-timelog ojt-timelog

# Or if using private repo
git clone git@github.com:yourusername/ojt-timelog.git
```

**Option B: Using SCP/SFTP**
```bash
# From your local machine
scp -r /path/to/your/project/* root@YOUR_SERVER_IP:/var/www/ojt-timelog/
```

---

### STEP 5: Install Dependencies & Configure

```bash
cd /var/www/ojt-timelog

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
chown -R www-data:www-data /var/www/ojt-timelog
chmod -R 755 /var/www/ojt-timelog
chmod -R 775 /var/www/ojt-timelog/storage
chmod -R 775 /var/www/ojt-timelog/bootstrap/cache

# Copy environment file
cp .env.production .env

# Generate application key
php artisan key:generate

# Update .env with your database credentials and domain
nano .env
```

---

### STEP 6: Run Migrations

```bash
# Run database migrations
php artisan migrate --force

# IMPORTANT: Create admin user manually since we removed default credentials
# Login to MySQL and create admin:
mysql -u root -p
```

```sql
USE ojt_timelog;

-- Generate UUID for admin
-- You can generate one at: https://www.uuidgenerator.net/
INSERT INTO users (id, email, password_hash, role, is_active, email_verified, created_at, updated_at) 
VALUES (
    'YOUR-GENERATED-UUID-HERE',
    'admin@your-domain.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- This is hash for 'Password123!'
    'super_admin',
    1,
    1,
    NOW(),
    NOW()
);

EXIT;
```

**Note**: The password hash above is for 'Password123!'. To create your own:
```bash
php -r "echo password_hash('YourSecurePassword', PASSWORD_BCRYPT);"
```

---

### STEP 7: Configure Nginx

Create Nginx configuration:
```bash
nano /etc/nginx/sites-available/ojt-timelog
```

Add this configuration:
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/ojt-timelog/public;

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers (already handled by Laravel, but double protection)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
}
```

Enable the site:
```bash
ln -s /etc/nginx/sites-available/ojt-timelog /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

---

### STEP 8: Setup SSL (HTTPS) - ESSENTIAL FOR PRODUCTION

```bash
# Install Certbot
apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
certbot renew --dry-run
```

---

### STEP 9: Optimize for Production

```bash
cd /var/www/ojt-timelog

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions again
chown -R www-data:www-data /var/www/ojt-timelog
chmod -R 755 /var/www/ojt-timelog
chmod -R 775 /var/www/ojt-timelog/storage
chmod -R 775 /var/www/ojt-timelog/bootstrap/cache
```

---

### STEP 10: Setup Firewall

```bash
# Install and configure UFW
apt install -y ufw

# Allow necessary ports
ufw allow OpenSSH
ufw allow 'Nginx Full'

# Enable firewall
ufw enable

# Check status
ufw status
```

---

### STEP 11: Setup Automatic Backups

```bash
# Install backup script
cat > /usr/local/bin/backup-ojt.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/ojt-timelog"
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u root -p'YOUR_MYSQL_ROOT_PASSWORD' ojt_timelog > $BACKUP_DIR/db_backup_$DATE.sql

# Backup application files
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz -C /var/www ojt-timelog

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
EOF

chmod +x /usr/local/bin/backup-ojt.sh

# Add to crontab (daily at 2 AM)
echo "0 2 * * * /usr/local/bin/backup-ojt.sh" | crontab -
```

---

### STEP 12: Post-Deployment Verification

**Checklist**:
- [ ] Website loads over HTTPS (no mixed content warnings)
- [ ] Login works with admin credentials
- [ ] Session persists across pages
- [ ] QR code generation works
- [ ] Rate limiting active (test with wrong password 6 times)
- [ ] Account lockout works (test with wrong password 5 times)
- [ ] Security headers present (check with securityheaders.com)
- [ ] Database backups running
- [ ] SSL certificate auto-renews

**Test Security Headers**:
```bash
curl -I https://your-domain.com
```

You should see:
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy

---

## 🔐 SECURITY REMINDERS

1. **Change admin password immediately after first login**
2. **Enable 2FA if available** (future enhancement)
3. **Monitor logs regularly**:
   ```bash
   tail -f /var/www/ojt-timelog/storage/logs/laravel.log
   ```
4. **Keep system updated**:
   ```bash
   apt update && apt upgrade -y
   ```
5. **Never commit .env file to git**
6. **Use strong passwords** (already enforced by application)

---

## 📊 MONITORING & MAINTENANCE

**Check application status**:
```bash
systemctl status nginx
systemctl status php8.1-fpm
systemctl status mysql
```

**View logs**:
```bash
# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Application logs
tail -f /var/www/ojt-timelog/storage/logs/laravel.log
```

**Clear caches after updates**:
```bash
cd /var/www/ojt-timelog
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🆘 TROUBLESHOOTING

**Issue: 500 Internal Server Error**
```bash
# Check permissions
chown -R www-data:www-data /var/www/ojt-timelog
chmod -R 755 /var/www/ojt-timelog

# Check logs
tail -f /var/www/ojt-timelog/storage/logs/laravel.log
```

**Issue: Database connection failed**
```bash
# Test MySQL connection
mysql -u ojt_user -p -e "SHOW DATABASES;"

# Check .env file
cat /var/www/ojt-timelog/.env | grep DB_
```

**Issue: Permission denied on storage**
```bash
chmod -R 775 /var/www/ojt-timelog/storage
chmod -R 775 /var/www/ojt-timelog/bootstrap/cache
chown -R www-data:www-data /var/www/ojt-timelog
```

---

## ✅ DEPLOYMENT COMPLETE!

Your OJT Time Log Management System is now:
- ✅ Secure (all 12 critical issues fixed)
- ✅ Deployed on Hostinger VPS
- ✅ Running with SSL/HTTPS
- ✅ Protected by firewall
- ✅ Automatically backing up
- ✅ Ready for production use

**URL**: https://your-domain.com

---

## 📞 SUPPORT

If you encounter issues:
1. Check logs first: `/var/www/ojt-timelog/storage/logs/laravel.log`
2. Verify all environment variables are set correctly
3. Ensure all services are running (nginx, php-fpm, mysql)
4. Check firewall rules

---

**Deployment Date**: _______________
**Deployed By**: _______________
**Server IP**: _______________
**Domain**: _______________

