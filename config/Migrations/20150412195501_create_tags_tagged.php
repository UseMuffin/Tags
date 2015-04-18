<?php
use Phinx\Migration\AbstractMigration;

class CreateTagsTagged extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('tags_tagged');
        $table->addColumn('tag_id', 'integer', [
            'default' => null,
            'length' => 11,
            'null' => true,
        ]);
        $table->addColumn('entity_id', 'integer', [
            'default' => null,
            'length' => 11,
            'null' => true,
        ]);
        $table->addColumn('entity', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
    }
}