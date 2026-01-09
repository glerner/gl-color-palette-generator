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

1. Create a .lando.yml file:

- If you already have a .lando.yml, merge the contents of .lando.example.yml into it.
- Otherwise, copy .lando.example.yml to .lando.yml:
   ```bash
   cp .lando.example.yml .lando.yml
   ```

2. Install Lando:
   Visit https://docs.lando.dev/getting-started/installation.html
   Follow the installation instructions for your operating system
   Verify installation with `lando version`

3. Start the environment:
   ```bash
   lando start
   ```

4. Install dependencies:
   ```bash
   lando composer install
   ```

5. Rebuild the environment:
   ```bash
   lando rebuild -y
   ```

6. Access the development sites at the URLs listed above.

---

## Docker and Lando Disk Usage Maintenance

Docker disk usage can grow quickly when working with Lando and WordPress, especially after importing large databases or running many projects. This section provides clear, actionable steps to understand, monitor, and reclaim disk space.

### 1. Check Docker Disk Usage
- **Show overall Docker disk usage:**
  ```sh
  docker system df
  ```
- **Show the size of each Docker volume:**
  ```sh
  docker volume ls -q | xargs -I {} sh -c 'echo -n "{}: "; docker run --rm -v {}:/volume alpine du -sh /volume | cut -f1'
  ```
  _(The first run will download the small `alpine` image.)_

### 2. Identify Volumes in Use or Unused
- **See which containers use a volume:**
  ```sh
  docker ps -a --filter volume=<volume_name>
  ```
- **List all volumes, showing if they are used or unused:**
  ```sh
  docker volume ls -q | xargs -n1 sh -c 'c=$(docker ps -a --filter volume=$0 --format "{{.Names}}" ); if [ -n "$c" ]; then echo "$0: used by $c"; else echo "$0: UNUSED"; fi'
  ```
- **Inspect a volume for more details (labels, mountpoints):**
  ```sh
  docker volume inspect <volume_name>
  ```

#### Interpretation:
- **"UNUSED"** = The volume is not attached to any container (safe to remove).
- **"used by ..."** = The volume is still attached to a running or stopped container.

### 3. Remove Unused Volumes
- **Remove a single unused volume:**
  ```sh
  docker volume rm <volume_name>
  ```
- **Remove all unused (dangling) volumes automatically:**
  ```sh
  docker volume prune
  ```
- **Automate removal of all truly unused volumes (after shutting down all containers, e.g. with `lando poweroff`):**
  ```sh
  for v in $(docker volume ls -q); do
    c=$(docker ps -a --filter volume=$v --format "{{.Names}}")
    if [ -z "$c" ]; then
      echo "Removing unused volume: $v"
      docker volume rm "$v"
    fi
  done
  ```

**Note:** To fully clean up, you may need to remove stopped containers before some volumes become "unused".

### 4. MySQL Database Volume Bloat
- **Large database imports can cause MySQL volumes to grow rapidly due to binary logs and temp files.**
- **Clear MySQL binary logs (if not needed):**
  ```sh
  lando mysql -e "RESET MASTER;"
  ```
- **Optimize tables and reclaim space:**
  ```sh
  lando mysqlcheck -o --all-databases
  ```

Your Lando `recipe: wordpress` may use the Bitnami image under the hood, even if you specify mysql:8.0. This would explain why /var/lib/mysql does not exist.

In Lando, check bitnami folders for MySQL size:
```sh
lando ssh -s database
# and then inside that,
# check size
du -sh /bitnami/mysql/data
# and list files
ls -lh /bitnami/mysql/data
```

### How Lando Uses Disk Space
- Lando uses Docker volumes to persist data for your services (like MySQL) and sometimes for your application files (e.g., `/app/wp-content`).
- When you run Lando, your project directory is mounted into the container, so changes in `wp-content` (uploads, plugins, etc.) are reflected on your host.
- However, Docker volumes for services and old appserver containers can accumulate over time, especially after rebuilds, database imports, or destroying/recreating environments.
- These unused volumes are not automatically deleted and can take up significant disk space.

#### Regular Cleanup is Important
- Regularly removing unused Docker volumes is essential to prevent disk space from being consumed by orphaned data.
- After stopping all Lando projects (e.g., with `lando poweroff`), you can safely remove unused volumes with the following script:

```sh
for v in $(docker volume ls -q); do
  c=$(docker ps -a --filter volume=$v --format "{{.Names}}")
  if [ -z "$c" ]; then
    echo "Removing unused volume: $v"
    docker volume rm "$v"
  fi
 done
```

- This script checks each Docker volume to see if it is attached to any container (running or stopped). If not, it removes the volume and frees up disk space.
- Running this script after major changes, rebuilds, or database imports will help keep your system clean and performant.

### Note on Frequent Rebuilds and Disk Usage

Frequent `lando rebuild` operations are normal and often necessary when:
- You change `.lando.yml` (services, environment variables, ports, etc.)
- You update Lando itself
- You update Docker, PHP, MySQL, or other stack components
- You encounter errors or disk space issues (e.g., running out of disk space with a container open)

However, each rebuild can leave behind unused Docker volumes and containers, which may consume significant disk space over time.

**To keep your system clean:**
- Regularly run the unused volume cleanup script above after major changes or troubleshooting.
- Monitor disk usage with `docker system df`.
- Occasionally run `docker system prune` for a more aggressive cleanup (after reviewing what will be removed).

This will help ensure that frequent rebuilds do not result in disk space being consumed by orphaned or outdated Docker data.

### 5. Best Practices
- Never remove volumes in use by your active Lando project.
- Regularly check disk usage if you do frequent imports or have many projects.
- After major cleanups, re-run the disk usage commands to verify space was reclaimed.

For more details, see the [Lando docs](https://docs.lando.dev/) and [Docker docs](https://docs.docker.com/).
- Use `ncdu` for interactive disk usage browsing (install if needed).
