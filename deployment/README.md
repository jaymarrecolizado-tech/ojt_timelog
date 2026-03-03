# Deployment Folder Contents

This folder contains all the files you need to deploy the OJT TimeLog app to Hostinger.

## Files

1. **DEPLOYMENT_GUIDE.md** - Complete step-by-step deployment instructions
2. **.env.production** - Production environment configuration template
3. **.htaccess** - Apache web server configuration
4. **deploy.sh** - Automated deployment script (for Linux servers)
5. **.deployignore** - Files to exclude during deployment

## Quick Start

### Option 1: Manual Deployment

1. Upload ALL project files (except this deployment folder) to your server
2. Follow the instructions in DEPLOYMENT_GUIDE.md

### Option 2: Automated Deployment

1. Upload ALL project files (except this deployment folder) to your server
2. Upload `deploy.sh` to your server
3. Make it executable: `chmod +x deploy.sh`
4. Run it: `./deploy.sh`
5. Update `.env` file with your database credentials

### Option 3: Git Deployment (Recommended)

```bash
# On your server
cd /path/to/domain/public
git clone https://github.com/jaymarrecolizado-tech/ojt_timelog.git .
git checkout master
composer install --optimize-autoloader --no-dev
cp .env.production .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Important Notes

### Before Deployment

- [ ] Create MySQL database on Hostinger
- [ ] Note down database credentials (host, name, username, password)
- [ ] Ensure domain points to correct directory
- [ ] Check PHP version (must be 8.1+)

### After Deployment

- [ ] Change default admin password immediately
- [ ] Update email settings in `.env`
- [ ] Test all features (login, scanning, etc.)
- [ ] Set up regular database backups
- [ ] Monitor application logs

### Default Credentials

**Admin:** admin@timelog.com / Adm1n@TLMS2026!
**Guard:** GUARD-001@guard.timelog.com / Gu4rd@TLMS2026!
**Student:** 2024-003@student.timelog.com / StuD3nt@TLMS2026!

⚠️ **CHANGE THESE PASSWORDS IMMEDIATELY AFTER DEPLOYMENT!**

## File Structure for Deployment

```
/app
/bootstrap
/config
/database
/public
/resources
/routes
/storage
/vendor
.env.production → rename to .env
.htaccess → copy to public/ folder
artisan
composer.json
composer.lock
```

## Common Issues & Solutions

### 500 Internal Server Error

**Cause:** Missing `.env` file or incorrect permissions

**Solution:**
```bash
cp .env.production .env
php artisan key:generate
chmod -R 755 storage bootstrap/cache
```

### Database Connection Failed

**Cause:** Incorrect database credentials in `.env`

**Solution:** Update `.env` with correct database settings

### 404 Not Found

**Cause:** `.htaccess` not in `public/` folder or mod_rewrite not enabled

**Solution:**
```bash
# Copy .htaccess to public/ folder
cp deployment/.htaccess public/.htaccess

# Enable mod_rewrite (on Debian/Ubuntu)
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Storage Files Not Accessible

**Cause:** Missing storage link or incorrect permissions

**Solution:**
```bash
php artisan storage:link
chmod -R 755 public/storage
```

## Hosting-Specific Settings

### Hostinger KVM 2

**Document Root:** `/path/to/domain/public`
**PHP Version:** 8.1 or higher
**Database:** MySQL or MariaDB

**Recommended Settings:**
- Memory Limit: 256M
- Max Execution Time: 300
- File Uploads: 10M
- Post Max Size: 10M

## Security Checklist

- [ ] APP_DEBUG is set to `false` in `.env`
- [ ] HTTPS is enabled (SSL certificate)
- [ ] Default passwords are changed
- [ ] File permissions are set correctly
- [ ] `.env` file is not accessible from web
- [ ] `.git` folder is not accessible from web
- [ ] Regular backups are scheduled

## Support

For detailed instructions, see: **DEPLOYMENT_GUIDE.md**

---

**Last Updated:** March 3, 2026
**Domain:** https://ojtlog.dictr2.online/
