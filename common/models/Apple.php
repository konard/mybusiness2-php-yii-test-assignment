<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Apple model - represents an apple that can be on a tree, fallen, or rotten.
 *
 * @property int $id
 * @property string $color
 * @property int $created_at Unix timestamp when apple appeared on tree
 * @property int|null $fallen_at Unix timestamp when apple fell from tree
 * @property int $status 0=on tree, 1=fallen, 2=rotten
 * @property float $eaten_percent Percentage of apple eaten (0-100)
 *
 * @property float $size Remaining size of apple (1 = whole, 0 = fully eaten)
 * @property string $statusLabel Human-readable status label
 * @property bool $isOnTree Whether apple is on tree
 * @property bool $isFallen Whether apple has fallen
 * @property bool $isRotten Whether apple is rotten
 */
class Apple extends ActiveRecord
{
    /** Apple is on tree */
    const STATUS_ON_TREE = 0;

    /** Apple has fallen from tree */
    const STATUS_FALLEN = 1;

    /** Apple is rotten (after 5 hours on ground) */
    const STATUS_ROTTEN = 2;

    /** Time in seconds after which fallen apple becomes rotten (5 hours) */
    const ROTTEN_TIME = 5 * 60 * 60;

    /** Available apple colors */
    const COLORS = ['green', 'red', 'yellow', 'golden', 'pink'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%apple}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['color'], 'required'],
            [['color'], 'string', 'max' => 50],
            [['color'], 'in', 'range' => self::COLORS],
            [['created_at', 'fallen_at'], 'integer'],
            [['status'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_ON_TREE, self::STATUS_FALLEN, self::STATUS_ROTTEN]],
            [['eaten_percent'], 'number', 'min' => 0, 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'color' => 'Color',
            'created_at' => 'Appeared At',
            'fallen_at' => 'Fallen At',
            'status' => 'Status',
            'eaten_percent' => 'Eaten %',
            'size' => 'Size',
            'statusLabel' => 'Status',
        ];
    }

    /**
     * Creates a new apple with random color and random creation time.
     * The creation time is within the last 7 days.
     *
     * @param string|null $color Optional specific color, if null - random color is used
     * @return static
     */
    public static function createRandom(?string $color = null): self
    {
        $apple = new self();
        $apple->color = $color ?? self::COLORS[array_rand(self::COLORS)];
        // Random time within last 7 days
        $apple->created_at = time() - rand(0, 7 * 24 * 60 * 60);
        $apple->status = self::STATUS_ON_TREE;
        $apple->eaten_percent = 0;
        return $apple;
    }

    /**
     * Get the remaining size of the apple (decimal from 0 to 1).
     * Example: if 25% eaten, size = 0.75
     *
     * @return float
     */
    public function getSize(): float
    {
        return round((100 - $this->eaten_percent) / 100, 2);
    }

    /**
     * Check if apple is currently on tree.
     *
     * @return bool
     */
    public function getIsOnTree(): bool
    {
        $this->checkAndUpdateRottenStatus();
        return $this->status === self::STATUS_ON_TREE;
    }

    /**
     * Check if apple has fallen but not rotten.
     *
     * @return bool
     */
    public function getIsFallen(): bool
    {
        $this->checkAndUpdateRottenStatus();
        return $this->status === self::STATUS_FALLEN;
    }

    /**
     * Check if apple is rotten.
     *
     * @return bool
     */
    public function getIsRotten(): bool
    {
        $this->checkAndUpdateRottenStatus();
        return $this->status === self::STATUS_ROTTEN;
    }

    /**
     * Get human-readable status label.
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        $this->checkAndUpdateRottenStatus();

        switch ($this->status) {
            case self::STATUS_ON_TREE:
                return 'On Tree';
            case self::STATUS_FALLEN:
                return 'Fallen';
            case self::STATUS_ROTTEN:
                return 'Rotten';
            default:
                return 'Unknown';
        }
    }

    /**
     * Check if apple should be rotten and update status if needed.
     * Apple becomes rotten after 5 hours of lying on ground.
     */
    public function checkAndUpdateRottenStatus(): void
    {
        if ($this->status === self::STATUS_FALLEN && $this->fallen_at !== null) {
            if ((time() - $this->fallen_at) >= self::ROTTEN_TIME) {
                $this->status = self::STATUS_ROTTEN;
                $this->save(false, ['status']);
            }
        }
    }

    /**
     * Make the apple fall from tree.
     *
     * @throws \yii\base\InvalidCallException if apple already fell
     * @return bool
     */
    public function fallToGround(): bool
    {
        if ($this->status !== self::STATUS_ON_TREE) {
            throw new \yii\base\InvalidCallException('Apple has already fallen from the tree.');
        }

        $this->status = self::STATUS_FALLEN;
        $this->fallen_at = time();

        return $this->save(false, ['status', 'fallen_at']);
    }

    /**
     * Eat a portion of the apple.
     *
     * @param float $percent Percentage to eat (0-100)
     * @throws \yii\base\InvalidCallException if apple cannot be eaten
     * @throws \InvalidArgumentException if percent is invalid
     * @return bool Whether apple was deleted (fully eaten)
     */
    public function eat(float $percent): bool
    {
        // Check for rotten status first
        $this->checkAndUpdateRottenStatus();

        // Validate percent
        if ($percent <= 0 || $percent > 100) {
            throw new \InvalidArgumentException('Percent must be between 0 and 100.');
        }

        // Cannot eat apple on tree
        if ($this->status === self::STATUS_ON_TREE) {
            throw new \yii\base\InvalidCallException('Cannot eat the apple - it is still on the tree.');
        }

        // Cannot eat rotten apple
        if ($this->status === self::STATUS_ROTTEN) {
            throw new \yii\base\InvalidCallException('Cannot eat the apple - it is rotten.');
        }

        // Calculate new eaten percentage
        $newEatenPercent = $this->eaten_percent + $percent;

        // If fully eaten (or more), delete the apple
        if ($newEatenPercent >= 100) {
            return $this->delete() !== false;
        }

        // Otherwise, update eaten percentage
        $this->eaten_percent = $newEatenPercent;
        $this->save(false, ['eaten_percent']);

        return false;
    }

    /**
     * Get all status options for dropdown.
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ON_TREE => 'On Tree',
            self::STATUS_FALLEN => 'Fallen',
            self::STATUS_ROTTEN => 'Rotten',
        ];
    }

    /**
     * Generate multiple random apples.
     *
     * @param int $count Number of apples to generate
     * @return int Number of successfully created apples
     */
    public static function generateRandom(int $count): int
    {
        $created = 0;
        for ($i = 0; $i < $count; $i++) {
            $apple = self::createRandom();
            if ($apple->save()) {
                $created++;
            }
        }
        return $created;
    }
}
