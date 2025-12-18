<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apple}}`.
 */
class m241218_200000_create_apple_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%apple}}', [
            'id' => $this->primaryKey(),
            'color' => $this->string(50)->notNull(),
            'created_at' => $this->integer()->notNull()->comment('Unix timestamp when apple appeared on tree'),
            'fallen_at' => $this->integer()->null()->comment('Unix timestamp when apple fell from tree'),
            'status' => $this->smallInteger()->notNull()->defaultValue(0)->comment('0=on tree, 1=fallen, 2=rotten'),
            'eaten_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0)->comment('Percentage of apple eaten (0-100)'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%apple}}');
    }
}
