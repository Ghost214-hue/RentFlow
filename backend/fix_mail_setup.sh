#!/bin/bash
# RentFlow Email Setup Fix Script
# This script fixes common email delivery issues on XAMPP/Linux

echo "=== RentFlow Email Setup Fix ===\n"

# Fix 1: Create maildrop directory for XAMPP sendmail
echo "1. Creating maildrop directory for XAMPP sendmail..."
MAILDROP_DIR="/opt/lampp/var/log/maillog/maildrop"
if [ ! -d "$MAILDROP_DIR" ]; then
    mkdir -p "$MAILDROP_DIR"
    chmod 777 "$MAILDROP_DIR"
    echo "   ✓ Created: $MAILDROP_DIR"
else
    echo "   ✓ Already exists: $MAILDROP_DIR"
fi

# Fix 2: Configure PHP sendmail path
echo "\n2. Configuring PHP sendmail path..."
PHP_INI="/opt/lampp/etc/php.ini"
if [ -f "$PHP_INI" ]; then
    # Backup original
    cp "$PHP_INI" "$PHP_INI.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Update sendmail_path if not already set
    if grep -q ";sendmail_path = " "$PHP_INI"; then
        sed -i 's|;sendmail_path = |sendmail_path = "/usr/sbin/sendmail -t -i"|' "$PHP_INI"
        echo "   ✓ Updated sendmail_path in php.ini"
    else
        echo "   - sendmail_path already configured"
    fi
else
    echo "   ✗ PHP ini not found at: $PHP_INI"
fi

# Fix 3: Configure XAMPP sendmail (if exists)
echo "\n3. Configuring XAMPP sendmail..."
if [ -f "/opt/lampp/sendmail/sendmail.ini" ]; then
    echo "   Found XAMPP sendmail configuration"
    # You can add custom SMTP configuration here
else
    echo "   - XAMPP sendmail not found (optional)"
fi

# Fix 4: Install and configure Postfix (local mail server)
echo "\n4. Installing and configuring Postfix mail server..."
if ! command -v postfix &> /dev/null; then
    echo "   Installing Postfix..."
    sudo apt-get update -qq
    sudo apt-get install -y postfix mailutils -qq
    
    # Configure as local only
    sudo postconf -e "inet_interfaces = localhost"
    sudo postconf -e "inet_protocols = ipv4"
    sudo systemctl restart postfix
    
    echo "   ✓ Postfix installed and configured"
else
    echo "   ✓ Postfix already installed"
    sudo systemctl restart postfix 2>/dev/null || true
fi

# Fix 5: Test email sending
echo "\n5. Testing email configuration..."
php -r "echo 'PHP Version: ' . phpversion() . \"\\n\";"
php -r "echo 'sendmail_path: ' . ini_get('sendmail_path') . \"\\n\";"
php -r "echo 'SMTP: ' . ini_get('SMTP') . \"\\n\";"
php -r "echo 'smtp_port: ' . ini_get('smtp_port') . \"\\n\";"

echo "\n=== Setup Complete ==="
echo ""
echo "To send emails now, you have 3 options:"
echo ""
echo "Option A: Use local mail server (Postfix) - RECOMMENDED"
echo "  - Emails will be sent locally"
echo "  - View logs: tail -f /var/log/mail.log"
echo "  - Test: php backend/send_pending_emails.php"
echo ""
echo "Option B: Configure SMTP with real credentials"
echo "  - Edit .env file with your SMTP settings"
echo "  - Run: php backend/test_mail_config.php"
echo ""
echo "Option C: Use MailHog for development (email testing)"
echo "  - Run: mailhog"
echo "  - Set MAIL_HOST=localhost, MAIL_PORT=1025"
echo "  - View emails at: http://localhost:8025"
echo ""

# Restart Apache to apply PHP changes
echo "Restarting Apache to apply changes..."
sudo /opt/lampp/lampp restartapache 2>/dev/null || echo "Please restart Apache manually: sudo /opt/lampp/lampp restartapache"

echo -e "\n✓ Email setup fix complete!"