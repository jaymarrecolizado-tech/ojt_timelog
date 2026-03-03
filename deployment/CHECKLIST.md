# Pre-Deployment Checklist

Complete this checklist BEFORE deploying to https://ojtlog.dictr2.online/

## Server Requirements

- [ ] Hostinger KVM 2 server access (SSH or File Manager)
- [ ] Domain https://ojtlog.dictr2.online/ configured and pointing to server
- [ ] PHP 8.1 or higher installed
- [ ] MySQL or MariaDB database created
- [ ] Database credentials noted:
  - Database Name: __________________________
  - Database User: _________________________
  - Database Password: _____________________
  - Database Host: _________________________ (usually localhost)

## Database Preparation

- [ ] MySQL database created on Hostinger
- [ ] Database user created with appropriate permissions
- [ ] Database tested for connectivity (optional but recommended)

## Email Configuration (if using email features)

- [ ] SMTP server details available
  - SMTP Host: __________________________
  - SMTP Port: __________________________
  - SMTP Username: ______________________
  - SMTP Password: _____________________
  - Encryption: _________________________

## Security Setup

- [ ] SSL/HTTPS certificate enabled on domain
- [ ] Strong admin password ready (minimum 12 chars, mixed case, numbers, symbols)
- [ ] Application key will be generated during deployment

## File Preparation

- [ ] All necessary files copied from project
- [ ] `.env.production` ready with database credentials
- [ ] `.htaccess` file prepared for Apache configuration

## Testing Plan

After deployment, test these features:

### Authentication
- [ ] Admin login works
- [ ] Guard login works
- [ ] Student login works
- [ ] Logout works correctly

### Admin Features
- [ ] Dashboard loads correctly
- [ ] Students list displays
- [ ] Can create new student
- [ ] Can edit student details
- [ ] Can add manual time logs
- [ ] Reports generate correctly
- [ ] Settings page works

### Guard Features
- [ ] Guard dashboard loads
- [ ] QR code displays and refreshes
- [ ] Manual QR code displays
- [ ] Copy button works

### Student Features
- [ ] Student dashboard loads
- [ ] View logs works
- [ ] Scan QR page loads
- [ ] Manual QR entry works
- [ ] Profile page displays

### Location Features
- [ ] Location management works
- [ ] Maps display correctly
- [ ] Can create/edit/delete locations

## Post-Deployment Actions

- [ ] Change default admin password
- [ ] Update email settings in `.env`
- [ ] Test all features end-to-end
- [ ] Set up regular database backups
- [ ] Configure monitoring (if desired)
- [ ] Update documentation with production-specific settings

## Backup Strategy

- [ ] Database backup plan in place
- [ ] File backup plan in place
- [ ] Backup schedule determined
- [ ] Off-site backup location identified

## Performance Optimization

- [ ] Configuration cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Views cached (`php artisan view:cache`)
- [ ] Application optimized (`php artisan optimize`)

## Security Verification

- [ ] `APP_DEBUG=false` in `.env`
- [ ] File permissions correct (755 for most, 775 for storage)
- [ ] `.env` file not accessible via web
- [ ] `.git` folder not accessible via web
- [ ] Only necessary ports open
- [ ] HTTPS properly configured

## Monitoring Setup

- [ ] Application logs configured
- [ ] Error monitoring setup (optional)
- [ ] Uptime monitoring setup (optional)
- [ ] Log rotation configured (optional)

## Documentation

- [ ] Deployment guide reviewed
- [ ] Troubleshooting guide reviewed
- [ ] Emergency contact procedures documented
- [ ] Rollback plan documented

## Rollback Plan

In case of issues:

- [ ] Previous version backed up
- [ ] Rollback procedure documented
- [ ] Quick rollback method tested

## Contacts

- [ ] Hostinger support: https://support.hostinger.com/
- [ ] Laravel documentation: https://laravel.com/docs
- [ ] Project repository: https://github.com/jaymarrecolizado-tech/ojt_timelog

---

## Deployment Timeline

**Estimated Time:** 30-60 minutes

1. **Preparation:** 5-10 minutes (checklist above)
2. **File Upload:** 5-15 minutes (depending on connection)
3. **Database Setup:** 5-10 minutes
4. **Configuration:** 5-10 minutes
5. **Testing:** 10-15 minutes

## Emergency Contacts

If deployment fails:

1. Check `storage/logs/laravel.log` for errors
2. Verify database connection in `.env`
3. Check file permissions
4. Review DEPLOYMENT_GUIDE.md troubleshooting section
5. Contact Hostinger support if server issues

---

**Checklist Version:** 1.0
**Last Updated:** March 3, 2026
**Domain:** https://ojtlog.dictr2.online/
