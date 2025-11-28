# Railway Deployment Guide

This guide explains how to deploy the Providence Financial Platform to Railway using one-click deployment.

## Prerequisites

Before deploying, ensure you have:
1. A Railway account (https://railway.app)
2. A MySQL database (can be provisioned via Railway)
3. Required environment variables ready

## One-Click Deployment

### Option 1: Deploy from GitHub

1. Click the "Deploy on Railway" button in the repository
2. Connect your GitHub account if not already connected
3. Railway will automatically detect the Dockerfile configuration
4. Configure environment variables (see below)
5. Click "Deploy"

### Option 2: Manual Deployment via Railway CLI

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Initialize project
railway init

# Link to existing project (or create new)
railway link

# Deploy
railway up
```

## Environment Variables

Configure the following environment variables in Railway dashboard:

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_HOST` | MySQL host | `mysql.railway.internal` |
| `DB_PORT` | MySQL port | `3306` |
| `DB_DATABASE` | Database name | `providence` |
| `DB_USERNAME` | Database user | `root` |
| `DB_PASSWORD` | Database password | `your-password` |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_CHARSET` | Database charset | `utf8mb4` |
| `DB_PREFIX` | Table prefix | (empty) |
| `JWT_SECRET` | Secret key for JWT tokens | - |
| `TELEGRAM_BOT_TOKEN` | Telegram bot token for notifications | - |
| `TELEGRAM_CHAT_ID` | Telegram chat ID | - |
| `TRONGRID_API_KEY` | TronGrid API key for USDT payments | - |
| `OPENAI_API_KEY` | OpenAI API key for AI features | - |

> **Note:** When using Railway's MySQL add-on, database environment variables are automatically provided as `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, and `MYSQLPASSWORD`. You can reference these in your Railway service configuration.

## Database Setup

### Using Railway's MySQL Add-on

1. Go to your Railway project dashboard
2. Click "New" → "Database" → "Add MySQL"
3. Railway will automatically create the database
4. Use the provided connection variables

### Database Migration

After deploying, import the database schema:

```sql
-- Connect to your database and run the SQL files from:
-- providence-admin/sql/
```

## Project Structure

```
zijin/
├── providence/          # Frontend mobile web app
│   ├── index.html      # Main entry point
│   ├── js/             # JavaScript files
│   └── css/            # Stylesheets
├── providence-admin/    # Admin panel & API
│   ├── admin/          # Admin panel HTML
│   ├── api/            # PHP API endpoints
│   ├── config/         # Configuration files
│   └── vendor/         # Composer dependencies
├── Dockerfile          # Docker build configuration
├── nginx.conf          # Nginx server configuration
├── php-fpm.conf        # PHP-FPM configuration
├── supervisord.conf    # Process manager configuration
├── railway.toml        # Railway deployment settings
└── .env.example        # Environment variables template
```

## Accessing the Application

After deployment, you can access:

- **Frontend**: `https://your-app.railway.app/`
- **Admin Panel**: `https://your-app.railway.app/providence-admin/admin/`
- **API**: `https://your-app.railway.app/providence-admin/api/`

## Troubleshooting

### Build Failures

1. Check Railway build logs for errors
2. Ensure all dependencies are correctly specified in `composer.json`
3. Verify PHP extensions are installed in Dockerfile

### Database Connection Issues

1. Verify environment variables are correctly set
2. Check if MySQL service is running
3. Ensure database user has proper permissions

### 502 Bad Gateway

1. Check PHP-FPM logs
2. Verify nginx configuration
3. Ensure port 8080 is correctly exposed

## Local Development

To run locally with Docker:

```bash
# Build the image
docker build -t zijin .

# Run the container
docker run -p 8080:8080 \
  -e DB_HOST=host.docker.internal \
  -e DB_DATABASE=providence \
  -e DB_USERNAME=root \
  -e DB_PASSWORD=password \
  zijin
```

## Support

For issues or questions, please open a GitHub issue.
