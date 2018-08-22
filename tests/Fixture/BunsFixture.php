<?php
namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BunsFixture extends TestFixture
{
    public $table = 'tags_buns';

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
            'name' => 'square',
            'tag_count' => 1,
        ],
        [
            'name' => 'round',
            'tag_count' => 1,
        ],
    ];
}
