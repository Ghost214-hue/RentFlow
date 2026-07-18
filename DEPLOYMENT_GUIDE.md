# RentalFlow Deployment Guide

## Ensuring Smooth Operation: Localhost vs Production

This guide explains the configuration changes made to ensure RentalFlow works seamlessly in both localhost and production environments.

## Problem Identified

The application was returning **404 errors** on localhost (`http://localhost/RentalFlow/signin`) while working fine in production. This was caused by:

1. **Hardcoded RewriteBase**: The `.htaccess` file had `RewriteBase /` which is correct for production (root domain) but fails for subdirectory deployments like `http://localhost/RentalFlow/`

2. **Base path issues**: HTML `<base>` tags and API URL routing were not accounting for subdirectory deployments.

## Solution Applied

### 1. Dynamic RewriteBase Configuration

**File Modified**: `.htaccess`

The `RewriteBase` directive now uses `/RentalFlow` for localhost deployments:

```apache
# RewriteBase - use /RentalFlow for localhost, / for production
RewriteBase /RentalFlow
```

**For Production**: Change this line to `RewriteBase /` when deploying to a root domain.

### 2. Frontend Routing (Already Implemented)

The application already has a `frontend/public/js/base-path.js` file that:

- Detects the current URL path dynamically
- Rewrites `/api/*` calls to include the correct base path
- Works automatically in both localhost and production

### 3. PHP Routing (Already Implemented)

The `frontend/public/index.php` file:

- Extracts the page name from the URL
- Routes to appropriate PHP files
- Handles authentication and redirects

## Deployment Checklist

### For Localhost (XAMPP/WAMP)

1. Place the project in `/opt/lampp/htdocs/RentalFlow/` (or equivalent)
2. Ensure Apache mod_rewrite is enabled
3. The application will be accessible at: `http://localhost/RentalFlow/`

**Current Configuration**:

- ✅ `.htaccess` has `RewriteBase /RentalFlow`
- ✅ Rewrite rules are configured for subdirectory
- ✅ API routes work correctly

### For Production (Root Domain)

1. Deploy to the web server root or configured DocumentRoot
2. **IMPORTANT**: Update `.htaccess` line 7:

   ```apache
   # Change from:
   RewriteBase /RentalFlow

   # To:
   RewriteBase /
   ```

3. If deploying to a subdomain (e.g., `app.example.com`), keep `RewriteBase /`

### For Production (Subdirectory)

If deploying to a subdirectory like `https://example.com/rentflow/`:

1. Update `.htaccess` line 7:

   ```apache
   RewriteBase /rentflow
   ```

2. Update the `<base>` tag in the same file (line 21):
   ```apache
   Substitute "s|<head>|<head><base href=\"/rentflow/\">|"
   ```

## Key Files to Configure

| File                              | Purpose                        | Action Required                |
| --------------------------------- | ------------------------------ | ------------------------------ |
| `.htaccess`                       | Apache URL rewriting           | ✅ Configured for localhost    |
| `.env`                            | Database and app configuration | Update for production database |
| `.env.production`                 | Production-specific settings   | Use in production              |
| `frontend/public/js/base-path.js` | Dynamic base path for frontend | ✅ Already working             |

## Testing URLs

After deployment, verify these URLs work:

```bash
# Home page (should redirect to signin)
http://your-domain/signin

# Signin page
http://your-domain/signin

# Signup page
http://your-domain/signup

# API health check
http://your-domain/api/health-check

# Authenticated pages (after login)
http://your-domain/dashboard
http://your-domain/properties
```

## Apache Configuration Requirements

Ensure your Apache configuration includes:

```apache
<Directory "/path/to/RentalFlow">
    AllowOverride All
    Require all granted
</Directory>

# Enable required modules
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule substitute_module modules/mod_substitute.so
LoadModule headers_module modules/mod_headers.so
```

## Environment Configuration

### Localhost (.env)

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=rentalflow
APP_URL=http://localhost/RentalFlow
```

### Production (.env or .env.production)

```env
DB_HOST=your-db-host
DB_USER=your-db-user
DB_PASS=your-db-pass
DB_NAME=rentalflow
APP_URL=https://yourdomain.com
```

## Troubleshooting

### Issue: 404 Error on localhost

**Solution**: Ensure `RewriteBase /RentalFlow` is set in `.htaccess`

### Issue: CSS/JS not loading

**Solution**: Check that the `<base>` tag is correctly set in the HTML output

### Issue: API calls failing

**Solution**: The `base-path.js` file should handle this automatically. Check browser console for errors.

### Issue: mod_rewrite not working

**Solution**:

1. Verify module is loaded: `apachectl -M | grep rewrite`
2. Check `AllowOverride All` is set in Apache config
3. Restart Apache after changes

## Summary

The application is now configured to work out-of-the-box on localhost at `http://localhost/RentalFlow/`. For production deployment, simply update the `RewriteBase` value in `.htaccess` to match your deployment path.

**For root domain**: `RewriteBase /`
**For /RentalFlow subdirectory**: `RewriteBase /RentalFlow`
**For other subdirectories**: `RewriteBase /your-subdirectory`
