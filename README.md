# GEOakim - Geolocation Data Collection System

A modern web application for collecting and analyzing geolocation data with enhanced UI and professional monitoring capabilities.

## Features

- 🐳 **Docker & Docker Compose** - Easy deployment and development
- 🍃 **MongoDB Integration** - Professional database storage instead of file-based
- 🍞 **Toast Notifications** - Modern UI feedback instead of JavaScript alerts
- 📊 **Dozzle Monitoring** - Real-time log monitoring for all containers
- 🔒 **Environment Variables** - Secure configuration management
- 📈 **Enhanced Dashboard** - Improved reporting with statistics
- 🗺️ **Interactive Maps** - Google Maps integration with markers

## Quick Start

### 1. Clone and Setup

```bash
git clone <repository-url>
cd GEOakim
```

### 2. Environment Configuration

```bash
# Copy the sample environment file
cp .env.sample .env

# Edit the .env file with your actual values
nano .env
```

### 3. Start the Application

```bash
# Build and start all services
docker-compose up -d

# View logs
docker-compose logs -f
```

### 4. Access Services

- **Main Application**: http://localhost:8080
- **Reports Dashboard**: http://localhost:8080/relatorio.php
- **Dozzle (Log Monitoring)**: http://localhost:9999
- **MongoDB**: localhost:27017

## Environment Variables

Copy `.env.sample` to `.env` and configure:

```env
# MongoDB Configuration
MONGO_HOST=mongodb
MONGO_PORT=27017
MONGO_DATABASE=geoakim
MONGO_USERNAME=geoakim_user
MONGO_PASSWORD=your_secure_password

# Google Maps API Key (required for maps)
GOOGLE_MAPS_API_KEY=your_google_maps_api_key

# Application Configuration
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
```

## Services

### Web Application (Port 8080)
- PHP 8.2 with Apache
- MongoDB PHP extension
- Serves the main application and reports

### MongoDB (Port 27017)
- MongoDB 7.0
- Persistent data storage
- Automatic initialization with indexes

### Dozzle (Port 9999)
- Real-time log monitoring
- Web-based interface
- Monitors all container logs

## Data Storage

### Collections
- `geo_data` - Main geolocation collection data
- `app_logs` - Application activity logs
- `error_logs` - Error tracking

### Volumes
- `mongodb_data` - MongoDB persistent storage
- `./logs` - Application log files (fallback)

## Development

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f web
docker-compose logs -f mongodb
```

### Database Access
```bash
# Connect to MongoDB shell
docker-compose exec mongodb mongosh -u geoakim_user -p secure_password_123 geoakim
```

### Rebuild Application
```bash
# After code changes
docker-compose down
docker-compose up --build -d
```

## Features Overview

### Enhanced UI
- Professional toast notifications instead of alerts
- Progress bars for user feedback
- Modern styling with CSS variables
- Responsive design

### MongoDB Integration
- Replaces JSON file storage
- Automatic failover to file storage if database unavailable
- Indexed collections for better performance
- Structured logging

### Monitoring
- Dozzle provides real-time log viewing
- Error tracking in separate collection
- Application activity monitoring

### Security
- Environment-based configuration
- No hardcoded credentials
- .env file excluded from git

## Troubleshooting

### MongoDB Connection Issues
```bash
# Check MongoDB status
docker-compose exec mongodb mongosh --eval "db.runCommand({ping:1})"

# Restart MongoDB
docker-compose restart mongodb
```

### Application Errors
```bash
# Check application logs
docker-compose logs web

# Check fallback log files
tail -f logs/fallback.log
```

### Reset Everything
```bash
# Stop and remove all containers and volumes
docker-compose down -v

# Remove built image
docker rmi geoakim-web

# Start fresh
docker-compose up --build -d
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test with Docker Compose
5. Submit a pull request

## License

This project is licensed under the MIT License.