<?php
namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class MuffinsFixture extends TestFixture
{
    public $table = 'tags_muffins';

    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'length' => 255],
        'tags_cnt' => ['type' => 'integer', 'null' => true, 'default' => 0],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public $records = [
        [
            'id' => 1,
            'name' => 'blue',
            'tags_cnt' => 2,
        ],
        [
            'id' => 2,
            'name' => 'red',
            'tags_cnt' => 1,
        ],
    ];
}
