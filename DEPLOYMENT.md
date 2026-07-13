# RentFlow Production Deployment Guide

## Pre-Deployment Checklist

### 1. Security

- [ ] Generate secure JWT secret: `openssl rand -base64 32`
- [ ] Update all credentials in `.env`
- [ ] Remove all debug keys and demo data
- [ ] Enable HTTPS/SSL certificate
- [ ] Update CORS allowed origins
- [ ] Configure firewall rules
- [ ] Set up IP whitelisting if needed
- [ ] Review and update authentication timeouts

### 2. Database

- [ ] Back up production database
- [ ] Run all migrations: `php backend/database/migrate.php`
- [ ] Verify database indexes
- [ ] Set up automated backups
- [ ] Test database failover

### 3. Server Setup

- [ ] Ensure mod_rewrite is enabled: `a2enmod rewrite`
- [ ] Ensure mod_headers is enabled: `a2enmod headers`
- [ ] Configure SSL/TLS certificates
- [ ] Set up log rotation
- [ ] Verify PHP version >= 8.0
- [ ] Install required PHP extensions (e.g., PDO, OpenSSL)

### 4. File Permissions

```bash
chmod 755 /var/www/rentflow
chmod 755 /var/www/rentflow/frontend/public
chmod 755 /var/www/rentflow/backend/public
chmod 755 /var/www/rentflow/logs
chmod 755 /var/www/rentflow/backend/public/uploads
chmod 644 /var/www/rentflow/frontend/public/.htaccess
chmod 644 /var/www/rentflow/backend/public/.htaccess
```

### 5. Environment Configuration

- [ ] Copy `.env.production` to `.env`
- [ ] Update database credentials
- [ ] Update email settings
- [ ] Update API URLs
- [ ] Set `APP_ENV=production`
- [ ] Never commit `.env` to version control

### 6. Logging & Monitoring

- [ ] Configure error logging
- [ ] Set up access logs
- [ ] Configure log rotation
- [ ] Set up error alerts
- [ ] Install monitoring tools (e.g., New Relic, Datadog)
- [ ] Set up uptime monitoring

## Deployment Steps

### 1. Upload Code

```bash
scp -r /opt/lampp/htdocs/RentFlow/* user@server:/var/www/rentflow/
```

### 2. Run Deployment Script

```bash
cd /var/www/rentflow
chmod +x deploy-production.sh
./deploy-production.sh
```

### 3. Install Dependencies

```bash
cd backend
composer install --no-dev --optimize-autoloader
cd ..
```

### 4. Configure Environment

```bash
cp .env.production .env
nano .env  # Update with your production values
```

### 5. Run Migrations

```bash
php backend/database/migrate.php
```

### 6. Test Application

```bash
# Test homepage
curl https://yourdomain.com/

# Test API
curl https://yourdomain.com/api/health

# Test login
curl -X POST https://yourdomain.com/api/login
```

## Post-Deployment

1. **Verify Functionality**
   - Test user login/signup
   - Test all main features
   - Test file uploads
   - Verify email sending

2. **Monitor Performance**
   - Check response times
   - Monitor CPU usage
   - Monitor memory usage
   - Check database performance

3. **Set Up Backups**

   ```bash
   # Database backup (daily)
   0 2 * * * mysqldump -u root -p$DBPASS $DBNAME > /backups/db-$(date +\%Y\%m\%d).sql

   # File backup (weekly)
   0 3 * * 0 tar czf /backups/rentflow-$(date +\%Y\%m\%d).tar.gz /var/www/rentflow
   ```

4. **Enable Monitoring**
   - Set up log aggregation
   - Configure alerts for errors
   - Monitor uptime
   - Track response times

## Troubleshooting

### 404 Errors After Deployment

- Verify `.htaccess` files exist
- Verify mod_rewrite is enabled
- Check Apache error logs: `tail -f /var/log/apache2/error.log`
- Verify base path in code matches deployment path

### Database Connection Issues

- Verify database credentials in `.env`
- Check database user has correct permissions
- Verify database server is running and accessible
- Check firewall rules

### Email Not Sending

- Verify SMTP settings in `.env`
- Check email logs: `tail -f logs/email.log`
- Verify sender email is authenticated
- Check spam folder

### Permission Denied Errors

- Verify file ownership: `chown -R www-data:www-data /var/www/rentflow`
- Verify directory permissions: `chmod 755 -R`
- Verify file permissions: `chmod 644 -R`

### High Memory Usage

- Check for infinite loops or large datasets
- Monitor slow queries
- Increase PHP memory limit if needed
- Optimize database queries

## Security Hardening

1. **Disable Directory Listing**
   - Already configured in `.htaccess`

2. **Remove Server Signatures**
   - Done via security headers in `.htaccess`

3. **Enable Security Headers**
   - X-Content-Type-Options
   - X-XSS-Protection
   - X-Frame-Options
   - Content-Security-Policy

4. **Rate Limiting**
   - Configured middleware active
   - Monitor for abuse

5. **HTTPS Enforcement**
   - Redirect HTTP to HTTPS
   - Use HSTS header
   - Configure SSL/TLS properly

6. **Input Validation**
   - Always validate user input
   - Use prepared statements for database queries
   - Sanitize output

## Rolling Back Deployment

```bash
# Keep previous version
mv /var/www/rentflow /var/www/rentflow-v2
mv /var/www/rentflow-v1 /var/www/rentflow
# Restart services
sudo systemctl restart apache2
```

## Performance Optimization

1. **Enable Caching**
   - Configure browser caching (done in `.htaccess`)
   - Consider Redis for session caching

2. **Minify Assets**
   - Minify CSS and JavaScript
   - Combine files where possible

3. **Database Optimization**
   - Add indexes
   - Monitor slow queries
   - Archive old data

4. **Server Configuration**
   - Enable gzip compression (done)
   - Configure keepalive connections
   - Tune PHP parameters

## Monitoring & Maintenance

### Daily Tasks

- [ ] Check error logs
- [ ] Monitor uptime
- [ ] Check disk space
- [ ] Monitor database performance

### Weekly Tasks

- [ ] Review security logs
- [ ] Check backup integrity
- [ ] Review performance metrics
- [ ] Check for updates

### Monthly Tasks

- [ ] Update dependencies
- [ ] Review access logs
- [ ] Optimize database
- [ ] Test disaster recovery

## Support & Escalation

For deployment issues:

1. Check error logs
2. Review configuration
3. Verify permissions
4. Check recent changes
5. Contact hosting provider if needed
