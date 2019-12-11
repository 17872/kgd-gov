<?php

use yii\db\Migration;

/**
 * Class m191211_015836_iin
 */
class m191211_015836_iin extends Migration
{
    /**
     * {@inheritdoc}
     */
//    public function safeUp()
//    {
//
//    }

    /**
     * {@inheritdoc}
     */
//    public function safeDown()
//    {
//
//    }

// Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%iin}}', [
            'id' => $this->primaryKey(),
            'iin' => $this->bigInteger()->notNull(),
            'data' => $this->text()
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%iin}}');
    }
}
