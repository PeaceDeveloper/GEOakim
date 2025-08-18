#!/bin/bash

# Test script for GEOakim Docker setup
echo "🧪 Testing GEOakim Docker Compose Configuration"
echo "================================================"

# Check if Docker is available
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed or not in PATH"
    exit 1
fi

echo "✅ Docker is available"

# Validate Docker Compose file
echo "🔍 Validating docker-compose.yml..."
if docker compose config > /dev/null 2>&1; then
    echo "✅ Docker Compose configuration is valid"
else
    echo "❌ Docker Compose configuration has errors"
    exit 1
fi

# Check if .env file exists
if [ -f ".env" ]; then
    echo "✅ Environment file (.env) exists"
else
    echo "❌ Environment file (.env) not found"
    exit 1
fi

# Validate PHP syntax
echo "🔍 Validating PHP files..."
for php_file in *.php; do
    if [ -f "$php_file" ]; then
        if php -l "$php_file" > /dev/null 2>&1; then
            echo "✅ $php_file syntax is valid"
        else
            echo "❌ $php_file has syntax errors"
            exit 1
        fi
    fi
done

# Check if all required files exist
required_files=("index.html" "result.php" "relatorio.php" "mongo-connection.php" "init-mongo.js" "ngrok.yml" "README.md")
for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file exists"
    else
        echo "❌ $file is missing"
        exit 1
    fi
done

echo ""
echo "🎉 All tests passed! The GEOakim setup is ready."
echo ""
echo "To start the application:"
echo "  docker compose up -d"
echo ""
echo "Access points:"
echo "  • Main Application: http://localhost:8080"
echo "  • Admin Report: http://localhost:8080/relatorio.php"
echo "  • MongoDB Express: http://localhost:8081"
echo "  • Dozzle (Logs): http://localhost:9999"
echo "  • Ngrok Dashboard: http://localhost:4040"