#!/bin/bash

# RentFlow Production Deployment Script
# Run this after uploading code to production server

set -e

echo "🚀 Starting RentFlow Production Deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root (optional, remove if not needed)
# if [ "$EUID" -ne 0 ]; then 
#   echo "This script should be run as root"
#   exit 1
# fi

# Set proper permissions
echo -e "${YELLOW}Setting directory permissions...${NC}"
chmod 755 .
chmod 755 frontend/public
chmod 755 backend/public
chmod 755 logs
chmod 644 logs/* 2>/dev/null || true
chmod 755 backend/public/uploads 2>/dev/null || true

# Create required directories if they don't exist
echo -e "${YELLOW}Creating required directories...${NC}"
mkdir -p logs
mkdir -p backend/public/uploads
mkdir -p backend/vendor

# Set writable directories
chmod 775 logs
chmod 775 backend/public/uploads

# Copy environment file
echo -e "${YELLOW}Setting up environment file...${NC}"
if [ ! -f .env ]; then
    cp .env.production .env
    echo -e "${YELLOW}⚠️  Created .env from .env.production - PLEASE UPDATE DATABASE CREDENTIALS!${NC}"
else
    echo -e "${GREEN}✓ .env file exists${NC}"
fi

# Run composer install if needed
if [ -f backend/composer.json ]; then
    echo -e "${YELLOW}Installing PHP dependencies...${NC}"
    cd backend
    composer install --no-dev --optimize-autoloader
    cd ..
fi

# Run database migrations
echo -e "${YELLOW}Running database migrations...${NC}"
php backend/database/migrate.php || echo -e "${YELLOW}⚠️  Migration script not found, skipping${NC}"

# Clear any cache
echo -e "${YELLOW}Clearing cache and temporary files...${NC}"
find logs -type f -name "*.log" -mtime +7 -delete 2>/dev/null || true

# Verify critical files exist
echo -e "${YELLOW}Verifying critical files...${NC}"
critical_files=(
    "frontend/public/index.php"
    "backend/public/index.php"
    "frontend/public/.htaccess"
    "backend/public/.htaccess"
)

for file in "${critical_files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓ $file${NC}"
    else
        echo -e "${YELLOW}⚠️  Missing: $file${NC}"
    fi
done

# Check Apache mods
echo -e "${YELLOW}Checking Apache modules...${NC}"
if apache2ctl -M 2>/dev/null | grep -q "rewrite_module"; then
    echo -e "${GREEN}✓ mod_rewrite is enabled${NC}"
else
    echo -e "${YELLOW}⚠️  mod_rewrite may not be enabled - run: a2enmod rewrite${NC}"
fi

if apache2ctl -M 2>/dev/null | grep -q "headers_module"; then
    echo -e "${GREEN}✓ mod_headers is enabled${NC}"
else
    echo -e "${YELLOW}⚠️  mod_headers may not be enabled - run: a2enmod headers${NC}"
fi

echo ""
echo -e "${GREEN}✅ Deployment preparation complete!${NC}"
echo ""
echo "📋 Post-deployment checklist:"
echo "   [ ] Update .env with production credentials"
echo "   [ ] Verify database migrations ran successfully"
echo "   [ ] Test login functionality"
echo "   [ ] Test API endpoints"
echo "   [ ] Check error logs: tail -f logs/*.log"
echo "   [ ] Configure SSL/HTTPS"
echo "   [ ] Set up monitoring and logging"
echo "   [ ] Test file upload functionality"
echo "   [ ] Run full test suite"
