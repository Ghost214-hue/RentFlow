# RentFlow - Production Setup Guide

## Quick Start

1. **Read the Documentation:**
   - [DEPLOYMENT.md](DEPLOYMENT.md) - Complete deployment checklist and guide
   - [PRODUCTION_REQUIREMENTS.md](PRODUCTION_REQUIREMENTS.md) - Server requirements and setup

2. **Environment Setup:**

   ```bash
   cp .env.production .env
   # Edit .env with production values
   nano .env
   ```

3. **Run Deployment Script:**
   ```bash
   chmod +x deploy-production.sh
   ./deploy-production.sh
   ```

## Files Created for Production

### Configuration Files

- `.env.production` - Production environment template
- `backend/config/production.php` - Production-specific error handling
- `backend/config/bootstrap.php` - Application bootstrap configuration

### Server Configuration

- `frontend/public/.htaccess` - Apache routing for frontend
- `backend/public/.htaccess` - Apache routing for backend API
- `nginx.conf` - Nginx server configuration (alternative to Apache)

### Documentation

- `DEPLOYMENT.md` - Step-by-step deployment guide
- `PRODUCTION_REQUIREMENTS.md` - Server requirements and setup instructions
- `deploy-production.sh` - Automated deployment script

## Key Features Implemented

### ✅ Security

- [x] Security headers (.htaccess)
- [x] HTTPS/SSL enforcement
- [x] Error suppression in production
- [x] CORS configuration
- [x] Directory listing disabled
- [x] Sensitive file protection

### ✅ Error Handling

- [x] Production error handler
- [x] Exception handling
- [x] Error logging
- [x] No error display in production

### ✅ Performance

- [x] Gzip compression
- [x] Browser caching
- [x] Asset minification support
- [x] Database query optimization guide

### ✅ Logging & Monitoring

- [x] Centralized logging
- [x] Error log configuration
- [x] Log rotation setup
- [x] Performance monitoring setup

### ✅ Backup & Recovery

- [x] Automated backup script
- [x] Database backup strategy
- [x] File backup strategy
- [x] Disaster recovery guide

## Deployment Workflow

### 1. Pre-Deployment

```bash
# Test locally first
php -S localhost:8080 router.php

# Run tests
php backend/tests/run_tests.php
```

### 2. Deploy to Production

```bash
# Connect to server
ssh user@server

# Navigate to app directory
cd /var/www/rentflow

# Copy production files
scp .env.production server:/var/www/rentflow/.env.production
scp deploy-production.sh server:/var/www/rentflow/

# Run deployment
./deploy-production.sh
```

### 3. Post-Deployment

```bash
# Verify installation
curl https://yourdomain.com/
curl https://yourdomain.com/api/health

# Check logs
tail -f logs/error.log
tail -f logs/access.log
```

## Production Checklist

Before going live:

- [ ] Update .env with production credentials
- [ ] Enable HTTPS/SSL certificate
- [ ] Run database migrations
- [ ] Test all user workflows
- [ ] Test API endpoints
- [ ] Verify email sending
- [ ] Test file uploads
- [ ] Configure backup strategy
- [ ] Set up monitoring
- [ ] Configure rate limiting
- [ ] Test error handling
- [ ] Review security headers
- [ ] Set up log rotation
- [ ] Configure firewall
- [ ] Test database failover

## Environment Variables

**Required in Production:**

```
DB_HOST=your-db-host
DB_USER=db-user
DB_PASS=secure-password
JWT_SECRET=secure-jwt-key
MAIL_HOST=smtp-server
MAIL_USERNAME=email@example.com
MAIL_PASSWORD=app-password
APP_URL=https://yourdomain.com
```

## Monitoring Commands

```bash
# Check disk space
df -h

# Check memory usage
free -h

# Check running processes
ps aux | grep php

# Check Apache status
sudo systemctl status apache2

# View error logs
tail -f /var/log/apache2/error.log

# View app logs
tail -f logs/error.log

# Monitor real-time
watch -n 1 'df -h && free -h'
```

## Troubleshooting

### Files Not Serving

- Check `.htaccess` exists
- Verify mod_rewrite enabled: `apache2ctl -M | grep rewrite`
- Check file permissions

### Database Connection Error

- Verify credentials in `.env`
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `mysql -u user -p -h host`

### Emails Not Sending

- Check SMTP settings
- Review `logs/email.log`
- Test SMTP connection

### High Memory Usage

- Check slow queries
- Monitor process list: `top`
- Increase PHP memory limit if needed

## Additional Resources

- [PHP Best Practices](https://www.php.net/manual/en/security.php)
- [Apache Security](https://httpd.apache.org/docs/2.4/misc/security_tips.html)
- [MySQL Security](https://dev.mysql.com/doc/mysql-security-excerpt/8.0/en/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

## Support

For issues or questions:

1. Check [DEPLOYMENT.md](DEPLOYMENT.md)
2. Review error logs in `logs/`
3. Verify environment configuration
4. Contact deployment team

---

**Last Updated:** 2026-07-13
**Version:** 1.0
