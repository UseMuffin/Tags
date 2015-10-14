<?php

use Phinx\Migration\AbstractMigration;

class UniqueTags extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('tags_tags');

        $table->addColumn('tag_key', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);

        $table->addIndex(['tag_key', 'label', 'namespace'], ['unique' => true]);
        $table->update();

        $table = $this->table('tags_tagged');

        $table->addIndex(['tag_id', 'fk_id', 'fk_table'], ['unique' => true]);
        $table->update();
    }
}
