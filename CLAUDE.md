# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the MERAF Production Panel, a CodeIgniter 4-based web application for managing digital licenses and product changelogs. The application handles license validation, user authentication, and various administrative features for digital product management.

## Architecture

### Framework Structure
- **Framework**: CodeIgniter 4 (PHP 8.1+)
- **Architecture**: MVC (Model-View-Controller) pattern
- **Main entry point**: `public/index.php` (web) and `spark` (CLI)
- **Configuration**: `app/Config/` directory contains all configuration files

### Directory Structure
- `app/` - Main application code
  - `Controllers/` - HTTP request handlers (Api.php, Home.php, LicenseManager.php, etc.)
  - `Models/` - Database interaction layer (LicensesModel.php, UserModel.php, etc.)
  - `Views/` - Template files organized by feature
  - `Config/` - Application configuration
  - `Database/Migrations/` - Database schema changes
  - `Database/Seeds/` - Test data seeding
  - `Helpers/` - Custom helper functions
  - `Language/` - Internationalization files
- `system/` - CodeIgniter 4 framework core (do not modify)
- `writable/` - Application logs, cache, uploads
- `tests/` - PHPUnit test files
- `vendor/` - Composer dependencies

### Key Controllers
- `Home.php` - Main dashboard and primary application logic (220KB+)
- `Api.php` - API endpoints for license validation (140KB+)
- `LicenseManager.php` - License management functionality
- `AuthController.php` - User authentication
- `Cronjob.php` - Scheduled task handlers

## Development Commands

### Dependency Management
```bash
composer install          # Install PHP dependencies
composer update           # Update dependencies
```

### Testing
```bash
# Run all tests
./phpunit
# or on Windows
vendor\bin\phpunit

# Run specific test directory
./phpunit app/Models

# Run tests with coverage
./phpunit --colors --coverage-text=tests/coverage.txt --coverage-html=tests/coverage/ -d memory_limit=1024m
```

### CLI Commands (Spark)
```bash
./spark                   # List available commands
./spark migrate           # Run database migrations
./spark db:seed           # Run database seeders
./spark cache:clear       # Clear application cache
./spark routes            # List all routes
```

### Code Quality
The project includes these development dependencies:
- **PHPUnit** for testing (version 10.5.16+ or 11.2+)
- **PHP-CS-Fixer** for code formatting (version 3.47.1+)
- **CodeIgniter Coding Standard** for style consistency
- **Kint** for debugging and development

## Database Configuration

Tests require database configuration in `app/Config/Database.php` under the 'tests' group. Set up a test database before running the full test suite.

## Key Features

### License Management System
- License validation and activation
- Device/domain registration tracking
- Email-based license distribution
- Purchase verification via Envato API

### Authentication & Security
- User authentication with CodeIgniter Shield
- IP blocking functionality
- Session management
- Role-based access control

### Notification System
- Email notifications for license events
- Admin notification management
- Template-based email system

## Environment Setup

1. Ensure PHP 8.1+ with required extensions (intl, mbstring, etc.)
2. Set up database connection in `.env` or `app/Config/Database.php`
3. Configure application settings in `app/Config/` files
4. Set proper file permissions on `writable/` directory
5. Run `composer install` to install dependencies

## Development Notes

- All new controllers should extend `BaseController`
- Use CodeIgniter's built-in helpers and libraries when possible
- Follow PSR-4 autoloading conventions
- Database interactions should go through Models, not direct queries in Controllers
- Use migrations for database schema changes
- The application uses output compression for performance optimization

### Important Helper Functions
- `getMyConfig()` - Dynamic configuration loading from database
- `generateLicenseKey()` - Secure license key generation
- `setMyTimezone()` - User timezone management
- `setMyLocale()` - Internationalization support
- `initLicenseManager()` - License system initialization

### Security Considerations
- All timestamps stored in UTC timezone
- IP blocking system for abuse prevention
- Rate limiting on API endpoints (15 requests/minute default)
- Multi-layer input validation and sanitization
- Secret key authentication for API access