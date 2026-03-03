#!/bin/bash

# Laravel Deployment Script for Hostinger
# Domain: https://ojtlog.dictr2.online/

set -e  # Exit on error

echo "========================================"
echo "Laravel Deployment Script"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/path/to/domain/public"  # Update this to your actual path
APP_ENV="production"

echo -e "${YELLOW}Checking prerequisites...${NC}"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo -e "${RED}Error: PHP is not installed${NC}"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;' | cut -d'.' -f1,2)
REQUIRED_PHP="8.1"
if [ "$(printf '%s\n' "$REQUIRED_PHP" "$PHP_VERSION" | sort -V | head -n1)" != "$REQUIRED_PHP" ]; then
    echo -e "${RED}Error: PHP version must be 8.1 or higher. Current: $PHP_VERSION${NC}"
    exit 1
fi

echo -e "${GREEN}✓ PHP version: $PHP_VERSION${NC}"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Error: Composer is not installed${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Composer is installed${NC}"

echo ""
echo -e "${YELLOW}Starting deployment...${NC}"

# Navigate to project directory
if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}Error: Project directory not found: $PROJECT_DIR${NC}"
    echo "Please update PROJECT_DIR in this script"
    exit 1
fi

cd "$PROJECT_DIR"

# Backup current version
echo ""
echo -e "${YELLOW}Creating backup...${NC}"
BACKUP_DIR="$HOME/backups/ojtlog_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp -r . "$BACKUP_DIR" 2>/dev/null || true
echo -e "${GREEN}✓ Backup created at: $BACKUP_DIR${NC}"

# Install dependencies
echo ""
echo -e "${YELLOW}Installing dependencies...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction
echo -e "${GREEN}✓ Dependencies installed${NC}"

# Generate application key
if [ ! -f ".env" ]; then
    echo ""
    echo -e "${YELLOW}Creating .env file...${NC}"
    if [ -f ".env.production" ]; then
        cp .env.production .env
    else
        cp .env.example .env
    fi
    php artisan key:generate
    echo -e "${GREEN}✓ .env file created${NC}"
else
    echo -e "${YELLOW}⚠ .env file already exists${NC}"
fi

# Run migrations
echo ""
echo -e "${YELLOW}Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Migrations completed${NC}"

# Run seeders (optional - comment out if not needed)
echo ""
echo -e "${YELLOW}Running seeders...${NC}"
php artisan db:seed --force
echo -e "${GREEN}✓ Seeders completed${NC}"

# Create storage link
echo ""
echo -e "${YELLOW}Creating storage link...${NC}"
php artisan storage:link --force
echo -e "${GREEN}✓ Storage link created${NC}"

# Clear and cache
echo ""
echo -e "${YELLOW}Clearing and caching...${NC}"
php artisan config:clear
php artisan config:cache

php artisan route:clear
php artisan route:cache

php artisan view:clear
php artisan view:cache

php artisan cache:clear

echo -e "${GREEN}✓ Cache cleared and rebuilt${NC}"

# Set permissions
echo ""
echo -e "${YELLOW}Setting permissions...${NC}"
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R apache:apache storage bootstrap/cache 2>/dev/null || true
echo -e "${GREEN}✓ Permissions set${NC}"

# Optimization
echo ""
echo -e "${YELLOW}Optimizing application...${NC}"
php artisan optimize
echo -e "${GREEN}✓ Application optimized${NC}"

echo ""
echo "========================================"
echo -e "${GREEN}Deployment completed successfully!${NC}"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Update your .env file with database credentials"
echo "2. Update default passwords:"
echo "   php artisan tinker --execute='\\\$admin = \\\App\\\Models\\\User::where(\"email\", \"admin@timelog.com\")->first(); \\\$admin->password_hash = \\\\Illuminate\\\Support\\\Facades\\\Hash::make(\"NEW_PASSWORD\"); \\\$admin->save();'"
echo "3. Visit https://ojtlog.dictr2.online/"
echo ""
echo "Backup location: $BACKUP_DIR"
echo ""
