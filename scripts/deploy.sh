#!/bin/bash

# GeoSPT Manager Deployment Script
# Usage: ./scripts/deploy.sh [environment]
# Environments: development, staging, production

set -e

ENVIRONMENT=${1:-development}
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

echo "================================================"
echo "GeoSPT Manager Deployment"
echo "Environment: $ENVIRONMENT"
echo "================================================"
echo ""

# Load environment-specific variables
if [ -f "$PROJECT_DIR/.env.$ENVIRONMENT" ]; then
    echo "✓ Loading environment file: .env.$ENVIRONMENT"
    export $(cat "$PROJECT_DIR/.env.$ENVIRONMENT" | grep -v '^#' | xargs)
else
    echo "⚠ Warning: .env.$ENVIRONMENT not found, using .env"
    if [ -f "$PROJECT_DIR/.env" ]; then
        export $(cat "$PROJECT_DIR/.env" | grep -v '^#' | xargs)
    else
        echo "✗ Error: No environment file found"
        exit 1
    fi
fi

echo ""

# Step 1: Git pull
echo "Step 1: Pulling latest code..."
cd "$PROJECT_DIR"
git pull origin main
echo "✓ Code updated"
echo ""

# Step 2: Install dependencies
echo "Step 2: Installing dependencies..."
if [ "$ENVIRONMENT" = "production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    composer install --optimize-autoloader --no-interaction
fi
echo "✓ Dependencies installed"
echo ""

# Step 3: Run migrations
echo "Step 3: Running database migrations..."
php spark migrate
echo "✓ Migrations completed"
echo ""

# Step 4: Clear cache
echo "Step 4: Clearing application cache..."
rm -rf writable/cache/*
php spark cache:clear
echo "✓ Cache cleared"
echo ""

# Step 5: Set permissions
echo "Step 5: Setting file permissions..."
chmod -R 755 "$PROJECT_DIR"
chmod -R 777 "$PROJECT_DIR/writable"
echo "✓ Permissions set"
echo ""

# Step 6: Restart services
if [ "$ENVIRONMENT" = "production" ]; then
    echo "Step 6: Restarting services..."
    
    # Using Docker Compose
    if command -v docker-compose &> /dev/null; then
        echo "Restarting Docker containers..."
        docker-compose restart app worker
        echo "✓ Docker containers restarted"
    fi
    
    # Using Supervisor
    if command -v supervisorctl &> /dev/null; then
        echo "Restarting Supervisor services..."
        sudo supervisorctl restart geospt:*
        echo "✓ Supervisor services restarted"
    fi
    
    echo ""
fi

# Step 7: Health check
echo "Step 7: Running health checks..."
sleep 5

if command -v curl &> /dev/null; then
    APP_URL=${app_baseURL:-http://localhost:8080}
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL/api/health" || echo "000")
    
    if [ "$HTTP_CODE" = "200" ]; then
        echo "✓ Application is responding"
    else
        echo "⚠ Warning: Application health check returned HTTP $HTTP_CODE"
    fi
else
    echo "⚠ curl not available, skipping health check"
fi
echo ""

# Step 8: Summary
echo "================================================"
echo "Deployment completed successfully!"
echo "================================================"
echo ""
echo "Environment: $ENVIRONMENT"
echo "Timestamp: $(date)"
echo ""

# Create deployment log
LOG_FILE="$PROJECT_DIR/writable/logs/deployment.log"
echo "$(date) - Deployment to $ENVIRONMENT completed" >> "$LOG_FILE"

exit 0
