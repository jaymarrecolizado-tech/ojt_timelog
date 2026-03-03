#!/bin/bash

# Deployment Package Creator for Linux/Mac
# Creates a deployment package for Hostinger

PACKAGE_NAME="ojt_timelog_deploy_$(date +%Y%m%d_%H%M%S).tar.gz"
DEPLOY_DIR="deploy_temp"

echo "========================================"
echo "Laravel Deployment Package Creator"
echo "========================================"
echo ""
echo "Creating deployment package..."
echo "Package: $PACKAGE_NAME"
echo ""

# Create temporary deployment directory
if [ -d "$DEPLOY_DIR" ]; then
    echo "Removing old deployment directory..."
    rm -rf "$DEPLOY_DIR"
fi

mkdir -p "$DEPLOY_DIR"

# Copy essential directories and files
echo "Copying files..."

# Copy app
rsync -av --progress \
    app/ \
    "$DEPLOY_DIR/app/" \
    --exclude='*.log' \
    --exclude='.DS_Store'

# Copy bootstrap
rsync -av --progress bootstrap/ "$DEPLOY_DIR/bootstrap/"

# Copy config
rsync -av --progress config/ "$DEPLOY_DIR/config/"

# Copy database
rsync -av --progress database/ "$DEPLOY_DIR/database/" \
    --exclude='*.sqlite' \
    --exclude='*.db'

# Copy public (excluding unnecessary files)
rsync -av --progress public/ "$DEPLOY_DIR/public/" \
    --exclude='hot' \
    --exclude='.gitignore' \
    --exclude='.htaccess' \
    --exclude='mix-manifest.json'

# Copy resources
rsync -av --progress resources/ "$DEPLOY_DIR/resources/" \
    --exclude='.DS_Store'

# Copy routes
rsync -av --progress routes/ "$DEPLOY_DIR/routes/"

# Copy storage (with structure but empty folders)
mkdir -p "$DEPLOY_DIR/storage/app"
mkdir -p "$DEPLOY_DIR/storage/framework/cache"
mkdir -p "$DEPLOY_DIR/storage/framework/sessions"
mkdir -p "$DEPLOY_DIR/storage/framework/testing"
mkdir -p "$DEPLOY_DIR/storage/framework/views"
mkdir -p "$DEPLOY_DIR/storage/logs"

# Copy essential files
cp artisan "$DEPLOY_DIR/"
cp composer.json "$DEPLOY_DIR/"
cp .gitattributes "$DEPLOY_DIR/"

# Copy deployment-specific files
cp deployment/.env.production "$DEPLOY_DIR/.env.production"
cp deployment/.htaccess "$DEPLOY_DIR/public/.htaccess"
cp deployment/DEPLOYMENT_GUIDE.md "$DEPLOY_DIR/"
cp deployment/README.md "$DEPLOY_DIR/"

# Create tar.gz package
echo ""
echo "Creating tar.gz package..."
tar -czf "$PACKAGE_NAME" -C "$DEPLOY_DIR" .

# Cleanup
echo "Cleaning up..."
rm -rf "$DEPLOY_DIR"

echo ""
echo "========================================"
echo "Deployment package created successfully!"
echo "========================================"
echo ""
echo "Package file: $PACKAGE_NAME"
echo ""
echo "Upload this file to your Hostinger server,"
echo "extract it, and follow the deployment guide."
echo ""
echo "Upload command example:"
echo "  scp $PACKAGE_NAME user@server:/path/to/domain/"
echo ""
echo "Extract command example:"
echo "  tar -xzf $PACKAGE_NAME"
echo ""
echo "Next steps:"
echo "1. Upload $PACKAGE_NAME to server"
echo "2. Extract to your domain's public folder"
echo "3. Follow DEPLOYMENT_GUIDE.md instructions"
echo ""
