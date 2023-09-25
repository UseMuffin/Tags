<?php
declare(strict_types=1);

namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TaggedFixture extends TestFixture
{
    public string $table = 'tags_tagged';

    public array $records = [
        [
            'tag_id' => 1,
            'fk_id' => 1,
            'fk_table' => 'tags_muffins',
        ],
        [
            'tag_id' => 2,
            'fk_id' => 1,
            'fk_table' => 'tags_muffins',
        ],
        [
            'tag_id' => 1,
            'fk_id' => 2,
            'fk_table' => 'tags_muffins',
        ],
        [
            'tag_id' => 1,
            'fk_id' => 1,
            'fk_table' => 'tags_buns',
        ],
        [
            'tag_id' => 2,
            'fk_id' => 2,
            'fk_table' => 'tags_buns',
        ],
    ];

    public function init(): void
    {
        $created = $modified = date('Y-m-d H:i:s');
        array_walk($this->records, function (&$record) use ($created, $modified) {
            $record += compact('created', 'modified');
        });
        parent::init();
    }
}
