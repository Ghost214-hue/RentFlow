# RentFlow - Production Requirements

## Server Requirements

### Operating System

- Ubuntu 18.04 LTS (or later)
- CentOS 7 (or later)
- Debian 9 (or later)

### Web Server

- Apache 2.4+ with mod_rewrite and mod_headers enabled
- **OR** Nginx 1.16+

### PHP

- PHP 8.0 or higher
- Required Extensions:
  - PDO (with MySQL support)
  - OpenSSL
  - JSON
  - Mbstring
  - Curl
  - Fileinfo
  - DOM
  - SimpleXML

### Database

- MySQL 5.7+ or MariaDB 10.3+
- Minimum: 2GB dedicated storage
- Recommended: 10GB+ for production

### Mail Server

- SMTP Server (Gmail, SendGrid, Mailgun, etc.)
- OR Local Postfix/Exim for local delivery

## Installation

### 1. Update System

```bash
sudo apt-get update
sudo apt-get upgrade -y
```

### 2. Install Apache & PHP

```bash
sudo apt-get install apache2 php8.4 php8.4-mysql php8.4-curl php8.4-json php8.4-mbstring php8.4-xml -y
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

### 3. Install MySQL

```bash
sudo apt-get install mysql-server -y
# Run security script
sudo mysql_secure_installation
```

### 4. Install Composer

```bash
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

### 5. Clone/Upload RentFlow

```bash
cd /var/www
sudo git clone <your-repo> rentflow
# OR upload via scp/FTP
sudo chown -R www-data:www-data /var/www/rentflow
```

### 6. Install Dependencies

```bash
cd /var/www/rentflow/backend
composer install --no-dev --optimize-autoloader
```

### 7. Configure Environment

```bash
cd /var/www/rentflow
cp .env.production .env
nano .env  # Edit with production values
```

### 8. Run Migrations

```bash
php backend/database/migrate.php
```

### 9. Configure Apache VirtualHost

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/rentflow/frontend/public

    <Directory /var/www/rentflow/frontend/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/rentflow/backend/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/rentflow_error.log
    CustomLog ${APACHE_LOG_DIR}/rentflow_access.log combined
</VirtualHost>
```

### 10. Enable SSL/TLS (Required for Production)

```bash
# Using Let's Encrypt with Certbot
sudo apt-get install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
sudo systemctl restart apache2
```

## Performance Tuning

### PHP Configuration

Edit `/etc/php/8.4/apache2/php.ini`:

```ini
max_execution_time = 30
memory_limit = 256M
post_max_size = 10M
upload_max_filesize = 10M
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

### MySQL Optimization

```sql
-- Run for production database
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
SET GLOBAL log_queries_not_using_indexes = 'ON';
```

### Apache Configuration

Edit `/etc/apache2/mods-available/mpm_prefork.conf`:

```apache
<IfModule mpm_prefork_module>
    StartServers 8
    MinSpareServers 5
    MaxSpareServers 20
    MaxRequestWorkers 256
    MaxConnectionsPerChild 0
</IfModule>
```

## Backup Strategy

### Automated Database Backups

```bash
# Create backup script
sudo nano /usr/local/bin/backup-rentflow-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/rentflow"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
mkdir -p $BACKUP_DIR

mysqldump -u rentflow_user -p$RENTFLOW_PASS rentflow_prod > \
    $BACKUP_DIR/rentflow_db_$TIMESTAMP.sql

gzip $BACKUP_DIR/rentflow_db_$TIMESTAMP.sql
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete
```

### Add to Crontab

```bash
sudo crontab -e
# Add this line (runs daily at 2 AM)
0 2 * * * /usr/local/bin/backup-rentflow-db.sh
```

### File Backups

```bash
# Weekly backup
0 3 * * 0 tar czf /var/backups/rentflow/files_$(date +\%Y\%m\%d).tar.gz /var/www/rentflow/
```

## Monitoring & Alerts

### Install Monitoring Tools

```bash
sudo apt-get install htop iotop nethogs -y
```

### Check Disk Space

```bash
df -h
du -sh /var/www/rentflow
du -sh /var/log
```

### Monitor Logs

```bash
tail -f /var/log/apache2/rentflow_error.log
tail -f /var/log/apache2/rentflow_access.log
tail -f /var/log/php_errors.log
tail -f /var/www/rentflow/logs/error.log
```

## Security Hardening

### Firewall Setup

```bash
sudo apt-get install ufw -y
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp     # SSH
sudo ufw allow 80/tcp     # HTTP
sudo ufw allow 443/tcp    # HTTPS
sudo ufw enable
```

### Fail2Ban Installation

```bash
sudo apt-get install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### SSH Key Authentication

```bash
# Disable password authentication
sudo nano /etc/ssh/sshd_config
# Set: PasswordAuthentication no
# Set: PubkeyAuthentication yes
sudo systemctl restart ssh
```

## Troubleshooting

### 500 Internal Server Error

1. Check Apache error logs: `tail -f /var/log/apache2/error.log`
2. Check PHP error logs: `tail -f /var/log/php_errors.log`
3. Verify file permissions
4. Check database connection

### Database Connection Issues

```bash
mysql -u rentflow_user -p
# Try connecting manually to verify credentials
```

### File Upload Issues

```bash
# Check upload directory permissions
ls -la /var/www/rentflow/backend/public/uploads/
chmod 755 /var/www/rentflow/backend/public/uploads/
```

## Support

For deployment issues, check:

1. DEPLOYMENT.md in project root
2. Error logs in logs/ directory
3. Server configuration files
4. Database configuration

Contact: support@yourdomain.com
