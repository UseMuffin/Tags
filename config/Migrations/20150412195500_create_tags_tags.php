<?php
use Phinx\Migration\AbstractMigration;

class CreateTagsTags extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('tags_tags');

        $table->addColumn('namespace', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('slug', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('label', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('counter', 'integer', [
            'default' => 0,
            'length' => 11,
            'null' => false,
            'signed' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => true,
        ]);

        $table->create();
    }
}
