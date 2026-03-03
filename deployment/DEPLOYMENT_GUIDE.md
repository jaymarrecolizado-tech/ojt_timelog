# Laravel App Deployment Guide

**Domain:** https://ojtlog.dictr2.online/
**PHP Version:** 8.1 or higher
**Database:** MySQL (recommended) or SQLite

## Prerequisites

1. SSH access to your Hostinger KVM 2 server
2. Domain configured to point to your server
3. PHP 8.1+ installed
4. Composer installed
5. MySQL database created (or use SQLite)

## Deployment Steps

### 1. Upload Files to Server

```bash
# Upload all files (except deployment folder) to your server's public_html or document root
# Use FileZilla, SCP, or Git

# Option 1: Using SCP
scp -r ./* user@your-server:/path/to/domain/public/

# Option 2: Using Git (recommended)
git clone https://github.com/jaymarrecolizado-tech/ojt_timelog.git
cd ojt_timelog
git checkout master
```

### 2. Set File Permissions

```bash
cd /path/to/domain/public
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Install Dependencies

```bash
# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Generate application key (if not set)
php artisan key:generate
```

### 4. Configure Environment

```bash
# Copy example .env
cp .env.production .env

# Edit .env with your database settings
nano .env
```

Update these settings in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ojtlog.dictr2.online

# Database Settings (choose one)
# Option 1: MySQL (recommended for production)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Option 2: SQLite (simpler, but less suitable for production)
DB_CONNECTION=sqlite
#DB_HOST=127.0.0.1
#DB_PORT=3306
#DB_DATABASE=ojt_timelog
#DB_USERNAME=root
#DB_PASSWORD=

# Keep other settings as needed
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
```

### 5. Run Database Migrations

```bash
# Run migrations
php artisan migrate --force

# Run seeders (optional, only for initial setup)
php artisan db:seed --force
```

### 6. Clear and Cache

```bash
# Clear and cache configuration
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear view cache
php artisan view:clear
php artisan view:cache

# Clear application cache
php artisan cache:clear
```

### 7. Set Up Link for Storage

```bash
# Create symbolic link for public storage
php artisan storage:link
```

### 8. Configure Apache (.htaccess)

If using Apache, ensure `.htaccess` exists in `public/` folder with:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]

    # Security headers
    <IfModule mod_headers.c>
        Header set X-Content-Type-Options "nosniff"
        Header set X-Frame-Options "SAMEORIGIN"
        Header set X-XSS-Protection "1; mode=block"
    </IfModule>
</IfModule>
```

### 9. Update Admin Passwords (Important!)

After deployment, update default passwords:

```bash
php artisan tinker --execute="
\$admin = \App\Models\User::where('email', 'admin@timelog.com')->first();
if (\$admin) {
    \$admin->password_hash = \Illuminate\Support\Facades\Hash::make('YOUR_NEW_PASSWORD');
    \$admin->save();
    echo 'Admin password updated\n';
}
"
```

## Post-Deployment Checklist

- [ ] Application loads correctly at https://ojtlog.dictr2.online/
- [ ] Login works with admin account
- [ ] Database migrations ran successfully
- [ ] File permissions are correct
- [ ] HTTPS is properly configured
- [ ] Email settings are configured (if using email features)
- [ ] Default passwords are changed

## Troubleshooting

### 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check file permissions
ls -la storage/
ls -la bootstrap/cache/
```

### Database Connection Issues

```bash
# Test database connection
php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo 'Database connection successful!\n';
} catch (\Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage() . '\n';
}
"
```

### Permission Issues

```bash
# Fix common permission issues
sudo chown -R www-data:www-data /path/to/domain
sudo chmod -R 755 /path/to/domain
sudo chmod -R 775 /path/to/domain/storage
sudo chmod -R 775 /path/to/domain/bootstrap/cache
```

### Clear All Caches

```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Security Recommendations

1. **Disable debug mode** in production (already set in `.env.production`)
2. **Use strong passwords** for all accounts
3. **Enable HTTPS** (SSL certificate)
4. **Regularly update** Laravel and dependencies
5. **Backup database** regularly
6. **Monitor logs** for suspicious activity
7. **Restrict access** to sensitive areas (admin panel)

## Default Credentials

After deployment, you can access with:

**Admin:**
- Email: admin@timelog.com
- Password: Adm1n@TLMS2026! (change this immediately)

**Guard:**
- Email: GUARD-001@guard.timelog.com
- Password: Gu4rd@TLMS2026!

**Student:**
- Email: 2024-003@student.timelog.com
- Password: StuD3nt@TLMS2026!

## Monitoring

### Check Application Health

```bash
# Check if Laravel is running
php artisan --version

# Check queue workers (if using)
php artisan queue:work --status

# Check scheduled tasks
php artisan schedule:list
```

### Log Files

- Application logs: `storage/logs/laravel.log`
- PHP error logs: `/var/log/apache2/error.log` (or server equivalent)

## Backup Strategy

### Database Backup

```bash
# For MySQL
mysqldump -u username -p database_name > backup.sql

# For SQLite
cp database/database.sqlite backup.sqlite
```

### Files Backup

```bash
# Backup entire project
tar -czf backup_$(date +%Y%m%d).tar.gz /path/to/domain/public
```

## Support

For issues or questions, check:
- Laravel documentation: https://laravel.com/docs
- Hostinger documentation: https://support.hostinger.com/

---

**Last Updated:** March 3, 2026
