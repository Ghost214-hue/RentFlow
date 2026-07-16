# Email Reminders System - RentaFlow

## Overview

The email reminder system automatically sends rent reminders to tenants when bills are overdue or approaching their due date.

## Files

- **`rent_reminders.php`** - Main cron script that sends reminders
- **`email_queue.php`** - Processes queued emails in batches
- **`test_email.php`** - Manual testing script for email functionality
- **`EmailService.php`** - Core email sending service
- **`NotificationRecipientService.php`** - Manages recipients (tenant + next of kin)

## Current Behavior

The `rent_reminders.php` cron currently:

1. **Runs daily** (when triggered by cron/manually)
2. **Finds overdue bills**: Bills where `due_date <= TODAY` and `status != 'paid'`
3. **Sends to**: Tenant + Next of Kin (if registered)
4. **Logs**: Each reminder in `rent_reminders` table and `logs/rent_reminders.log`

### Query Logic:

```sql
SELECT b.*, t.email, t.name, h.unit, p.payment_method_type
FROM bills b
JOIN houses h ON h.id = b.house_id
JOIN tenants t ON t.id = h.tenant_id
WHERE b.status != 'paid'
  AND b.due_date <= CURDATE()           -- PAST due date only
  AND h.status = 'occupied'
  AND t.email IS NOT NULL
```

**Limitation**: Only sends AFTER due date (overdue), not advance reminders.

---

## Testing the System

### Option 1: Quick Email Test (Recommended First)

Tests SMTP configuration without needing overdue bills:

```bash
cd /opt/lampp/htdocs/RentFlow
php backend/cron/test_email.php
```

**What it does:**

1. Checks database connection
2. Verifies email templates exist
3. Validates SMTP settings
4. **Sends a test email** (you provide the recipient)
5. Checks for overdue bills

**Example output:**

```
=== RentaFlow Email Test ===

[1] Testing Database Connection... ✓ OK
[2] Testing Email Service... ✓ OK
[3] Checking Email Templates... ✓ Found 'Rent Reminder'
[4] Checking SMTP Configuration... ✓ Configured
[5] Sending Test Email...
    Enter recipient email: test@example.com
    ✓ SENT SUCCESSFULLY
    Check inbox: test@example.com
```

### Option 2: Test Actual Rent Reminders

Creates an overdue bill scenario:

```bash
# 1. Create a test overdue bill (MySQL)
mysql -u root rentaflow -e "
  INSERT INTO bills (owner_id, house_id, month, rent, water, electricity, total, status, due_date, created_at)
  SELECT 1, id, '2024-01', 45000, 0, 0, 45000, 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()
  FROM houses LIMIT 1;
"

# 2. Run the cron
php backend/cron/rent_reminders.php

# 3. Check output
# Should show: ✓ Sent to tenant@email.com - 2024-01 - KES 45000.00

# 4. Verify in database
mysql -u root rentaflow -e "SELECT * FROM rent_reminders ORDER BY sent_at DESC LIMIT 5;"
```

---

## Setting Up Automated Reminders

### Step 1: Verify Email Works

```bash
php backend/cron/test_email.php
# Enter your email when prompted
# Check inbox for test email
```

### Step 2: Run Manual Test

```bash
# Check for existing overdue bills
php backend/cron/rent_reminders.php

# View logs
tail -f backend/logs/rent_reminders.log
tail -f backend/logs/error.log
```

### Step 3: Set Up Cron Job

Edit crontab:

```bash
crontab -e
```

Add line (runs daily at 8:00 AM):

```bash
0 8 * * * cd /opt/lampp/htdocs/RentFlow && php backend/cron/rent_reminders.php > /dev/null 2>&1
```

**Or with logging:**

```bash
0 8 * * * cd /opt/lampp/htdocs/RentFlow && php backend/cron/rent_reminders.php >> backend/logs/cron.log 2>&1
```

### Step 4: Verify Cron is Working

```bash
# Check cron is running
ps aux | grep rent_reminders

# Check logs after next scheduled run
cat backend/logs/rent_reminders.log
```

---

## Understanding Reminder Timing

### Current System: **Overdue Only**

| Bill Status | Due Date        | Email Sent?      |
| ----------- | --------------- | ---------------- |
| Pending     | Today           | ❌ No            |
| Pending     | Tomorrow        | ❌ No            |
| Pending     | 7 days from now | ❌ No            |
| Pending     | Yesterday       | ✅ Yes (overdue) |
| Partial     | Yesterday       | ✅ Yes (overdue) |

### To Add 7-Day Advance Reminders

I can modify `rent_reminders.php` to also send reminders 7 days before due date. This would require:

1. **Two separate queries**:
   - One for overdue (existing)
   - One for due in 7 days (new)

2. **Different email subjects**:
   - Overdue: "Rent Reminder - OVERDUE"
   - Advance: "Rent Reminder - Due in 7 Days"

3. **Updated logging** to track reminder type

**Would you like me to add 7-day advance reminders?**

---

## Monitoring & Troubleshooting

### Check if emails are being sent:

```bash
# 1. View recent reminders
tail -20 backend/logs/rent_reminders.log

# 2. Check database for sent records
mysql -u root rentaflow -e "SELECT tenant_id, month, sent_at, status FROM rent_reminders ORDER BY sent_at DESC LIMIT 10;"

# 3. Check for errors
grep -i "error\|failed" backend/logs/error.log | tail -20
```

### Common Issues:

1. **Emails not sending:**
   - Check SMTP credentials in `.env`
   - For Gmail: Use App Password (not regular password)
   - Check firewall allows outbound port 587

2. **No overdue bills found:**
   - Bills are only overdue if `due_date <= TODAY` AND `status != 'paid'`
   - Manually set a bill to overdue for testing

3. **Template not found:**
   - Template must be in `email_templates` table
   - Run seeder: `php backend/database/seeders/seed.php`

### Email Queue System

If `MAIL_QUEUE_ENABLED=true` in `.env`:

- Emails are queued in `email_queue` table
- Processed by `email_queue.php` cron
- Run queue processor: `php backend/cron/email_queue.php`

---

## Testing Checklist

- [ ] Run `php backend/cron/test_email.php` and receive test email
- [ ] Create test overdue bill and run `php backend/cron/rent_reminders.php`
- [ ] Verify email received by tenant
- [ ] Check `rent_reminders` table has new record
- [ ] Check `logs/rent_reminders.log` shows success
- [ ] Set up daily cron job
- [ ] Monitor first automated run

---

## Quick Commands Reference

```bash
# Test email configuration
php backend/cron/test_email.php

# Send all overdue reminders now
php backend/cron/rent_reminders.php

# Process email queue (if queue enabled)
php backend/cron/email_queue.php

# Check queue status
php backend/cron/email_queue.php --check

# View logs
tail -f backend/logs/rent_reminders.log
tail -f backend/logs/error.log

# Check cron job is scheduled
crontab -l

# Manually trigger cron (for testing)
php backend/cron/rent_reminders.php
```

---

## Support

If emails aren't sending:

1. Check `backend/logs/error.log` for SMTP errors
2. Verify `.env` has correct SMTP credentials
3. Test with `test_email.php` script
4. Ensure port 587 (TLS) or 465 (SSL) is not blocked
