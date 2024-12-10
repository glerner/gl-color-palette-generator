# Database Setup for Testing

This document outlines the MySQL commands needed to set up the test database for PHPUnit testing.

## Setup Commands

```sql
-- Drop test database if it exists
DROP DATABASE IF EXISTS `wordpress_test`;

-- Create test database
CREATE DATABASE `wordpress_test`;

-- Grant privileges to wordpress user (using % wildcard for host)
GRANT ALL PRIVILEGES ON `wordpress_test`.* TO "wordpress"@"%";

-- Flush privileges to apply changes
FLUSH PRIVILEGES;
```

## Running the Commands

You can run these commands in MySQL using:

1. Lando MySQL CLI:
```bash
lando mysql
```

2. Or directly:
```bash
lando mysql -e "DROP DATABASE IF EXISTS \`wordpress_test\`;"
lando mysql -e "CREATE DATABASE \`wordpress_test\`;"
lando mysql -e "GRANT ALL PRIVILEGES ON \`wordpress_test\`.* TO \"wordpress\"@\"%\";"
lando mysql -e "FLUSH PRIVILEGES;"
```

Note: 
- Database and table names should be enclosed in backticks (\`)
- User names and hosts should be in double quotes
- When using `-e`, the entire MySQL command must be enclosed in double quotes, with inner quotes escaped
- The 'wordpress'@'%' user is pre-configured in Lando with basic privileges. You can verify existing grants using:
  ```sql
  SHOW GRANTS FOR 'wordpress'@'%';
  ```
  Example output:
  ```
  +---------------------------------------------------------------+
  | Grants for wordpress@%                                         |
  +---------------------------------------------------------------+
  | GRANT USAGE ON *.* TO 'wordpress'@'%'                         |
  | GRANT ALL PRIVILEGES ON `wordpress`.* TO 'wordpress'@'%'      |
  | GRANT ALL PRIVILEGES ON `wordpress_test`.* TO 'wordpress'@'%' |
  +---------------------------------------------------------------+
  
