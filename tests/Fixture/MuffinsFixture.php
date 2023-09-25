<?php
declare(strict_types=1);

namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class MuffinsFixture extends TestFixture
{
    public string $table = 'tags_muffins';

    public array $records = [
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
