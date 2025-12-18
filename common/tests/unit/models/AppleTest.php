<?php

namespace common\tests\unit\models;

use common\models\Apple;

/**
 * Apple model test
 */
class AppleTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // Clean up any existing apples before each test
        Apple::deleteAll();
    }

    public function testCreateRandom()
    {
        $apple = Apple::createRandom();

        verify($apple->color)->notEmpty();
        verify(in_array($apple->color, Apple::COLORS))->true();
        verify($apple->status)->equals(Apple::STATUS_ON_TREE);
        verify($apple->eaten_percent)->equals(0);
        verify($apple->created_at)->notEmpty();
        verify($apple->created_at <= time())->true();
        verify($apple->fallen_at)->null();
    }

    public function testCreateWithSpecificColor()
    {
        $apple = Apple::createRandom('red');

        verify($apple->color)->equals('red');
    }

    public function testSize()
    {
        $apple = Apple::createRandom();
        $apple->eaten_percent = 0;
        verify($apple->size)->equals(1.0);

        $apple->eaten_percent = 25;
        verify($apple->size)->equals(0.75);

        $apple->eaten_percent = 50;
        verify($apple->size)->equals(0.5);

        $apple->eaten_percent = 100;
        verify($apple->size)->equals(0.0);
    }

    public function testFallToGround()
    {
        $apple = Apple::createRandom();
        $apple->save();

        verify($apple->status)->equals(Apple::STATUS_ON_TREE);
        verify($apple->isOnTree)->true();
        verify($apple->isFallen)->false();

        $result = $apple->fallToGround();

        verify($result)->true();
        verify($apple->status)->equals(Apple::STATUS_FALLEN);
        verify($apple->fallen_at)->notNull();
        verify($apple->isOnTree)->false();
        verify($apple->isFallen)->true();
    }

    public function testCannotFallTwice()
    {
        $apple = Apple::createRandom();
        $apple->save();
        $apple->fallToGround();

        $this->expectException(\yii\base\InvalidCallException::class);
        $this->expectExceptionMessage('Apple has already fallen from the tree.');

        $apple->fallToGround();
    }

    public function testCannotEatAppleOnTree()
    {
        $apple = Apple::createRandom();
        $apple->save();

        $this->expectException(\yii\base\InvalidCallException::class);
        $this->expectExceptionMessage('Cannot eat the apple - it is still on the tree.');

        $apple->eat(25);
    }

    public function testEatFallenApple()
    {
        $apple = Apple::createRandom();
        $apple->save();
        $apple->fallToGround();

        $deleted = $apple->eat(25);

        verify($deleted)->false();
        verify($apple->eaten_percent)->equals(25);
        verify($apple->size)->equals(0.75);
    }

    public function testEatMultipleTimes()
    {
        $apple = Apple::createRandom();
        $apple->save();
        $apple->fallToGround();

        $apple->eat(25);
        verify($apple->eaten_percent)->equals(25);
        verify($apple->size)->equals(0.75);

        $apple->eat(25);
        verify($apple->eaten_percent)->equals(50);
        verify($apple->size)->equals(0.5);

        $apple->eat(25);
        verify($apple->eaten_percent)->equals(75);
        verify($apple->size)->equals(0.25);
    }

    public function testEatFullyDeletesApple()
    {
        $apple = Apple::createRandom();
        $apple->save();
        $appleId = $apple->id;
        $apple->fallToGround();

        $deleted = $apple->eat(100);

        verify($deleted)->true();
        verify(Apple::findOne($appleId))->null();
    }

    public function testEatMoreThan100DeletesApple()
    {
        $apple = Apple::createRandom();
        $apple->save();
        $appleId = $apple->id;
        $apple->fallToGround();

        $apple->eat(50);
        $deleted = $apple->eat(75); // Total would be 125%

        verify($deleted)->true();
        verify(Apple::findOne($appleId))->null();
    }

    public function testCannotEatInvalidPercent()
    {
        $apple = Apple::createRandom();
        $apple->save();
        $apple->fallToGround();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Percent must be between 0 and 100.');

        $apple->eat(0);
    }

    public function testCannotEatNegativePercent()
    {
        $apple = Apple::createRandom();
        $apple->save();
        $apple->fallToGround();

        $this->expectException(\InvalidArgumentException::class);

        $apple->eat(-10);
    }

    public function testCannotEatRottenApple()
    {
        $apple = Apple::createRandom();
        $apple->status = Apple::STATUS_ROTTEN;
        $apple->save();

        $this->expectException(\yii\base\InvalidCallException::class);
        $this->expectExceptionMessage('Cannot eat the apple - it is rotten.');

        $apple->eat(25);
    }

    public function testAppleBecomesRotten()
    {
        $apple = Apple::createRandom();
        $apple->status = Apple::STATUS_FALLEN;
        $apple->fallen_at = time() - Apple::ROTTEN_TIME - 1; // Fallen more than 5 hours ago
        $apple->save();

        // Trigger rotten check
        $isRotten = $apple->isRotten;

        verify($isRotten)->true();
        verify($apple->status)->equals(Apple::STATUS_ROTTEN);
    }

    public function testAppleNotRottenIfRecentlyFallen()
    {
        $apple = Apple::createRandom();
        $apple->status = Apple::STATUS_FALLEN;
        $apple->fallen_at = time() - 60; // Fallen 1 minute ago
        $apple->save();

        $isRotten = $apple->isRotten;

        verify($isRotten)->false();
        verify($apple->status)->equals(Apple::STATUS_FALLEN);
    }

    public function testAppleOnTreeCannotBeRotten()
    {
        $apple = Apple::createRandom();
        $apple->save();

        // Even if created long ago, apple on tree should not be rotten
        $apple->created_at = time() - 7 * 24 * 60 * 60; // Created 7 days ago
        $apple->save();

        verify($apple->isRotten)->false();
        verify($apple->status)->equals(Apple::STATUS_ON_TREE);
    }

    public function testStatusLabels()
    {
        $apple = Apple::createRandom();
        verify($apple->statusLabel)->equals('On Tree');

        $apple->status = Apple::STATUS_FALLEN;
        $apple->fallen_at = time();
        verify($apple->statusLabel)->equals('Fallen');

        $apple->status = Apple::STATUS_ROTTEN;
        verify($apple->statusLabel)->equals('Rotten');
    }

    public function testGenerateMultipleApples()
    {
        $count = Apple::generateRandom(5);

        verify($count)->equals(5);
        verify(Apple::find()->count())->equals(5);
    }

    public function testGetStatusOptions()
    {
        $options = Apple::getStatusOptions();

        verify($options)->arrayHasKey(Apple::STATUS_ON_TREE);
        verify($options)->arrayHasKey(Apple::STATUS_FALLEN);
        verify($options)->arrayHasKey(Apple::STATUS_ROTTEN);
    }

    public function testValidation()
    {
        $apple = new Apple();
        $apple->color = 'invalid_color';

        verify($apple->validate())->false();
        verify($apple->errors)->arrayHasKey('color');
    }

    public function testValidColorValidation()
    {
        foreach (Apple::COLORS as $color) {
            $apple = new Apple();
            $apple->color = $color;
            $apple->created_at = time();

            verify($apple->validate(['color']))->true();
        }
    }
}
