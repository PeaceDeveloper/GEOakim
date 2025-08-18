# GEOakim - Geolocation Data Collector

A web application that collects device and geolocation information using a fake Microsoft Teams interface.

## Features

- Collects device information (GPU, screen resolution, platform, language, timezone)
- Geolocation collection with user permission
- Data storage in MongoDB
- Real-time log viewing with Dozzle
- External access via Ngrok tunnels
- Prettier toast notifications instead of browser alerts
- Admin interface for viewing collected data

## Docker Services

- **Web Application**: PHP 8.2 with Apache serving the application
- **MongoDB**: Database for storing collected entries
- **MongoDB Express**: Web interface for database management
- **Ngrok**: Tunneling service for external access
- **Dozzle**: Real-time log viewer for all containers

## Setup

1. Clone the repository
2. Copy the environment file and configure:
   ```bash
   cp .env .env.local
   ```
3. Update the `.env.local` file with your settings:
   - Set `NGROK_AUTHTOKEN` with your ngrok token
   - Adjust ports if needed
   - Set MongoDB credentials

4. Start the application:
   ```bash
   docker-compose up -d
   ```

## Access Points

- **Main Application**: http://localhost:8080
- **Admin Report**: http://localhost:8080/relatorio.php
- **MongoDB Express**: http://localhost:8081
- **Dozzle (Logs)**: http://localhost:9999
- **Ngrok Dashboard**: http://localhost:4040

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `MONGO_INITDB_ROOT_USERNAME` | MongoDB admin username | admin |
| `MONGO_INITDB_ROOT_PASSWORD` | MongoDB admin password | adminpassword |
| `MONGO_DATABASE` | Database name | geoakim |
| `NGROK_AUTHTOKEN` | Your ngrok authentication token | your_ngrok_token_here |
| `PHP_PORT` | Port for the web application | 8080 |
| `DOZZLE_PORT` | Port for Dozzle log viewer | 9999 |
| `MONGO_EXPRESS_PORT` | Port for MongoDB Express | 8081 |

## Usage

1. Access the main application at the configured port
2. The interface mimics Microsoft Teams login
3. Users clicking "Enter Meeting" will be prompted for geolocation
4. All data is collected and stored in MongoDB
5. View reports at `/relatorio.php`
6. Monitor logs through Dozzle
7. Access externally via ngrok tunnels

## Data Structure

Each entry contains:
- Timestamp
- IP address
- User agent
- GPU information (vendor/renderer)
- Screen resolution
- Platform information
- Language settings
- Timezone
- Geolocation (latitude, longitude, accuracy) if permitted

## Security Notes

- This application is for educational/testing purposes
- Ensure proper consent and legal compliance when collecting user data
- Use strong passwords for MongoDB in production
- Configure ngrok authentication for external access