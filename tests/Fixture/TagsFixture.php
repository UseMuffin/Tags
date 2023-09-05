<?php
declare(strict_types=1);

namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TagsFixture extends TestFixture
{
    public string $table = 'tags_tags';

    public array $records = [
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

    public function init(): void
    {
        $created = $modified = date('Y-m-d H:i:s');
        array_walk($this->records, function (&$record) use ($created, $modified) {
            $record += compact('created', 'modified');
        });
        parent::init();
    }
}
