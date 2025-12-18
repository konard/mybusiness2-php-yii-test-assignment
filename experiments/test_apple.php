<?php
/**
 * Test script for Apple model functionality.
 * This script demonstrates the usage of Apple model as per the requirements.
 *
 * Usage: php experiments/test_apple.php
 */

// Bootstrap Yii2 application
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../console/config/main.php',
    require __DIR__ . '/../console/config/main-local.php'
);

$application = new yii\console\Application($config);

use common\models\Apple;

echo "=== Apple Model Test Script ===\n\n";

// Clean up existing apples for clean test
Apple::deleteAll();

// Create a green apple (as per the issue example)
echo "1. Creating a green apple:\n";
$apple = Apple::createRandom('green');
$apple->save();

echo "   \$apple = new Apple('green');\n";
echo "   echo \$apple->color; // " . $apple->color . "\n\n";

// Try to eat apple on tree (should throw exception)
echo "2. Trying to eat apple on tree:\n";
try {
    $apple->eat(50);
    echo "   ERROR: Should have thrown an exception!\n";
} catch (\yii\base\InvalidCallException $e) {
    echo "   \$apple->eat(50); // " . $e->getMessage() . "\n";
}
echo "   echo \$apple->size; // " . $apple->size . " - decimal\n\n";

// Make apple fall
echo "3. Making apple fall to ground:\n";
echo "   \$apple->fallToGround(); // упасть на землю\n";
$apple->fallToGround();
echo "   Status: " . $apple->statusLabel . "\n\n";

// Eat 25% of apple
echo "4. Eating 25% of apple:\n";
echo "   \$apple->eat(25); // откусить четверть яблока\n";
$apple->eat(25);
echo "   echo \$apple->size; // " . number_format($apple->size, 2) . "\n";
echo "   Eaten percent: " . $apple->eaten_percent . "%\n\n";

// Additional tests
echo "5. Additional functionality tests:\n\n";

// Test eating more
echo "   Eating 50% more...\n";
$apple->eat(50);
echo "   Size after eating 50% more: " . number_format($apple->size, 2) . "\n";
echo "   Eaten percent: " . $apple->eaten_percent . "%\n\n";

// Create another apple and test rotten status
echo "6. Testing rotten apple:\n";
$rottenApple = Apple::createRandom('red');
$rottenApple->status = Apple::STATUS_FALLEN;
$rottenApple->fallen_at = time() - (6 * 60 * 60); // Fell 6 hours ago
$rottenApple->save();

echo "   Created red apple that fell 6 hours ago\n";
echo "   Status before check: " . $rottenApple->statusLabel . "\n";

// Trigger rotten check
$isRotten = $rottenApple->isRotten;
echo "   Status after isRotten check: " . $rottenApple->statusLabel . "\n";

try {
    $rottenApple->eat(10);
    echo "   ERROR: Should have thrown an exception!\n";
} catch (\yii\base\InvalidCallException $e) {
    echo "   Trying to eat rotten apple: " . $e->getMessage() . "\n";
}

// Generate random apples
echo "\n7. Generating random apples:\n";
$count = Apple::generateRandom(5);
echo "   Generated {$count} random apples\n";
$totalApples = Apple::find()->count();
echo "   Total apples in database: {$totalApples}\n";

// List all apples
echo "\n8. All apples in database:\n";
$apples = Apple::find()->all();
foreach ($apples as $a) {
    $a->checkAndUpdateRottenStatus();
    echo "   - ID: {$a->id}, Color: {$a->color}, Status: {$a->statusLabel}, Size: " . number_format($a->size, 2) . "\n";
}

echo "\n=== Test completed successfully! ===\n";
