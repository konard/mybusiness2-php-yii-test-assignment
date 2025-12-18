# MyBusiness2 PHP Yii Test Assignment

A test assignment for https://mybusiness2.ru implementing an Apple management system using Yii2 Advanced Template.

## Requirements

- PHP 8.0+
- MySQL 5.7+
- Composer

## Installation

1. Clone the repository:
```bash
git clone https://github.com/konard/mybusiness2-php-yii-test-assignment.git
cd mybusiness2-php-yii-test-assignment
```

2. Install dependencies:
```bash
composer install
```

3. Initialize the application:
```bash
php init --env=Development
```

4. Configure database in `common/config/main-local.php`:
```php
'db' => [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8',
],
```

5. Run migrations:
```bash
php yii migrate
```

6. Create an admin user:
```bash
php yii user/create admin admin@example.com your_password
```

7. Start the backend application:
```bash
php -S localhost:8080 -t backend/web
```

8. Access the backend at http://localhost:8080 and login with your admin credentials.

## Features

### Apple Model

The Apple model (`common/models/Apple.php`) implements the following functionality:

**Properties:**
- `color` - Apple color (green, red, yellow, golden, pink)
- `created_at` - Unix timestamp when apple appeared on tree
- `fallen_at` - Unix timestamp when apple fell from tree
- `status` - Current state (on tree, fallen, rotten)
- `eaten_percent` - Percentage of apple eaten (0-100)
- `size` - Remaining size as decimal (1 = whole, 0 = eaten)

**States:**
- On Tree - Apple is hanging on the tree
- Fallen - Apple has fallen to the ground
- Rotten - Apple is spoiled (after 5 hours on ground)

**Functions:**
- `fallToGround()` - Make the apple fall from the tree
- `eat($percent)` - Eat a portion of the apple
- `createRandom()` - Create a new apple with random color
- `generateRandom($count)` - Generate multiple random apples

**Business Rules:**
- Apple on tree cannot be eaten
- Apple on tree cannot rot
- Apple becomes rotten after 5 hours on ground
- Rotten apple cannot be eaten
- Fully eaten apple is deleted

### Backend Application

The password-protected backend provides:
- Apple list with status indicators
- Generate random apples form
- Fall, eat, delete actions
- Detailed apple view

### Usage Example

```php
$apple = new Apple('green');

echo $apple->color; // green

$apple->eat(50); // Throws exception - cannot eat apple on tree
echo $apple->size; // 1 - decimal

$apple->fallToGround(); // Fall to ground
$apple->eat(25); // Eat 25%
echo $apple->size; // 0.75
```

## Testing

Run unit tests:
```bash
vendor/bin/codecept run common/tests/unit/models/AppleTest.php
```

Run the test script:
```bash
php experiments/test_apple.php
```

## Directory Structure

```
common
    models/              Apple, User, LoginForm models
    tests/               Unit tests for Apple model
console
    controllers/         UserController for CLI user management
    migrations/          Database migrations
backend
    controllers/         AppleController, SiteController
    views/               Apple management views
frontend
    ...                  Standard Yii2 frontend application
```

## License

MIT License
