<?php
namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class MuffinsFixture extends TestFixture
{
    public $table = 'tags_muffins';

    public $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'name' => ['type' => 'string', 'length' => 255],
        'tag_count' => ['type' => 'integer', 'null' => true, 'default' => 0],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public $records = [
        [
            'name' => 'blue',
            'tag_count' => 2,
        ],
        [
            'name' => 'red',
            'tag_count' => 1,
        ],
    ];
}
