# Lando Configuration Guide

This document explains the advanced Lando configuration used in [.lando.example.yml](../.lando.example.yml) for plugin development.

## Basic Configuration

```yaml
name: gl-color-palette-generator
recipe: wordpress
config:
  php: '8.1'
  ssl: true
  xdebug: true
```

The basic configuration uses the WordPress recipe with PHP 8.1, SSL, and Xdebug enabled. PHP 8.1 is chosen for optimal WordPress compatibility while maintaining modern PHP features.

## Services

### Appserver
```yaml
services:
  appserver:
	config:
	  php: php.ini
	ports:
	  - "443"
```
The appserver configuration:

- Uses a custom php.ini for PHP configuration
- Forces HTTPS (port 443) for security
- Includes composer configuration for PHPUnit testing

### Database
```yaml
services:
  database:
	type: mysql:8.0
	healthcheck: mysql -uroot --silent --execute "SHOW DATABASES;"
```

MySQL 8.0 is used with a healthcheck to ensure database availability.

### Development Tools

```yaml
  mailhog:
	type: mailhog
	hogfrom:
	  - appserver
  pma:
	type: phpmyadmin
	hosts:
	  - database
```

Additional development services:
- MailHog for email testing (https://mail.lndo.site)
- PHPMyAdmin for database management (https://pma.lndo.site)

## Build Configuration

```yaml
	build_as_root:
	  - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
	  - apt-get update -y
	  - apt-get install -y git
	build:
	  - composer install
```

Build steps:
1. Install Composer within the container
   - This ensures a consistent Composer version across all developers
   - Prevents conflicts with host system's Composer installation
   - Ensures PHP version compatibility
2. Install Git within the container
   - Required by Composer to clone dependencies from GitHub
   - Used when installing packages that reference Git repositories
   - Not related to versioning the plugin itself (use your host system's Git for that)

## Testing Configuration
```yaml
	run_as_root:
	  # Create log file and set permissions
	  - mkdir -p /var/www/html/logs
	  - touch /var/www/html/logs/xdebug.log
	  - chmod 777 /var/www/html/logs/xdebug.log
```

Test environment setup:
- Creates necessary directories and files
- Sets appropriate permissions
- Configures Xdebug logging

## Environment Variables

```yaml
	environment:
	  HTTP_HOST: 'gl-color-palette.lndo.site'
	  BUILD_PATH: '/app/wp-content/plugins/gl-color-palette-generator'
	  XDEBUG_MODE: 'debug,develop,trace,coverage'
	  XDEBUG_CONFIG: 'client_host=host.docker.internal client_port=9003 log=/var/www/html/logs/xdebug.log'
	  WP_TESTS_DIR: /app/wordpress-phpunit
```

Key environment variables for:
- Domain configuration
- Build path location
- Xdebug settings
- WordPress test locations

## Test Commands

Various test commands are available:

- `lando test` - Run all non-WP-Mock tests
- `lando test:wp-mock` - Run WP Mock tests
- `lando test:unit` - Run unit tests
- `lando test:integration` - Run integration tests
- `lando test:coverage` - Generate code coverage report
- `lando test:admin` - Run admin tests
- `lando test:core` - Run core tests
- `lando test:providers` - Run provider tests

## Events
```yaml
events:
  post-db-import:
	- appserver: wp search-replace "glerner.com" "gl-color-palette.lndo.site"
```

Automated events:
- Domain replacement after database import

## Performance Optimization
```yaml
excludes:
  - vendor          # Composer dependencies
  - node_modules    # NPM dependencies
  - wordpress-phpunit  # Test framework
  - .git           # Version control
```

Excludes prevent unnecessary files from being mounted into the container, improving performance.

## URLs

- Main site: https://gl-color-palette.lndo.site
- PHPMyAdmin: https://pma.lndo.site
- MailHog: https://mail.lndo.site

## Getting Started

1. Copy .lando.example.yml to .lando.yml:
   ```bash
   cp .lando.example.yml .lando.yml
   ```
   If you already have a .lando.yml, merge the contents of .lando.example.yml into it.

2. Start the environment:
   ```bash
   lando start
   ```

3. Install dependencies:
   ```bash
   lando composer install
   ```

4. Access the development sites at the URLs listed above.
