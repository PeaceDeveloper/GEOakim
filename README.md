# GEOakim

GEOakim is a PHP-based geo-tracking application that collects user device information and geolocation data through a web interface designed to mimic Microsoft Teams.

## Features

- Collects device information (GPU, screen resolution, platform, etc.)
- Geolocation tracking with user permission
- Interactive reporting dashboard with Google Maps integration
- Data export to CSV
- Docker containerized deployment
- ngrok integration for public access
- Log monitoring with Dozzle

## Prerequisites

- Docker and Docker Compose
- ngrok account with authtoken (for public access)
- Google Maps API key (for map functionality)

## Quick Start

1. **Clone the repository:**
   ```bash
   git clone https://github.com/PeaceDeveloper/GEOakim.git
   cd GEOakim
   ```

2. **Configure environment variables:**
   Copy the `.env` file and update the values:
   ```bash
   cp .env .env.local
   # Edit .env.local with your actual values
   ```

   Required environment variables:
   - `GOOGLE_MAPS_API_KEY`: Your Google Maps API key
   - `NGROK_AUTHTOKEN`: Your ngrok authentication token

3. **Start the application:**
   ```bash
   docker-compose up -d
   ```

4. **Access the application:**
   - Local access: http://localhost:8080
   - Public access: Check ngrok dashboard at http://localhost:4040
   - Logs monitoring: http://localhost:9999

## Services

### Web Application (Port 8080)
- **index.html**: Main data collection interface
- **result.php**: Data processing endpoint
- **relatorio.php**: Reporting dashboard

### ngrok (Port 4040)
Provides secure tunneling to expose your local application to the internet:
- Dashboard: http://localhost:4040
- Automatically generates HTTPS URLs
- Useful for testing webhooks or sharing with external users

### Dozzle (Port 9999)
Real-time log monitoring for all Docker containers:
- Web interface: http://localhost:9999
- Monitor application logs, errors, and access patterns
- Filter logs by container

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_PORT` | Application port | 8080 |
| `TIMEZONE` | Application timezone | America/Sao_Paulo |
| `GOOGLE_MAPS_API_KEY` | Google Maps API key | YOUR_GOOGLE_MAPS_API_KEY_HERE |
| `NGROK_AUTHTOKEN` | ngrok authentication token | YOUR_NGROK_AUTHTOKEN_HERE |
| `NGROK_PROTOCOL` | ngrok protocol | http |
| `NGROK_PORT` | ngrok target port | 8080 |
| `DOZZLE_PORT` | Dozzle dashboard port | 9999 |
| `DATA_VOLUME` | Data storage path | ./data |
| `LOGS_VOLUME` | Logs storage path | ./logs |

### Getting API Keys

#### Google Maps API Key
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the Maps JavaScript API
4. Create credentials (API Key)
5. Restrict the API key to your domain for security

#### ngrok Authtoken
1. Sign up at [ngrok.com](https://ngrok.com/)
2. Go to your dashboard
3. Copy your authtoken from the "Your Authtoken" section

## Data Storage

The application stores collected data in JSON format:
- `data/data.json`: Structured data for dashboard
- `data/log.txt`: Raw log entries
- `logs/`: Apache access and error logs

## Development

### Local Development
For development without Docker:
1. Ensure PHP 8.1+ with Apache is installed
2. Place files in your web server directory
3. Set environment variables or update hardcoded values
4. Access via your local web server

### File Structure
```
GEOakim/
├── index.html          # Main collection interface
├── result.php          # Data processing endpoint
├── relatorio.php       # Reporting dashboard
├── Dockerfile          # Application container
├── docker-compose.yml  # Service orchestration
├── ngrok.yml          # ngrok configuration
├── .env               # Environment variables template
├── data/              # Data storage directory
├── logs/              # Log storage directory
└── README.md          # This file
```

## Security Considerations

- Never commit real API keys or tokens to version control
- Use environment variables for sensitive configuration
- Restrict Google Maps API key to your domain
- Consider implementing rate limiting for data collection endpoints
- Regularly rotate ngrok authtokens
- Monitor access logs for suspicious activity

## Troubleshooting

### Common Issues

1. **ngrok tunnel not working:**
   - Verify your authtoken is correct
   - Check ngrok dashboard at http://localhost:4040
   - Ensure the web service is running

2. **Google Maps not loading:**
   - Verify your API key is correct
   - Check if the Maps JavaScript API is enabled
   - Ensure API key restrictions allow your domain

3. **Data not saving:**
   - Check file permissions in the data directory
   - Verify the web container has write access
   - Check container logs via Dozzle

### Viewing Logs
```bash
# View all container logs
docker-compose logs

# View specific service logs
docker-compose logs web
docker-compose logs ngrok
docker-compose logs dozzle

# Follow logs in real-time
docker-compose logs -f
```

### Stopping Services
```bash
# Stop all services
docker-compose down

# Stop and remove volumes
docker-compose down -v
```

## License

This project is provided as-is for educational and testing purposes. Please ensure compliance with applicable privacy laws and regulations when collecting user data.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For issues and questions, please use the GitHub Issues page.