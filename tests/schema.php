<?php
declare(strict_types=1);

return [
    'tags_buns' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'length' => 255],
            'tag_count' => ['type' => 'integer', 'null' => true, 'default' => 0],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'tags_muffins' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'length' => 255],
            'tag_count' => ['type' => 'integer', 'null' => true, 'default' => 0],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'tags_tagged' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'tag_id' => ['type' => 'integer', 'null' => false],
            'fk_id' => ['type' => 'integer', 'null' => false],
            'fk_table' => ['type' => 'string', 'limit' => 255, 'null' => false],
            'created' => ['type' => 'datetime', 'null' => true],
            'modified' => ['type' => 'datetime', 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'tags_tags' => [
        'columns' => [
            'id' => ['type' => 'integer'],
            'namespace' => ['type' => 'string', 'length' => 255, 'null' => true],
            'tag_key' => ['type' => 'string', 'length' => 255],
            'slug' => ['type' => 'string', 'length' => 255],
            'label' => ['type' => 'string', 'length' => 255],
            'counter' => ['type' => 'integer', 'unsigned' => true, 'default' => 0, 'null' => true],
            'created' => ['type' => 'datetime', 'null' => true],
            'modified' => ['type' => 'datetime', 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
