# OJT TimeLog - Deployment Package

**Domain:** https://ojtlog.dictr2.online/
**Deployment Date:** March 3, 2026

## Quick Start

### Option 1: Create Deployment Package (Recommended)

**For Windows:**
```cmd
cd deployment
create_package.bat
```

**For Linux/Mac:**
```bash
cd deployment
chmod +x create_package.sh
./create_package.sh
```

This creates a zip/tar.gz file containing all necessary files.

### Option 2: Direct Upload

Upload ALL project files (except the `deployment/` folder) to your server.

### Option 3: Git Clone

```bash
git clone https://github.com/jaymarrecolizado-tech/ojt_timelog.git
cd ojt_timelog
```

## Deployment Files

| File | Purpose |
|------|---------|
| **README.md** | Quick start guide and file overview |
| **DEPLOYMENT_GUIDE.md** | Complete step-by-step deployment instructions |
| **CHECKLIST.md** | Pre-deployment and post-deployment checklist |
| **.env.production** | Production environment configuration template |
| **.htaccess** | Apache web server configuration |
| **deploy.sh** | Automated deployment script (Linux/Mac) |
| **create_package.bat** | Create deployment package (Windows) |
| **create_package.sh** | Create deployment package (Linux/Mac) |
| **.deployignore** | Files to exclude during deployment |

## Step-by-Step Deployment

### 1. Prepare Your Server

- Create MySQL database on Hostinger
- Note database credentials (name, user, password, host)
- Ensure PHP 8.1+ is installed
- Enable HTTPS/SSL on domain

### 2. Upload Files

**Option A: Upload Deployment Package**
1. Run `create_package.bat` (Windows) or `create_package.sh` (Linux/Mac)
2. Upload the created zip/tar.gz file to server
3. Extract to domain's public folder

**Option B: Direct Upload**
1. Upload all project files via File Manager or FTP
2. Exclude `deployment/` folder

**Option C: Git Clone**
```bash
ssh user@your-server
cd /path/to/domain/public
git clone https://github.com/jaymarrecolizado-tech/ojt_timelog.git .
```

### 3. Configure Environment

```bash
# Copy production .env
cp .env.production .env

# Edit with your settings
nano .env
```

Update in `.env`:
- `APP_URL=https://ojtlog.dictr2.online`
- Database credentials
- Email settings (if needed)
- Change default passwords

### 4. Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
php artisan key:generate
```

### 5. Run Database Migrations

```bash
php artisan migrate --force
php artisan db:seed --force  # Optional, for sample data
```

### 6. Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Optimize Application

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 8. Test Deployment

Visit https://ojtlog.dictr2.online/ and test:
- [ ] Admin login: admin@timelog.com / Adm1n@TLMS2026!
- [ ] Guard login: GUARD-001@guard.timelog.com / Gu4rd@TLMS2026!
- [ ] Student login: 2024-003@student.timelog.com / StuD3nt@TLMS2026!

## IMPORTANT: Change Default Passwords!

After successful deployment, change all default passwords:

```bash
php artisan tinker --execute="
\$admin = \App\Models\User::where('email', 'admin@timelog.com')->first();
\$admin->password_hash = \Illuminate\Support\Facades\Hash::make('YOUR_NEW_ADMIN_PASSWORD');
\$admin->save();
echo 'Admin password updated\n';

\$guard = \App\Models\User::where('email', 'GUARD-001@guard.timelog.com')->first();
\$guard->password_hash = \Illuminate\Support\Facades\Hash::make('YOUR_NEW_GUARD_PASSWORD');
\$guard->save();
echo 'Guard password updated\n';
"
```

## File Structure After Deployment

```
/public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── index.php
│   └── .htaccess
├── resources/
├── routes/
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── vendor/
├── .env (from .env.production)
├── .gitattributes
├── artisan
├── composer.json
└── composer.lock
```

## Troubleshooting

### 500 Internal Server Error
- Check `.env` exists and has correct APP_KEY
- Check file permissions (755 for storage)
- Review `storage/logs/laravel.log`

### Database Connection Failed
- Verify database credentials in `.env`
- Test database connection
- Check if database exists

### 404 Not Found
- Ensure `.htaccess` is in `public/` folder
- Check mod_rewrite is enabled
- Verify domain points to correct directory

### Camera Not Working
- Browsers block camera on HTTP
- Use manual QR entry feature
- Requires HTTPS for camera access

## Default Credentials

⚠️ **CHANGE THESE IMMEDIATELY AFTER DEPLOYMENT!**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@timelog.com | Adm1n@TLMS2026! |
| Guard | GUARD-001@guard.timelog.com | Gu4rd@TLMS2026! |
| Student | 2024-003@student.timelog.com | StuD3nt@TLMS2026! |

## Security Checklist

- [ ] `APP_DEBUG=false` in `.env`
- [ ] HTTPS enabled
- [ ] Default passwords changed
- [ ] File permissions correct
- [ ] `.env` not web-accessible
- [ ] `.git` not web-accessible
- [ ] Regular backups configured

## Backup Strategy

### Database Backup
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### Files Backup
```bash
tar -czf backup_$(date +%Y%m%d).tar.gz /path/to/domain/public
```

### Schedule Backup (Cron Job)
```bash
# Daily database backup at 2 AM
0 2 * * * mysqldump -u username -ppassword database_name > /path/to/backups/db_$(date +\%Y\%m\%d).sql

# Weekly file backup on Sunday at 3 AM
0 3 * * 0 tar -czf /path/to/backups/files_$(date +\%Y\%m\%d).tar.gz /path/to/domain/public
```

## Support Resources

- **Complete Guide:** See `DEPLOYMENT_GUIDE.md`
- **Checklist:** See `CHECKLIST.md`
- **Laravel Docs:** https://laravel.com/docs
- **Hostinger Support:** https://support.hostinger.com/
- **GitHub Repo:** https://github.com/jaymarrecolizado-tech/ojt_timelog

## Quick Commands Reference

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check Laravel version
php artisan --version

# Test database connection
php artisan tinker --execute="
try { \DB::connection()->getPdo(); echo 'Connected!\n'; }
catch (\Exception \$e) { echo 'Failed: ' . \$e->getMessage() . '\n'; }
"

# View logs
tail -f storage/logs/laravel.log

# Restart queue workers
php artisan queue:restart
```

---

**Version:** 1.0
**Last Updated:** March 3, 2026
**Domain:** https://ojtlog.dictr2.online/
**PHP Version:** 8.1+
**Laravel Version:** 10.x
