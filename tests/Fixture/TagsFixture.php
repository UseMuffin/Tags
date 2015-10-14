<?php
namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TagsFixture extends TestFixture
{
    public $table = 'tags_tags';

    public $fields = [
        'id' => ['type' => 'integer'],
        'namespace' => ['type' => 'string', 'length' => 255, 'null' => true],
        'tag_key' => ['type' => 'string', 'length' => 255],
        'slug' => ['type' => 'string', 'length' => 255],
        'label' => ['type' => 'string', 'length' => 255],
        'counter' => ['type' => 'integer', 'unsigned' => true, 'default' => 0, 'null' => true],
        'created' => ['type' => 'datetime', 'null' => true],
        'modified' => ['type' => 'datetime', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public $records = [
        [
            'namespace' => null,
            'tag_key' => 'color',
            'slug' => 'color',
            'label' => 'Color',
            'counter' => 3,
        ],
        [
            'namespace' => null,
            'tag_key' => 'dark-color',
            'slug' => 'dark-color',
            'label' => 'Dark Color',
            'counter' => 2,
        ],
    ];

    public function init()
    {
        $created = $modified = date('Y-m-d H:i:s');
        array_walk($this->records, function (&$record) use ($created, $modified) {
            $record += compact('created', 'modified');
        });
        parent::init();
    }
}
