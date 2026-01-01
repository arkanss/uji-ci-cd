#!/bin/bash
set -e

# Script to set up local secrets for testing Docker Swarm secrets functionality

SECRETS_DIR=".docker/secrets"

echo "Setting up local secrets for testing..."

# Create secrets directory
mkdir -p "$SECRETS_DIR"

# Generate APP_KEY if not exists
if [ ! -f "$SECRETS_DIR/staging_app_key" ]; then
    echo "Generating APP_KEY..."
    # Generate a random base64 encoded key (32 bytes = 256 bits for AES-256)
    APP_KEY="base64:$(openssl rand -base64 32)"
    echo -n "$APP_KEY" > "$SECRETS_DIR/staging_app_key"
    echo "✓ Created staging_app_key"
else
    echo "✓ staging_app_key already exists"
fi

# Create DB_PASSWORD if not exists
if [ ! -f "$SECRETS_DIR/staging_db_password" ]; then
    echo "Creating DB_PASSWORD..."
    echo -n "test_password_123" > "$SECRETS_DIR/staging_db_password"
    echo "✓ Created staging_db_password"
else
    echo "✓ staging_db_password already exists"
fi

echo ""
echo "Secret files created in $SECRETS_DIR:"
ls -la "$SECRETS_DIR"

echo ""
echo "To test locally, run:"
echo "  cd .docker"
echo "  docker-compose -f docker-compose.local.yml up --build"
