# dblibrary

Reusable PDO connection manager for PHP projects.

## Installation

```bash
composer require sdl/dblibrary
```
## Use

Set up a .env file for security layer
```bash
composer require vlucas/phpdotenv
```

### config/database.php is the interface between the app and .env configuration
```php
<?php

declare(strict_types=1);

return [
    'default' => [
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => isset($_ENV['DB_PORT']) ? (int) $_ENV['DB_PORT'] : 3306,
        'dbname' => $_ENV['DB_NAME'] ?? '',
        'user' => $_ENV['DB_USER'] ?? '',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
    ],

    'personal' => [
        'driver' => 'pgsql',
        'host' => $_ENV['DIARY_DB_HOST'] ?? '127.0.0.1',
        'port' => isset($_ENV['DIARY_DB_PORT']) ? (int) $_ENV['DIARY_DB_PORT'] : 5432,
        'dbname' => $_ENV['DIARY_DB_NAME'] ?? '',
        'user' => $_ENV['DIARY_DB_USER'] ?? '',
        'pass' => $_ENV['DIARY_DB_PASS'] ?? '',
    ],
];
```
### .env.example
```
DB_HOST=
DB_PORT=
DB_NAME=
DB_USER=
DB_PASS=

DIARY_DB_HOST=
DIARY_DB_PORT=
DIARY_DB_NAME=
DIARY_DB_USER=
DIARY_DB_PASS=
```

Use `.env.testing` for PHPUnit setup



